<?php
/**
 * ToolsWallet Admin Authentication & Security Layer
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AdminAuth {
    private const CREDENTIALS_FILE = __DIR__ . '/../data/admin_creds.json';
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    /**
     * Get or initialize admin credentials
     * Default: username: admin, password: admin123
     */
    public static function getCredentials(): array {
        if (file_exists(self::CREDENTIALS_FILE)) {
            $data = json_decode(file_get_contents(self::CREDENTIALS_FILE), true);
            if (!empty($data['username']) && !empty($data['password_hash'])) {
                return $data;
            }
        }

        // Default initial credentials (admin / admin123)
        $default = [
            'username' => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $dataDir = dirname(self::CREDENTIALS_FILE);
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0755, true);
        }
        file_put_contents(self::CREDENTIALS_FILE, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }

    /**
     * Update admin password
     */
    public static function updatePassword(string $newPassword): bool {
        $creds = self::getCredentials();
        $creds['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
        $creds['updated_at'] = date('Y-m-d H:i:s');
        return file_put_contents(self::CREDENTIALS_FILE, json_encode($creds, JSON_PRETTY_PRINT)) !== false;
    }

    /**
     * Check if currently authenticated as admin
     */
    public static function isLoggedIn(): bool {
        return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    /**
     * Require admin login (redirects to login if not authenticated)
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            $current = urlencode($_SERVER['REQUEST_URI'] ?? '');
            header('Location: ' . url('admin/login.php?redirect=' . $current));
            exit;
        }
    }

    /**
     * Attempt login with rate-limiting and secure session creation
     */
    public static function attemptLogin(string $username, string $password): array {
        $attemptsKey = 'admin_login_attempts';
        $lockoutKey = 'admin_login_lockout';

        // Check Lockout
        if (!empty($_SESSION[$lockoutKey]) && time() < $_SESSION[$lockoutKey]) {
            $remaining = ceil(($_SESSION[$lockoutKey] - time()) / 60);
            return ['success' => false, 'message' => "Too many failed attempts. Locked out for {$remaining} more minute(s)."];
        }

        $creds = self::getCredentials();
        $validUser = hash_equals($creds['username'], trim($username));
        $validPass = password_verify($password, $creds['password_hash']);

        if ($validUser && $validPass) {
            // Reset attempts on success
            unset($_SESSION[$attemptsKey], $_SESSION[$lockoutKey]);

            // Regenerate session ID to prevent fixation attacks
            session_regenerate_id(true);

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $creds['username'];
            $_SESSION['admin_login_time'] = time();

            return ['success' => true, 'message' => 'Login successful'];
        }

        // Increment failed attempts
        $_SESSION[$attemptsKey] = ($_SESSION[$attemptsKey] ?? 0) + 1;
        if ($_SESSION[$attemptsKey] >= self::MAX_ATTEMPTS) {
            $_SESSION[$lockoutKey] = time() + (self::LOCKOUT_MINUTES * 60);
            return ['success' => false, 'message' => 'Account locked due to 5 failed attempts. Please try again in 15 minutes.'];
        }

        $left = self::MAX_ATTEMPTS - $_SESSION[$attemptsKey];
        return ['success' => false, 'message' => "Invalid username or password. ({$left} attempt(s) remaining)"];
    }

    /**
     * Logout
     */
    public static function logout(): void {
        unset($_SESSION['admin_logged_in'], $_SESSION['admin_user'], $_SESSION['admin_login_time']);
        session_destroy();
    }

    /**
     * CSRF Token Management
     */
    public static function getCsrfToken(): string {
        if (empty($_SESSION['admin_csrf'])) {
            $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['admin_csrf'];
    }

    public static function verifyCsrf(?string $token): bool {
        if (empty($token) || empty($_SESSION['admin_csrf'])) {
            return false;
        }
        return hash_equals($_SESSION['admin_csrf'], $token);
    }
}
