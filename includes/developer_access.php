<?php
// includes/developer_access.php - IP allowlist + emergency token gate for developer-only access
// Usage:
//   require_once __DIR__ . '/bootstrap.php';
//   require_once __DIR__ . '/includes/developer_access.php';
//   enforce_developer_access();
// Configure via .env:
//   DEVELOPER_WHITELIST=192.168.1.10,203.0.113.5
//   DEV_ACCESS_TOKEN=your-long-random-token

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) return $_SERVER['HTTP_X_REAL_IP'];
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function enforce_developer_access() {
    $ip = get_client_ip();
    $whitelist = getenv('DEVELOPER_WHITELIST') ?: '';
    $allowedIps = array_filter(array_map('trim', explode(',', $whitelist)));

    $token = getenv('DEV_ACCESS_TOKEN') ?: '';
    $provided = $_GET['dev_token'] ?? $_POST['dev_token'] ?? ($_SERVER['HTTP_X_DEV_ACCESS'] ?? '');

    $isAllowedIp = in_array($ip, $allowedIps, true);
    $isValidToken = ($token && hash_equals($token, $provided));

    // Developers logged in with role can also pass
    $isDeveloperRole = (isset($_SESSION['role']) && $_SESSION['role'] === 'developer');

    if ($isAllowedIp || $isValidToken || $isDeveloperRole) {
        return true;
    }

    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Forbidden</title></head><body>';
    echo '<h1>403 Forbidden</h1>';
    echo '<p>Developer access required. If you are a whitelisted developer, ensure your IP is listed in DEVELOPER_WHITELIST or provide the dev access token.</p>';
    echo '</body></html>';
    exit;
}
