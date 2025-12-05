-- Migration: System Events / Traffic Logs
CREATE TABLE IF NOT EXISTS system_events (
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_request_id (request_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
