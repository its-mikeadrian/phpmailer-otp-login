<?php
// security.php: session hardening and CSRF utilities
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_handler.php';
require_once __DIR__ . '/remember_me.php';

function secure_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Harden session behavior
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    // Configure cookie params from environment
    $domain = env_get('SESSION_COOKIE_DOMAIN', '');
    $path = env_get('SESSION_COOKIE_PATH', '/');
    $secure = $isHttps || filter_var(env_get('SESSION_COOKIE_SECURE', ''), FILTER_VALIDATE_BOOLEAN);
    $samesite = env_get('SESSION_COOKIE_SAMESITE', 'Strict');
    $name = env_get('SESSION_NAME', 'STEELSYNCSESSID');
    session_name($name);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $path,
        'domain' => $domain,
        'secure' => $secure,
        'httponly' => true,
        'samesite' => $samesite,
    ]);

    // Register encrypted DB-backed handler
    $pdo = get_db_connection();
    $handler = new DbSessionHandler($pdo);
    session_set_save_handler($handler, true);

    session_start();

    // Opportunistic auto-login via Remember Me
    if (empty($_SESSION['auth_user_id'])) {
        remember_me_auto_login();
    }
}

function csrf_token(): string {
    secure_session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_validate(): bool {
    secure_session_start();
    $expected = $_SESSION['csrf_token'] ?? '';
    $provided = $_POST['csrf_token'] ?? '';
    if ($expected === '' || $provided === '') {
        return false;
    }
    $ok = hash_equals($expected, $provided);
    // Rotate token after a check to reduce reuse window
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $ok;
}

function session_timeout_warn_script(int $warnBeforeSeconds = 60): string {
    $ttl = (int) env_get('SESSION_LIFETIME_SECONDS', '1800');
    $warnAt = max(1, $ttl - $warnBeforeSeconds);
    return '<script>(function(){var ttl=' . $ttl . ',warnAt=' . $warnAt . ';var t=0;function reset(){t=0;}function tick(){t++;if(t===warnAt){var el=document.createElement("div");el.className="alert alert-warning position-fixed top-0 start-50 translate-middle-x mt-3";el.textContent="Your session will expire soon due to inactivity.";document.body.appendChild(el);}if(t>=ttl){window.location.href="logout.php";}}["mousemove","keydown","click","scroll"].forEach(function(e){window.addEventListener(e,reset,{passive:true});});setInterval(tick,1000);}());</script>';
}