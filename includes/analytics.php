<?php
/**
 * ToolsWallet High-Performance Traffic & Single-User Analytics Engine
 * Backed by lightweight zero-config SQLite for 100% uptime and resilience
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/config.php';

class Analytics {
    private static ?PDO $db = null;

    private static function getDb(): PDO {
        if (self::$db !== null) {
            return self::$db;
        }

        $dataDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'data';
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0755, true);
        }

        $dbPath = $dataDir . DIRECTORY_SEPARATOR . 'analytics.sqlite';
        self::$db = new PDO('sqlite:' . $dbPath);
        self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Initialize schema if not exists
        self::initSchema();

        return self::$db;
    }

    private static function initSchema(): void {
        $schema = "
        CREATE TABLE IF NOT EXISTS page_views (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            visitor_id TEXT NOT NULL,
            ip_hash TEXT NOT NULL,
            page_url TEXT NOT NULL,
            page_title TEXT,
            tool_slug TEXT,
            referrer TEXT,
            device_type TEXT,
            browser TEXT,
            os TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE INDEX IF NOT EXISTS idx_pv_visitor ON page_views(visitor_id);
        CREATE INDEX IF NOT EXISTS idx_pv_created ON page_views(created_at);
        CREATE INDEX IF NOT EXISTS idx_pv_page ON page_views(page_url);
        CREATE INDEX IF NOT EXISTS idx_pv_slug ON page_views(tool_slug);

        CREATE TABLE IF NOT EXISTS visitor_profiles (
            visitor_id TEXT PRIMARY KEY,
            ip_hash TEXT NOT NULL,
            first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
            total_views INTEGER DEFAULT 1,
            device_type TEXT,
            browser TEXT,
            os TEXT,
            user_label TEXT
        );
        CREATE INDEX IF NOT EXISTS idx_vp_last_seen ON visitor_profiles(last_seen);
        ";
        self::$db->exec($schema);
    }

    /**
     * Get or create a persistent Visitor ID (Single User tracking)
     */
    public static function getVisitorId(): string {
        if (!empty($_COOKIE['tw_visitor_id'])) {
            return substr(preg_replace('/[^a-zA-Z0-9_-]/', '', $_COOKIE['tw_visitor_id']), 0, 40);
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $seed = $ip . '|' . $ua . '|' . microtime(true) . '|' . bin2hex(random_bytes(8));
        $visitorId = 'usr_' . substr(hash('sha256', $seed), 0, 16);

        // Set persistent 1-year cookie
        if (!headers_sent()) {
            setcookie('tw_visitor_id', $visitorId, [
                'expires' => time() + (86400 * 365),
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        $_COOKIE['tw_visitor_id'] = $visitorId;

        return $visitorId;
    }

    /**
     * Parse User Agent into Device, Browser, OS
     */
    public static function parseUserAgent(): array {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $device = 'Desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $ua)) {
            $device = 'Mobile';
        }

        $browser = 'Other';
        if (preg_match('/edg/i', $ua)) $browser = 'Edge';
        elseif (preg_match('/chrome|crios/i', $ua)) $browser = 'Chrome';
        elseif (preg_match('/firefox|fxios/i', $ua)) $browser = 'Firefox';
        elseif (preg_match('/safari/i', $ua)) $browser = 'Safari';
        elseif (preg_match('/opera|opr/i', $ua)) $browser = 'Opera';

        $os = 'Other';
        if (preg_match('/windows|win32/i', $ua)) $os = 'Windows';
        elseif (preg_match('/android/i', $ua)) $os = 'Android';
        elseif (preg_match('/iphone|ipad|ipod/i', $ua)) $os = 'iOS';
        elseif (preg_match('/macintosh|mac os x/i', $ua)) $os = 'macOS';
        elseif (preg_match('/linux/i', $ua)) $os = 'Linux';

        return ['device' => $device, 'browser' => $browser, 'os' => $os];
    }

    /**
     * Record a Page Visit
     */
    public static function track(?string $title = null, ?string $toolSlug = null): void {
        // Skip tracking for admin panel, assets, and CLI
        if (php_sapi_name() === 'cli') return;
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (str_contains($uri, '/admin/') || str_contains($uri, '/assets/') || str_contains($uri, '/api/')) {
            return;
        }

        try {
            $db = self::getDb();
            $visitorId = self::getVisitorId();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ipHash = substr(hash('sha256', $ip . 'tw_salt'), 0, 16);
            $referrer = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 250);
            $uaInfo = self::parseUserAgent();

            $pageUrl = strtok($uri, '?') ?: '/';

            // Insert into page_views
            $stmt = $db->prepare("
                INSERT INTO page_views (visitor_id, ip_hash, page_url, page_title, tool_slug, referrer, device_type, browser, os)
                VALUES (:visitor_id, :ip_hash, :page_url, :page_title, :tool_slug, :referrer, :device, :browser, :os)
            ");
            $stmt->execute([
                ':visitor_id' => $visitorId,
                ':ip_hash' => $ipHash,
                ':page_url' => $pageUrl,
                ':page_title' => $title ? substr($title, 0, 150) : null,
                ':tool_slug' => $toolSlug,
                ':referrer' => $referrer,
                ':device' => $uaInfo['device'],
                ':browser' => $uaInfo['browser'],
                ':os' => $uaInfo['os'],
            ]);

            // Upsert visitor_profiles
            $profStmt = $db->prepare("
                INSERT INTO visitor_profiles (visitor_id, ip_hash, first_seen, last_seen, total_views, device_type, browser, os)
                VALUES (:visitor_id, :ip_hash, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1, :device, :browser, :os)
                ON CONFLICT(visitor_id) DO UPDATE SET
                    last_seen = CURRENT_TIMESTAMP,
                    total_views = total_views + 1,
                    device_type = excluded.device_type,
                    browser = excluded.browser,
                    os = excluded.os
            ");
            $profStmt->execute([
                ':visitor_id' => $visitorId,
                ':ip_hash' => $ipHash,
                ':device' => $uaInfo['device'],
                ':browser' => $uaInfo['browser'],
                ':os' => $uaInfo['os'],
            ]);
        } catch (\Throwable $e) {
            // Silently fail to never disrupt user-facing requests
        }
    }

    /**
     * Get KPI Summary Stats
     */
    public static function getKpiStats(): array {
        $db = self::getDb();

        $totalViews = (int)$db->query("SELECT COUNT(*) FROM page_views")->fetchColumn();
        $totalVisitors = (int)$db->query("SELECT COUNT(DISTINCT visitor_id) FROM visitor_profiles")->fetchColumn();

        $todayViews = (int)$db->query("SELECT COUNT(*) FROM page_views WHERE created_at >= date('now', 'start of day')")->fetchColumn();
        $todayVisitors = (int)$db->query("SELECT COUNT(DISTINCT visitor_id) FROM page_views WHERE created_at >= date('now', 'start of day')")->fetchColumn();

        $last7DaysViews = (int)$db->query("SELECT COUNT(*) FROM page_views WHERE created_at >= date('now', '-7 days')")->fetchColumn();
        $last7DaysVisitors = (int)$db->query("SELECT COUNT(DISTINCT visitor_id) FROM page_views WHERE created_at >= date('now', '-7 days')")->fetchColumn();

        $liveVisitors = (int)$db->query("SELECT COUNT(DISTINCT visitor_id) FROM page_views WHERE created_at >= datetime('now', '-15 minutes')")->fetchColumn();

        return [
            'total_views' => $totalViews,
            'total_visitors' => $totalVisitors,
            'today_views' => $todayViews,
            'today_visitors' => $todayVisitors,
            'last_7d_views' => $last7DaysViews,
            'last_7d_visitors' => $last7DaysVisitors,
            'live_now' => max(1, $liveVisitors)
        ];
    }

    /**
     * Get Traffic Breakdown by Page & Single Users
     */
    public static function getPageTraffic(int $limit = 50, string $filter = 'all'): array {
        $db = self::getDb();

        $whereClause = "";
        if ($filter === 'today') {
            $whereClause = "WHERE created_at >= date('now', 'start of day')";
        } elseif ($filter === '7d') {
            $whereClause = "WHERE created_at >= date('now', '-7 days')";
        } elseif ($filter === '30d') {
            $whereClause = "WHERE created_at >= date('now', '-30 days')";
        }

        $sql = "
            SELECT 
                page_url,
                tool_slug,
                COUNT(*) as total_views,
                COUNT(DISTINCT visitor_id) as unique_users,
                MAX(created_at) as last_visited,
                ROUND(CAST(COUNT(*) AS FLOAT) / MAX(1, COUNT(DISTINCT visitor_id)), 2) as views_per_user
            FROM page_views
            $whereClause
            GROUP BY page_url
            ORDER BY total_views DESC
            LIMIT :limit
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get Single Users breakdown (Individual Visitor Profiles)
     */
    public static function getSingleUsers(int $limit = 50, int $offset = 0): array {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT 
                vp.*,
                (SELECT GROUP_CONCAT(DISTINCT page_url) FROM page_views WHERE visitor_id = vp.visitor_id) as visited_pages,
                (SELECT COUNT(DISTINCT tool_slug) FROM page_views WHERE visitor_id = vp.visitor_id AND tool_slug IS NOT NULL) as tools_used
            FROM visitor_profiles vp
            ORDER BY vp.last_seen DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get specific user session history
     */
    public static function getUserHistory(string $visitorId, int $limit = 30): array {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT * FROM page_views 
            WHERE visitor_id = :vid 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->execute([':vid' => $visitorId, ':limit' => $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get Recent Real-Time Visits Log
     */
    public static function getRecentVisits(int $limit = 25): array {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT * FROM page_views 
            ORDER BY id DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get Device & Browser breakdown
     */
    public static function getDeviceStats(): array {
        $db = self::getDb();
        $devices = $db->query("SELECT device_type, COUNT(*) as count FROM page_views GROUP BY device_type ORDER BY count DESC")->fetchAll();
        $browsers = $db->query("SELECT browser, COUNT(*) as count FROM page_views GROUP BY browser ORDER BY count DESC LIMIT 5")->fetchAll();
        $referrers = $db->query("SELECT CASE WHEN referrer = '' OR referrer IS NULL THEN 'Direct / Bookmark' ELSE referrer END as source, COUNT(*) as count FROM page_views GROUP BY source ORDER BY count DESC LIMIT 6")->fetchAll();

        return [
            'devices' => $devices,
            'browsers' => $browsers,
            'referrers' => $referrers
        ];
    }
}
