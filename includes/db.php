<?php
/**
 * ToolsWallet Database Connection & Repository
 * Seamless auto-fallback to static data.php if MySQL is not yet configured/imported
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $pdo = null;
    private static bool $connectionAttempted = false;
    private static bool $isConnected = false;
    private static ?array $staticData = null;

    public static function getPdo(): ?PDO {
        if (self::$connectionAttempted) {
            return self::$pdo;
        }

        self::$connectionAttempted = true;

        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 2, // 2-second timeout to avoid page hanging if MySQL is stopped
            ];
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            self::$isConnected = true;
        } catch (\Throwable $e) {
            self::$pdo = null;
            self::$isConnected = false;
        }

        return self::$pdo;
    }

    public static function isConnected(): bool {
        self::getPdo();
        return self::$isConnected;
    }

    private static function getStaticData(): array {
        if (self::$staticData === null) {
            self::$staticData = require __DIR__ . '/data.php';
        }
        return self::$staticData;
    }

    /**
     * Get all active categories
     */
    public static function getCategories(): array {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC, id ASC");
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    $keyed = [];
                    foreach ($rows as $row) {
                        $keyed[$row['slug']] = $row;
                    }
                    return $keyed;
                }
            } catch (\Throwable $e) {}
        }

        $static = self::getStaticData();
        return $static['categories'] ?? [];
    }

    /**
     * Get single category by slug
     */
    public static function getCategory(string $slug): ?array {
        $categories = self::getCategories();
        return $categories[$slug] ?? null;
    }

    /**
     * Get all active tools, optionally filtered by category slug
     */
    public static function getTools(?string $categorySlug = null): array {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                if ($categorySlug) {
                    $stmt = $pdo->prepare("
                        SELECT t.*, c.name as category_name, c.slug as category_slug, c.color as category_color 
                        FROM tools t 
                        JOIN categories c ON t.category_id = c.id 
                        WHERE c.slug = :category_slug AND t.status = 'active' 
                        ORDER BY t.is_popular DESC, t.run_count DESC
                    ");
                    $stmt->execute(['category_slug' => $categorySlug]);
                } else {
                    $stmt = $pdo->query("
                        SELECT t.*, c.name as category_name, c.slug as category_slug, c.color as category_color 
                        FROM tools t 
                        JOIN categories c ON t.category_id = c.id 
                        WHERE t.status = 'active' 
                        ORDER BY t.category_id ASC, t.run_count DESC
                    ");
                }
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    $keyed = [];
                    foreach ($rows as $r) {
                        $keyed[$r['slug']] = $r;
                    }
                    return $keyed;
                }
            } catch (\Throwable $e) {}
        }

        $static = self::getStaticData();
        $tools = $static['tools'] ?? [];
        if ($categorySlug) {
            return array_filter($tools, fn($t) => ($t['category_slug'] ?? '') === $categorySlug);
        }
        return $tools;
    }

    /**
     * Get tool by slug
     */
    public static function getTool(string $slug): ?array {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("
                    SELECT t.*, c.name as category_name, c.slug as category_slug, c.color as category_color 
                    FROM tools t 
                    JOIN categories c ON t.category_id = c.id 
                    WHERE t.slug = :slug AND t.status = 'active' LIMIT 1
                ");
                $stmt->execute(['slug' => $slug]);
                $row = $stmt->fetch();
                if ($row) {
                    return $row;
                }
            } catch (\Throwable $e) {}
        }

        $static = self::getStaticData();
        return $static['tools'][$slug] ?? null;
    }

    /**
     * Get Popular Tools
     */
    public static function getPopularTools(int $limit = 8): array {
        $all = self::getTools();
        $filtered = array_filter($all, fn($t) => !empty($t['is_popular']));
        usort($filtered, fn($a, $b) => ($b['run_count'] ?? 0) <=> ($a['run_count'] ?? 0));
        return array_slice($filtered, 0, $limit);
    }

    /**
     * Get Trending Tools
     */
    public static function getTrendingTools(int $limit = 8): array {
        $all = self::getTools();
        $filtered = array_filter($all, fn($t) => !empty($t['is_trending']));
        if (count($filtered) < 4) {
            $filtered = $all;
        }
        usort($filtered, fn($a, $b) => ($b['run_count'] ?? 0) <=> ($a['run_count'] ?? 0));
        return array_slice($filtered, 0, $limit);
    }

    /**
     * Get FAQs
     */
    public static function getFaqs(?string $toolSlug = null, ?string $categorySlug = null): array {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                if ($toolSlug) {
                    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE tool_slug = :tool_slug ORDER BY display_order ASC");
                    $stmt->execute(['tool_slug' => $toolSlug]);
                } elseif ($categorySlug) {
                    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE category_slug = :cat_slug ORDER BY display_order ASC");
                    $stmt->execute(['cat_slug' => $categorySlug]);
                } else {
                    $stmt = $pdo->query("SELECT * FROM faqs WHERE tool_slug IS NULL AND category_slug IS NULL ORDER BY display_order ASC");
                }
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    return $rows;
                }
            } catch (\Throwable $e) {}
        }

        $static = self::getStaticData();
        return $static['faqs'] ?? [];
    }

    /**
     * Record a tool run
     */
    public static function incrementToolRun(string $slug): void {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE tools SET run_count = run_count + 1 WHERE slug = :slug");
                $stmt->execute(['slug' => $slug]);

                $ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
                $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);
                $logStmt = $pdo->prepare("INSERT INTO tool_runs (tool_slug, ip_hash, user_agent) VALUES (:slug, :ip, :ua)");
                $logStmt->execute(['slug' => $slug, 'ip' => $ipHash, 'ua' => $ua]);
            } catch (\Throwable $e) {}
        }
    }
}
