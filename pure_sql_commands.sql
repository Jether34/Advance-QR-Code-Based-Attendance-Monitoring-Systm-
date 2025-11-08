-- ============================================
-- PURE SQL COMMANDS FOR ATTENDANCE QR SYSTEM
-- Copy and paste these commands directly into phpMyAdmin or MySQL Workbench
-- ============================================

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS attendance_qr_system 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Step 2: Use the database
USE attendance_qr_system;

-- Step 3: Create all tables

-- Teachers table
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

-- Students table
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

-- Attendance records table
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

-- Profile edits table
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

-- Posts table
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT,
    image_path VARCHAR(255),
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL
);

-- Post likes table
CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id)
);

-- Post comments table
CREATE TABLE IF NOT EXISTS post_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Step 4: Insert sample data

-- Sample teachers (password is 'password123' hashed)
INSERT INTO teachers (full_name, email, password, gender, grade_level, strand, section_block, faculty) VALUES
('Mr. John Santos', 'teacher@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', '11', 'STEM', '1', 'STEM'),
('Ms. Maria Garcia', 'ms.garcia@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', '12', 'ABM', '1', 'ABM'),
('Mr. Pedro Reyes', 'mr.reyes@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', '11', 'HUMSS', '2', 'HUMSS');

-- Sample students (password is 'password123' hashed)
INSERT INTO students (student_id, full_name, email, password, gender, grade_level, strand, section_block) VALUES
('STU001', 'Juan Dela Cruz', 'student@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', '11', 'STEM', '1'),
('STU002', 'Maria Clara', 'maria@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', '11', 'STEM', '1'),
('STU003', 'Jose Rizal Jr.', 'jose@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', '12', 'ABM', '1');

-- Step 5: Create indexes for performance
CREATE INDEX idx_attendance_student_date ON attendance_records(student_id, attendance_date);
CREATE INDEX idx_attendance_date ON attendance_records(attendance_date);
CREATE INDEX idx_posts_user ON posts(user_id);
CREATE INDEX idx_posts_created ON posts(created_at);
CREATE INDEX idx_profile_edits_user ON profile_edits(user_id);

-- Step 6: Verify the setup
SHOW TABLES;

SELECT 'Database setup completed successfully!' as Status;

SELECT 
    'teachers' as table_name, 
    COUNT(*) as record_count 
FROM teachers
UNION ALL
SELECT 
    'students' as table_name, 
    COUNT(*) as record_count 
FROM students;