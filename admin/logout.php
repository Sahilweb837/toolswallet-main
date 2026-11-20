<?php
/**
 * ToolsWallet Admin Logout
 */
require_once __DIR__ . '/auth.php';
AdminAuth::logout();
header('Location: ' . url('admin/login.php?logged_out=1'));
exit;
