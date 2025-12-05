<?php
// logging.php - simple event logging helper for system audit/traffic
require_once __DIR__ . '/db.php';

// Generate or retrieve request ID for correlation
function get_request_id() {
    static $requestId = null;
    if ($requestId === null) {
        $requestId = bin2hex(random_bytes(8));
    }
    return $requestId;
}

function log_event($pdo, $eventType, $attrs = []) {
    if (!$pdo) {
        $pdo = get_db();
    }

    // Ensure table exists (idempotent)
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_type VARCHAR(50) NOT NULL,
        user_role VARCHAR(20) DEFAULT NULL,
        user_id INT DEFAULT NULL,
        email VARCHAR(150) DEFAULT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        success TINYINT(1) DEFAULT 1,
        message VARCHAR(255) DEFAULT NULL,
        request_id VARCHAR(32) DEFAULT NULL,
        method VARCHAR(10) DEFAULT NULL,
        path VARCHAR(255) DEFAULT NULL,
        status_code INT DEFAULT NULL,
        latency_ms INT DEFAULT NULL,
        object_type VARCHAR(50) DEFAULT NULL,
        object_id VARCHAR(50) DEFAULT NULL,
        context_json TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Gather environment info
    $ip = null;
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($parts[0]);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    }
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $user_role = $attrs['user_role'] ?? null;
    $user_id   = $attrs['user_id'] ?? null;
    $email     = $attrs['email'] ?? null;
    $success   = isset($attrs['success']) ? (int)$attrs['success'] : 1;
    $message   = $attrs['message'] ?? null;

    // Request context
    $request_id  = $attrs['request_id'] ?? get_request_id();
    $method      = $attrs['method'] ?? $_SERVER['REQUEST_METHOD'] ?? null;
    $path        = $attrs['path'] ?? $_SERVER['PHP_SELF'] ?? null;
    $status_code = $attrs['status_code'] ?? null;
    $latency_ms  = $attrs['latency_ms'] ?? null;

    // Object context
    $object_type = $attrs['object_type'] ?? null;
    $object_id   = $attrs['object_id'] ?? null;
    $context_json = $attrs['context_json'] ?? null;
    if (is_array($context_json)) {
        $context_json = json_encode($context_json, JSON_UNESCAPED_UNICODE);
    }

    $stmt = $pdo->prepare("INSERT INTO system_events (event_type, user_role, user_id, email, ip_address, user_agent, success, message, request_id, method, path, status_code, latency_ms, object_type, object_id, context_json)
                           VALUES (:event_type, :user_role, :user_id, :email, :ip, :ua, :success, :message, :request_id, :method, :path, :status_code, :latency_ms, :object_type, :object_id, :context_json)");
    $stmt->execute([
        ':event_type'  => $eventType,
        ':user_role'   => $user_role,
        ':user_id'     => $user_id,
        ':email'       => $email,
        ':ip'          => $ip,
        ':ua'          => $ua,
        ':success'     => $success,
        ':message'     => $message,
        ':request_id'  => $request_id,
        ':method'      => $method,
        ':path'        => $path,
        ':status_code' => $status_code,
        ':latency_ms'  => $latency_ms,
        ':object_type' => $object_type,
        ':object_id'   => $object_id,
        ':context_json'=> $context_json,
    ]);
}
