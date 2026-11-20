<?php
/**
 * ToolsWallet Configuration & Core Utilities
 * Domain: toolswallet.online
 */
// ... your existing config ...

// YouTube downloader — get free key at https://rapidapi.com
// Subscribe to: "YouTube Media Downloader" (free tier: 100-500/mo)
define('RAPIDAPI_KEY', 'PASTE_YOUR_RAPIDAPI_KEY_HERE');
// Secure Session Settings with Long-term Persistence
if (session_status() === PHP_SESSION_NONE) {
    // Ensure session save directory exists and is writable
    $sessPath = session_save_path();
    if (empty($sessPath) || !is_dir($sessPath) || !is_writable($sessPath)) {
        $fallbackDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tw_sessions';
        if (!is_dir($fallbackDir)) {
            @mkdir($fallbackDir, 0777, true);
        }
        if (is_dir($fallbackDir) && is_writable($fallbackDir)) {
            session_save_path($fallbackDir);
        }
    }

    $lifetime = 60 * 60 * 24 * 30; // 30 days persistence
    ini_set('session.gc_maxlifetime', (string)$lifetime);
    ini_set('session.use_strict_mode', '1');
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    
    // Cookie parameters for security and session durability
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    @session_start();
}

// Auto-restore User Session from Secure Remember Token if session expired
if (empty($_SESSION['tw_user']) && !empty($_COOKIE['tw_remember'])) {
    $rememberToken = $_COOKIE['tw_remember'];
    $usersFile = ROOT_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'users.json';
    if (file_exists($usersFile)) {
        $allUsers = json_decode(@file_get_contents($usersFile), true) ?: [];
        foreach ($allUsers as $uEmail => $uData) {
            if (!empty($uData['remember_token']) && hash_equals($uData['remember_token'], $rememberToken)) {
                // Verified valid persistent session token
                $_SESSION['tw_user'] = [
                    'name' => $uData['name'],
                    'email' => $uData['email'],
                    'plan' => $uData['plan'] ?? 'Pro Member'
                ];
                break;
            }
        }
    }
}

// Environment & Debugging
define('APP_ENV', 'development'); // 'development' or 'production'
define('APP_NAME', 'ToolsWallet');
define('APP_TAGLINE', 'All-in-One Online Tools Platform');
define('APP_DOMAIN', 'toolswallet.online');

// Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'toolswallet');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Upload & Temp Directories
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_TEMP_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'temp');

// Auto-detect Base URL
function get_base_url(): string {
    if (php_sapi_name() === 'cli') {
        return 'https://toolswallet.online';
    }
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Check if running in subfolder like /toolsmain
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDir = str_replace('\\', '/', $scriptDir);
    if ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') {
        $scriptDir = '';
    }
    
    return rtrim($protocol . $host . $scriptDir, '/');
}

define('BASE_URL', get_base_url());

/**
 * Generate application URL
 */
function url(string $path = ''): string {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

/**
 * Generate direct URL to a tool's dedicated page (NO SLUG)
 */
function tool_url(string $slug): string {
    return url('tools/' . ltrim($slug, '/') . '.php');
}

/**
 * Generate direct URL to a category page
 */
function category_url(string $slug): string {
    return url('category.php?slug=' . ltrim($slug, '/'));
}

/**
 * Generate asset URL
 */
function asset(string $path = ''): string {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Escape output for safe HTML
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Resolve FontAwesome icon class (supports brand icons like fa-brands fa-instagram)
 */
function fa_class(string $icon): string {
    $icon = trim($icon);
    if (str_starts_with($icon, 'fa-solid ') || str_starts_with($icon, 'fa-brands ') || str_starts_with($icon, 'fa-regular ')) {
        return $icon;
    }
    $brandIcons = ['fa-instagram', 'fa-youtube', 'fa-facebook', 'fa-twitter', 'fa-tiktok', 'fa-github', 'fa-linkedin', 'fa-css3-alt', 'fa-html5'];
    if (in_array($icon, $brandIcons)) {
        return 'fa-brands ' . $icon;
    }
    return 'fa-solid ' . $icon;
}

/**
 * Security: SSRF Protection Validator
 * Verifies if a given URL is a safe public target
 * Rejects private IPs, loopbacks, AWS metadata IPs, and dangerous schemes
 */
function is_safe_public_url(string $url): bool {
    // Only allow http and https
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    $parts = parse_url($url);
    if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
        return false;
    }
    
    $scheme = strtolower($parts['scheme']);
    if ($scheme !== 'http' && $scheme !== 'https') {
        return false;
    }
    
    $host = strtolower($parts['host']);
    
    // Reject localhost and common local alias names
    if (in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0', 'local', 'intranet'])) {
        return false;
    }
    
    // Resolve IP address
    $ip = gethostbyname($host);
    if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
        // DNS lookup failed
        return false;
    }
    
    // Validate that the IP is not private or reserved
    if (!filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    )) {
        return false;
    }
    
    // Specifically block AWS/cloud metadata address (169.254.169.254)
    if (strpos($ip, '169.254.') === 0) {
        return false;
    }
    
    return true;
}

/**
 * Format large numbers for views (e.g. 15.4K)
 */
function format_number_k(int $n): string {
    if ($n >= 1000000) {
        return round($n / 1000000, 1) . 'M';
    }
    if ($n >= 1000) {
        return round($n / 1000, 1) . 'K';
    }
    return (string)$n;
}

/**
 * Clean temporary files older than 1 hour in uploads/temp
 */
function cleanup_temp_files(): void {
    if (!is_dir(UPLOAD_TEMP_DIR)) {
        @mkdir(UPLOAD_TEMP_DIR, 0755, true);
        return;
    }
    
    $now = time();
    $files = @scandir(UPLOAD_TEMP_DIR);
    if ($files) {
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitkeep') {
                continue;
            }
            $filePath = UPLOAD_TEMP_DIR . DIRECTORY_SEPARATOR . $file;
            if (is_file($filePath) && ($now - filemtime($filePath) > 3600)) {
                @unlink($filePath);
            }
        }
    }
}

// Run cleanup occasionally (1 in 50 requests)
if (mt_rand(1, 50) === 1) {
    cleanup_temp_files();
}
