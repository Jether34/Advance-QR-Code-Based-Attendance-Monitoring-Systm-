-- ============================================
-- COMPLETE DATABASE SETUP FOR ATTENDANCE SYSTEM
-- Run this file to create all tables in the correct order
-- ============================================

-- Create database (run separately if needed)
-- CREATE DATABASE IF NOT EXISTS attendance_qr_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE attendance_qr_system;

-- Table for teachers (MISSING from original files)
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male','Female','Other'),
    grade_level VARCHAR(10),
    strand VARCHAR(50),
    section_block VARCHAR(20),
    faculty VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for students
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male','Female','Other'),
    grade_level VARCHAR(10),
    strand VARCHAR(50),
    section_block VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for attendance records (with all columns)
CREATE TABLE IF NOT EXISTS attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    attendance_date DATE NOT NULL,
    attendance_time DATETIME NOT NULL,
    status ENUM('present','absent','late','excuse') DEFAULT 'present',
    time_period VARCHAR(20) DEFAULT 'scan',
    morning_in DATETIME DEFAULT NULL,
    morning_out DATETIME DEFAULT NULL,
    afternoon_in DATETIME DEFAULT NULL,
    afternoon_out DATETIME DEFAULT NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- Table for user profile edits
CREATE TABLE IF NOT EXISTS profile_edits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    gender ENUM('Male','Female','Other'),
    profile_picture VARCHAR(255),
    edited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES teachers(id) ON DELETE CASCADE
);

-- Table for community wall posts (supports text, image, file)
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT,
    image_path VARCHAR(255),
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL
);

-- Table for post likes
CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id)
);

-- Table for post comments
CREATE TABLE IF NOT EXISTS post_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for system events / traffic logs
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
);

-- Insert sample data (optional)
-- Sample teachers
INSERT IGNORE INTO teachers (full_name, email, password, gender, grade_level, strand, section_block, faculty) VALUES
('Mr. John Santos', 'mr.santos@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', '11', 'STEM', '1', 'STEM'),
('Ms. Maria Garcia', 'ms.garcia@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', '12', 'ABM', '1', 'ABM'),
('Mr. Pedro Reyes', 'mr.reyes@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', '11', 'HUMSS', '2', 'HUMSS');

-- Sample students
INSERT IGNORE INTO students (student_id, full_name, email, password, gender, grade_level, strand, section_block) VALUES
('STU001', 'Juan Dela Cruz', 'juan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', '11', 'STEM', '1'),
('STU002', 'Maria Clara', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', '11', 'STEM', '1'),
('STU003', 'Jose Rizal Jr.', 'jose@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', '12', 'ABM', '1');

-- Create indexes for better performance
CREATE INDEX idx_attendance_student_date ON attendance_records(student_id, attendance_date);
CREATE INDEX idx_attendance_date ON attendance_records(attendance_date);
CREATE INDEX idx_posts_user ON posts(user_id);
CREATE INDEX idx_posts_created ON posts(created_at);
CREATE INDEX idx_profile_edits_user ON profile_edits(user_id);

SHOW TABLES;
SELECT 'Database setup completed successfully!' as Status;
