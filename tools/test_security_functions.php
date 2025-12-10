<?php
// Quick functional test script
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['PHP_SELF'] = '/test.php';
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

echo "=== Testing Bootstrap & Security Functions ===\n\n";

// Test 1: Bootstrap loads
echo "Test 1: Bootstrap loading... ";
require_once __DIR__ . '/../bootstrap.php';
echo "✓\n";

// Test 2: Session is active
echo "Test 2: Session active... ";
echo (session_status() === PHP_SESSION_ACTIVE ? "✓\n" : "✗\n");

// Test 3: CSRF token generation
echo "Test 3: CSRF token generation... ";
require_once __DIR__ . '/../security_utils.php';
$token = generate_csrf_token();
echo (strlen($token) > 20 ? "✓ (token: " . substr($token, 0, 8) . "...)\n" : "✗\n");

// Test 4: CSRF token verification
echo "Test 4: CSRF token verification... ";
$verified = verify_csrf_token($token);
echo ($verified ? "✓\n" : "✗\n");

// Test 5: Invalid token rejection
echo "Test 5: Invalid token rejection... ";
$rejected = !verify_csrf_token('invalid_token_12345');
echo ($rejected ? "✓\n" : "✗\n");

// Test 6: Sanitization
echo "Test 6: XSS sanitization... ";
$xss_test = '<script>alert("xss")</script>';
$sanitized = sanitize_output($xss_test);
echo (strpos($sanitized, '<') === false ? "✓\n" : "✗\n");

// Test 7: Rate limiting
echo "Test 7: Rate limiting... ";
$limit1 = check_rate_limit('test_key', 2, 300);
$limit2 = check_rate_limit('test_key', 2, 300);
$limit3 = check_rate_limit('test_key', 2, 300);
echo ($limit1 && $limit2 && !$limit3 ? "✓\n" : "✗\n");

echo "\n=== All Basic Tests Complete ===\n";
?>
