<?php
// bootstrap.php - central initialization (load config, set secure session params, start session)
require_once __DIR__ . '/config.php';

// Apply robust session settings (redundant-safe with config.php)
// Only apply if headers haven't been sent yet (CLI or early page execution)
if (!headers_sent()) {
    // Enforce HSTS when HTTPS is in use
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
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

// CSRF protection utilities
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf() {
        // Only enforce on POST requests for HTML forms
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                http_response_code(403);
                echo 'Forbidden: invalid CSRF token';
                exit;
            }
        }
    }
}

// Lightweight login rate limiting (per IP + email)
if (!function_exists('rate_limit_check')) {
    function rate_limit_check($key, $maxAttempts = 5, $windowSeconds = 300) {
        $now = time();
        if (!isset($_SESSION['rate_limit'])) $_SESSION['rate_limit'] = [];
        $bucket = &$_SESSION['rate_limit'][$key];
        if (!is_array($bucket)) $bucket = ['count' => 0, 'reset' => $now + $windowSeconds];
        if ($now > $bucket['reset']) { $bucket = ['count' => 0, 'reset' => $now + $windowSeconds]; }
        if ($bucket['count'] >= $maxAttempts) {
            $retry = $bucket['reset'] - $now;
            http_response_code(429);
            echo 'Too many attempts. Try again in ' . max(1, $retry) . ' seconds.';
            exit;
        }
        $bucket['count']++;
    }
}

?>
