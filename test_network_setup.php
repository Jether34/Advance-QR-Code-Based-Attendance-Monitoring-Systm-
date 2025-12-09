<?php
// test_network_setup.php - Verify mobile testing is ready
header('Content-Type: application/json');

$status = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server_ip' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'unknown',
    'pc_ip' => gethostbyname(gethostname()),
    'server_url' => 'http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'unknown') . '/puta',
    'network_detected' => 'unknown',
    'tests' => []
];

// Detect which network we're on
$server_ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
if (strpos($server_ip, '192.168.254.') === 0) {
    $status['network_detected'] = '192.168.254.x (Network B)';
    $status['ollama_endpoint'] = 'http://192.168.254.254:11434';
} elseif (strpos($server_ip, '192.168.1.') === 0) {
    $status['network_detected'] = '192.168.1.x (Network A - Original)';
    $status['ollama_endpoint'] = 'http://192.168.1.12:11434';
} else {
    $status['network_detected'] = 'localhost (Development)';
    $status['ollama_endpoint'] = 'http://localhost:11434';
}

// Test 1: Check if server IP is configured correctly
$status['tests']['config_file'] = file_exists(__DIR__ . '/config.php') ? 'OK' : 'MISSING';

// Test 2: Check if api_config.php exists
$status['tests']['api_config'] = file_exists(__DIR__ . '/api_config.php') ? 'OK' : 'MISSING';

// Test 3: Database connection
try {
    require_once __DIR__ . '/db.php';
    $pdo = get_db();
    $status['tests']['database'] = 'CONNECTED';
} catch (Exception $e) {
    $status['tests']['database'] = 'ERROR: ' . $e->getMessage();
}

// Test 4: Ollama API accessibility (from PC perspective)
$ollamaCheck = @file_get_contents('http://localhost:11434/api/tags');
$status['tests']['ollama_local'] = $ollamaCheck !== false ? 'RUNNING' : 'NOT_AVAILABLE';

// Test 5: Session configuration
$status['tests']['session_started'] = session_status() === PHP_SESSION_ACTIVE ? 'YES' : 'NO';

// Test 6: Key files exist
$requiredFiles = [
    'config.php',
    'api_config.php',
    'db.php',
    'login.php',
    'student_dashboard.php',
    'reviewer_ai.php',
    'pdf_to_csv_converter.php'
];

$status['tests']['required_files'] = count($requiredFiles);
$status['tests']['required_files_status'] = [];
foreach ($requiredFiles as $file) {
    $status['tests']['required_files_status'][$file] = file_exists(__DIR__ . '/' . $file) ? '✓' : '✗';
}

// Test 7: Vendor autoloader (for PDF parser)
$status['tests']['composer_autoloader'] = file_exists(__DIR__ . '/vendor/autoload.php') ? 'OK' : 'MISSING';

echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
