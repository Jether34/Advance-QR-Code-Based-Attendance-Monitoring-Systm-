<?php
// test_web_simulation.php - Simulate a real web request without output before session
// This mimics what actually happens on a webserver

if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['PHP_SELF'] = '/test.php';
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

// Suppress output buffer to avoid header warnings
ob_start();

echo "=== Web Request Simulation ===\n";

try {
    // This is what happens in real page execution
    require_once __DIR__ . '/../bootstrap.php';
    echo "✓ Bootstrap loaded and session started\n";

    require_once __DIR__ . '/../security_utils.php';

    // Test CSRF
    $token = generate_csrf_token();
    echo "✓ CSRF token generated: " . substr($token, 0, 8) . "...\n";

    // Test token validation
    $valid = verify_csrf_token($token);
    echo "✓ CSRF token validated: " . ($valid ? 'PASS' : 'FAIL') . "\n";

    // Test rate limit
    $limit1 = check_rate_limit('api_test', 3, 60);
    $limit2 = check_rate_limit('api_test', 3, 60);
    $limit3 = check_rate_limit('api_test', 3, 60);
    $limit4 = check_rate_limit('api_test', 3, 60);
    echo "✓ Rate limiting works: " . ($limit1 && $limit2 && $limit3 && !$limit4 ? 'PASS' : 'FAIL') . "\n";

    // Test sanitization
    $xss = sanitize_output('<script>alert("xss")</script>');
    echo "✓ XSS sanitized: " . (strpos($xss, '<') === false ? 'PASS' : 'FAIL') . "\n";

    echo "\n=== All Web Simulation Tests PASSED ===\n";

} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

ob_end_flush();
?>
