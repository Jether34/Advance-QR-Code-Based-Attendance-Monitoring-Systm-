<?php
// api_config.php - Centralized API endpoint configuration
// This file should be included in any file that needs to call APIs

// Get the current server domain (works for both PC and phone access)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];

// Ollama API endpoint - when accessed from phone, routes through the PC's IP
// Since Ollama runs on localhost:11434 on the PC, we need to route through the PC's IP
define('OLLAMA_API_URL', 'http://192.168.1.12:11434/api/generate');

// Database endpoint
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pns_attendance');

// Application endpoints
define('APP_URL', $protocol . '://' . $host);
define('API_BASE', APP_URL . '/api');

// For debugging - shows which endpoint is being used
// error_log('API Config: Using Ollama at ' . OLLAMA_API_URL);
// error_log('API Config: Using App URL ' . APP_URL);
