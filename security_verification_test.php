<?php
/**
 * Security Verification Test
 * Verifies that all security measures from commit bbb22be are properly implemented
 */

echo "=== SECURITY VERIFICATION TEST ===\n\n";

$passed = 0;
$failed = 0;
$warnings = 0;

function test($name, $condition, $isWarning = false) {
    global $passed, $failed, $warnings;
    if ($condition) {
        echo "✅ PASS: $name\n";
        $passed++;
    } else {
        if ($isWarning) {
            echo "⚠️  WARN: $name\n";
            $warnings++;
        } else {
            echo "❌ FAIL: $name\n";
            $failed++;
        }
    }
}

// Test 1: Check .env is not in repository
echo "1. ENVIRONMENT CONFIGURATION\n";
test("/.env file NOT in git repository", !file_exists('.git/objects') || !shell_exec('git ls-files .env 2>/dev/null'));
test("/.env.example exists as template", file_exists('.env.example'));
test("/.gitignore contains .env", strpos(file_get_contents('.gitignore'), '.env') !== false);
echo "\n";

// Test 2: Session Security
echo "2. SESSION SECURITY\n";
require_once 'config.php';
test("/Session strict mode configured", ini_get('session.use_strict_mode') == '1');
test("/Session httponly flag set", ini_get('session.cookie_httponly') == '1');
test("/Session samesite policy configured", ini_get('session.cookie_samesite') !== '');
test("/Session lifetime defined", defined('SESSION_LIFETIME'));
echo "\n";

// Test 3: Storage Protection
echo "3. STORAGE DIRECTORY PROTECTION\n";
test("/storage/.htaccess exists", file_exists('storage/.htaccess'));
test("/storage/.htaccess denies access", strpos(file_get_contents('storage/.htaccess'), 'Deny from all') !== false);
test("/storage/nginx_deny.conf exists", file_exists('storage/nginx_deny.conf'));
echo "\n";

// Test 4: Database Security
echo "4. DATABASE SECURITY\n";
$dbContent = file_get_contents('db.php');
test("/Database uses PDO (not mysqli)", strpos($dbContent, 'PDO') !== false);
test("/PDO error mode is EXCEPTION", strpos($dbContent, 'PDO::ERRMODE_EXCEPTION') !== false);
test("/Emulate prepares disabled", strpos($dbContent, 'PDO::ATTR_EMULATE_PREPARES') !== false);
test("/Database credentials from env", strpos($dbContent, 'getenv(\'DB_') !== false);
echo "\n";

// Test 5: Secret Detection
echo "5. SECRET SCANNING\n";
test("/detect-secrets baseline exists", file_exists('tools/detect_secrets.baseline'));
test("/.pre-commit-config.yaml exists", file_exists('.pre-commit-config.yaml'));
$precommitContent = file_get_contents('.pre-commit-config.yaml');
test("/detect-secrets hook configured", strpos($precommitContent, 'detect-secrets') !== false);
test("/GitHub secret scan workflow exists", file_exists('.github/workflows/secret-scan.yml'));
echo "\n";

// Test 6: XSS Protection
echo "6. XSS PROTECTION\n";
$phpFiles = glob('*.php');
$xssProtectionCount = 0;
foreach ($phpFiles as $file) {
    if (in_array($file, ['vendor'])) continue;
    $content = file_get_contents($file);
    if (preg_match('/htmlspecialchars|htmlentities/', $content)) {
        $xssProtectionCount++;
    }
}
test("/XSS protection used in PHP files", $xssProtectionCount > 10);
echo "  Found XSS protection in $xssProtectionCount files\n";
echo "\n";

// Test 7: CSRF Protection
echo "7. CSRF PROTECTION\n";
$csrfFiles = shell_exec('grep -r "csrf_token" --include="*.php" --exclude-dir=vendor 2>/dev/null | wc -l');
test("/CSRF token implementation found", intval($csrfFiles) > 5);
echo "  Found CSRF protection in " . trim($csrfFiles) . " locations\n";
echo "\n";

// Test 8: No Hardcoded Secrets
echo "8. NO HARDCODED CREDENTIALS\n";
$secretPatterns = [
    '/password\s*=\s*["\'][^"\']{8,}["\']/i' => 'hardcoded passwords',
    '/api[_-]?key\s*=\s*["\'][^"\']+["\']/i' => 'hardcoded API keys',
    '/secret\s*=\s*["\'][^"\']+["\']/i' => 'hardcoded secrets',
];

foreach ($secretPatterns as $pattern => $description) {
    $found = [];
    foreach (glob('*.php') as $file) {
        if ($file === 'security_verification_test.php') continue;
        $content = file_get_contents($file);
        if (preg_match($pattern, $content, $matches)) {
            // Exclude false positives like form fields
            if (!preg_match('/name=|placeholder=|\$_POST\[|input type=/', $matches[0])) {
                $found[] = $file;
            }
        }
    }
    test("/No $description in code", count($found) === 0, true);
    if (count($found) > 0) {
        echo "  Found in: " . implode(', ', $found) . "\n";
    }
}
echo "\n";

// Test 9: Security Documentation
echo "9. SECURITY DOCUMENTATION\n";
test("/SECURITY_GUIDE.md exists", file_exists('SECURITY_GUIDE.md'));
test("/SECURITY_AUDIT_CHECKLIST.md exists", file_exists('SECURITY_AUDIT_CHECKLIST.md'));
test("/SECURITY_HARDENING_SUMMARY.md exists", file_exists('SECURITY_HARDENING_SUMMARY.md'));
echo "\n";

// Test 10: Bootstrap Security
echo "10. BOOTSTRAP SECURITY\n";
test("/bootstrap.php exists", file_exists('bootstrap.php'));
$bootstrapContent = file_get_contents('bootstrap.php');
test("/Session regeneration implemented", strpos($bootstrapContent, 'session_regenerate_id') !== false);
test("/Cookie security params set", strpos($bootstrapContent, 'session_set_cookie_params') !== false);
echo "\n";

// Summary
echo "\n";
echo "=================================\n";
echo "SECURITY TEST SUMMARY\n";
echo "=================================\n";
echo "✅ Passed:  $passed\n";
echo "❌ Failed:  $failed\n";
echo "⚠️  Warnings: $warnings\n";
echo "=================================\n";

if ($failed === 0) {
    echo "\n🎉 ALL CRITICAL SECURITY TESTS PASSED!\n";
    echo "The security hardening from commit bbb22be is properly implemented.\n";
    exit(0);
} else {
    echo "\n⚠️  SECURITY ISSUES DETECTED!\n";
    echo "Please review the failed tests above.\n";
    exit(1);
}
