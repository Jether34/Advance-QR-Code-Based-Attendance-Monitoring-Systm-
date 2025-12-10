<?php
// bootstrap.php - central initialization (load config, set secure session params, start session)
require_once __DIR__ . '/config.php';

// Apply robust session settings (redundant-safe with config.php)
// Only apply if headers haven't been sent yet (CLI or early page execution)
if (!headers_sent()) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    $forceSecure = (getenv('FORCE_HTTPS') === 'true' || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'));
    if ($forceSecure) {
        ini_set('session.cookie_secure', '1');
    }
    if (PHP_VERSION_ID >= 70300) {
        ini_set('session.cookie_samesite', 'Lax');
    }

    $cookieLifetime = defined('SESSION_LIFETIME') ? (int)SESSION_LIFETIME : 3600;
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieLifetime,
        'path' => $cookieParams['path'] ?? '/',
        'domain' => $cookieParams['domain'] ?? '',
        'secure' => $forceSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session id occasionally for logged in users
if (isset($_SESSION['user_id']) && empty($_SESSION['session_regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['session_regenerated'] = time();
}

?>
