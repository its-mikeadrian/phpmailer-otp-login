<?php
require_once __DIR__ . '/../src/security.php';
secure_session_start();

// Clear Remember Me token and cookie
remember_me_clear();

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], true);
}
session_destroy();
header('Location: index.php');
exit;
