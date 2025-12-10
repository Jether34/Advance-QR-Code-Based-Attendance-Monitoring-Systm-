<?php
// security_utils.php - Security helper functions

/**
 * Sanitize output to prevent XSS
 */
function sanitize_output($data) {
    if (is_array($data)) {
        return array_map('sanitize_output', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Remove console.log statements from JavaScript in production
 */
function minify_js_for_production($js_code) {
    if (APP_ENV === 'production') {
        // Remove console.log statements
        $js_code = preg_replace('/console\.(log|debug|info|warn|error)\([^)]*\);?/i', '', $js_code);
        // Remove single-line comments
        $js_code = preg_replace('/\/\/.*$/m', '', $js_code);
        // Remove multi-line comments
        $js_code = preg_replace('/\/\*.*?\*\//s', '', $js_code);
    }
    return $js_code;
}

/**
 * Output JavaScript code with security considerations
 */
function output_secure_js($js_code) {
    echo minify_js_for_production($js_code);
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Secure redirect
 */
function secure_redirect($url) {
    // Prevent open redirect vulnerabilities
    $allowed_hosts = [
        $_SERVER['HTTP_HOST'],
        'localhost',
        '127.0.0.1',
        SERVER_IP
    ];
    
    $parsed = parse_url($url);
    if (isset($parsed['host']) && !in_array($parsed['host'], $allowed_hosts)) {
        $url = '/';
    }
    
    header('Location: ' . $url, true, 302);
    exit;
}

/**
 * Rate limiting
 */
function check_rate_limit($key, $max_attempts = 5, $time_window = 300) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $rate_key = 'rate_limit_' . $key;
    $now = time();
    
    if (!isset($_SESSION[$rate_key])) {
        $_SESSION[$rate_key] = ['count' => 1, 'start' => $now];
        return true;
    }
    
    $data = $_SESSION[$rate_key];
    
    if ($now - $data['start'] > $time_window) {
        $_SESSION[$rate_key] = ['count' => 1, 'start' => $now];
        return true;
    }
    
    if ($data['count'] >= $max_attempts) {
        return false;
    }
    
    $_SESSION[$rate_key]['count']++;
    return true;
}

/**
 * Mask sensitive data for logging
 */
function mask_sensitive_data($data, $fields = ['password', 'token', 'secret', 'key']) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $fields)) {
                $data[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $data[$key] = mask_sensitive_data($value, $fields);
            }
        }
    }
    return $data;
}

?>
