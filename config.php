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
define('SERVER_IP', getenv('SERVER_IP') ?: '192.168.1.12');
define('SERVER_PORT', getenv('SERVER_PORT') && getenv('SERVER_PORT') != '80' ? ':' . getenv('SERVER_PORT') : '');
define('PROJECT_PATH', getenv('PROJECT_PATH') ?: '/puta');

// Determine protocol (HTTP/HTTPS)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
define('SERVER_URL', $protocol . '://' . SERVER_IP . SERVER_PORT . PROJECT_PATH);

// Application Settings
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'Asia/Manila');

// Security Settings
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 3600);
define('PASSWORD_MIN_LENGTH', getenv('PASSWORD_MIN_LENGTH') ?: 8);

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Security Headers
if (!headers_sent()) {
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