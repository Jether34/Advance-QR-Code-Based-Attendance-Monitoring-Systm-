<?php
// config.php - Secure configuration file

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// Server Configuration (from environment or defaults)
// Auto-detect server URL from current request origin (works with ngrok, LAN, and localhost)
if (getenv('SERVER_URL')) {
    define('SERVER_URL', getenv('SERVER_URL'));
} else {
    // Use the current request's host/scheme to build the URL (works transparently across ngrok, LAN, localhost)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? (getenv('SERVER_IP') ?: '192.168.1.12');
    // Auto-detect if running from subdirectory or root
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $path = ($scriptDir && $scriptDir !== '/') ? $scriptDir : '';
    define('SERVER_URL', $protocol . '://' . $host . $path);
}
define('SERVER_IP', getenv('SERVER_IP') ?: '192.168.1.12');
define('SERVER_PORT', getenv('SERVER_PORT') && getenv('SERVER_PORT') != '80' ? ':' . getenv('SERVER_PORT') : '');

// Application Settings
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'Asia/Manila');

// Security Settings
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 3600);
define('PASSWORD_MIN_LENGTH', getenv('PASSWORD_MIN_LENGTH') ?: 8);

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Harden session cookie settings and session behavior
// Use strict mode and secure cookie flags where possible. These settings should be applied
// before any session_start() calls elsewhere in the app.
if (!headers_sent()) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    // Respect environment override to force secure cookies (useful when behind TLS)
    $forceSecure = (getenv('FORCE_HTTPS') === 'true' || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'));
    if ($forceSecure) {
        ini_set('session.cookie_secure', '1');
    }
    // Set SameSite attribute if supported
    if (PHP_VERSION_ID >= 70300) {
        ini_set('session.cookie_samesite', 'Lax');
    }
    // Set session cookie lifetime according to configuration
    ini_set('session.cookie_lifetime', (string)SESSION_LIFETIME);
}

// Security Headers
if (!headers_sent()) {
    // Prevent page caching (force fresh page loads)
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:;");
    
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Prevent MIME sniffing
    header('X-Content-Type-Options: nosniff');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Remove server information
    header_remove('X-Powered-By');
}

// Disable error display in production
if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}

?>