<?php
// api_config.php - Centralized API endpoint configuration
// This file should be included in any file that needs to call APIs

// Get the current server domain (works for both PC and phone access)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];

// Detect the server IP to determine which network we're on
$server_ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';

// Determine Ollama API endpoint based on server IP
// Supports multiple network configurations
if (strpos($server_ip, '192.168.254.') === 0) {
    // Network 192.168.254.x (New network configuration)
    define('OLLAMA_API_URL', 'http://192.168.254.254:11434/api/generate');
} elseif (strpos($server_ip, '192.168.1.') === 0) {
    // Network 192.168.1.x (Original network configuration)
    define('OLLAMA_API_URL', 'http://192.168.1.12:11434/api/generate');
} else {
    // Fallback to localhost for local development
    define('OLLAMA_API_URL', 'http://localhost:11434/api/generate');
}

// Database endpoint - use environment variables when possible to avoid committing secrets
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'pns_attendance');

// Application endpoints
define('APP_URL', $protocol . '://' . $host);
define('API_BASE', APP_URL . '/api');

// For debugging - shows which endpoint is being used
// error_log('API Config: Using Ollama at ' . OLLAMA_API_URL);
// error_log('API Config: Using App URL ' . APP_URL);
