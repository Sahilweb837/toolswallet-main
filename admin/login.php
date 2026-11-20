<?php
/**
 * ToolsWallet Secure Admin Login
 * Domain: toolswallet.online
 */

require_once __DIR__ . '/auth.php';

// Redirect if already logged in
if (AdminAuth::isLoggedIn()) {
    header('Location: ' . url('admin/index.php'));
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!AdminAuth::verifyCsrf($csrf)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $result = AdminAuth::attemptLogin($username, $password);
        if ($result['success']) {
            $dest = !empty($_POST['redirect']) ? urldecode($_POST['redirect']) : url('admin/index.php');
            header('Location: ' . $dest);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$csrfToken = AdminAuth::getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Admin Login | ToolsWallet</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top right, rgba(255, 107, 0, 0.08), transparent 50%),
                        radial-gradient(circle at bottom left, rgba(251, 146, 60, 0.05), transparent 50%),
                        var(--bg-body);
            padding: 1.5rem;
        }
        .admin-login-card {
            width: 100%;
            max-width: 440px;
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-xl);
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 40px -15px rgba(234, 88, 12, 0.15), 0 0 1px 1px rgba(0,0,0,0.03);
            position: relative;
            overflow: hidden;
        }
        .admin-login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff7a00, #ea580c);
        }
        .login-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-brand-icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: linear-gradient(135deg, #ff7a00, #ea580c);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 8px 20px rgba(234, 88, 12, 0.35);
            margin-bottom: 1rem;
        }
        .form-control-wrap {
            position: relative;
            margin-bottom: 1.25rem;
        }
        .form-control-wrap i.input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
        }
        .form-control-wrap input {
            width: 100%;
            height: 50px;
            padding: 0 2.75rem 0 2.75rem;
            border: 1px solid var(--border-subtle);
            background: var(--bg-body);
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            color: var(--text-primary);
            transition: all var(--transition-fast);
        }
        .form-control-wrap input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
            background: #fff;
        }
        .toggle-pwd {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        .admin-demo-hint {
            margin-top: 1.5rem;
            padding: 0.85rem 1rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            border: 1px dashed var(--primary-border);
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
    </style>
</head>
<body>

    <div class="admin-login-card">
        <div class="login-brand">
            <div class="login-brand-icon">
                <svg width="30" height="30" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="7" y="11" width="30" height="23" rx="4.5" fill="#FFFFFF" />
                    <path d="M7 15C7 13 9 11 12 11H32C35 11 37 13 37 15V18C33 16.5 27 16 22 16C17 16 11 16.5 7 18V15Z" fill="#FFF3EB" />
                    <rect x="24" y="20" width="13" height="8" rx="3" fill="#FF5400" />
                    <circle cx="28" cy="24" r="1.6" fill="#FFFFFF" />
                    <path d="M17 17L13 23H17L15 28L21 21H17L19 17H17Z" fill="#EA580C" />
                </svg>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">Admin Portal</h2>
            <p style="font-size: 0.85rem; color: var(--text-muted);">Secure Management & Traffic Analytics</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 0.75rem 1rem; border-radius: var(--radius-md); margin-bottom: 1.25rem; font-size: 0.88rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

            <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text-primary);">Administrator Username</label>
            <div class="form-control-wrap">
                <i class="fa-solid fa-user-shield input-icon"></i>
                <input type="text" name="username" placeholder="Username" value="admin" required autofocus autocomplete="username">
            </div>

            <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text-primary);">Password</label>
            <div class="form-control-wrap">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" id="adminPassword" name="password" placeholder="Password" value="admin123" required autocomplete="current-password">
                <button type="button" class="toggle-pwd" onclick="togglePasswordVisibility()"><i class="fa-regular fa-eye" id="eyeIcon"></i></button>
            </div>

            <button type="submit" class="btn-primary-gradient" style="width: 100%; height: 50px; font-size: 1rem; font-weight: 700; border-radius: var(--radius-md); margin-top: 0.5rem;">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In to Dashboard
            </button>
        </form>

        <div class="admin-demo-hint">
            <i class="fa-solid fa-key" style="color: var(--primary); font-size: 1rem;"></i>
            <div>
                <strong>Default Credentials:</strong><br>
                Username: <code>admin</code> | Password: <code>admin123</code>
            </div>
        </div>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem;">
            <a href="<?= url() ?>" style="color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.35rem;">
                <i class="fa-solid fa-arrow-left"></i> Back to ToolsWallet Website
            </a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById('adminPassword');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
