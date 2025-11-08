-- Migration: Insert default data and configurations
-- Version: 1.1.0
-- Date: 2025-11-08
-- Description: Default users, settings, and sample data for testing

USE attendance_qr_system;

-- Insert default admin teacher
INSERT IGNORE INTO teachers (username, password, full_name, email, grade_level, strand, section_block, role) VALUES
('admin', '$2y$10$YourHashedPasswordHere', 'System Administrator', 'admin@school.edu', '12', 'STEM', 'A', 'admin'),
('teacher1', '$2y$10$YourHashedPasswordHere', 'John Doe Teacher', 'john.teacher@school.edu', '11', 'STEM', 'A', 'teacher'),
('teacher2', '$2y$10$YourHashedPasswordHere', 'Jane Smith Teacher', 'jane.teacher@school.edu', '12', 'HUMSS', 'B', 'teacher');

-- Insert sample students for testing
INSERT IGNORE INTO students (username, password, student_id, lrn, full_name, email, gender, grade_level, strand, section_block) VALUES
('student001', '$2y$10$YourHashedPasswordHere', 'STU001', 'LRN001', 'Alice Johnson', 'alice@student.edu', 'female', '11', 'STEM', 'A'),
('student002', '$2y$10$YourHashedPasswordHere', 'STU002', 'LRN002', 'Bob Smith', 'bob@student.edu', 'male', '11', 'STEM', 'A'),
('student003', '$2y$10$YourHashedPasswordHere', 'STU003', 'LRN003', 'Carol Davis', 'carol@student.edu', 'female', '12', 'HUMSS', 'B'),
('student004', '$2y$10$YourHashedPasswordHere', 'STU004', 'LRN004', 'David Wilson', 'david@student.edu', 'male', '12', 'HUMSS', 'B'),
('student005', '$2y$10$YourHashedPasswordHere', 'STU005', 'LRN005', 'Eva Brown', 'eva@student.edu', 'female', '11', 'STEM', 'A');

-- Insert default system settings
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description, is_editable) VALUES
('app_name', 'Smart Attendance System', 'string', 'Application name displayed in UI', true),
('app_version', '3.1.0', 'string', 'Current application version', false),
('timezone', 'Asia/Manila', 'string', 'Default timezone for the application', true),
('qr_code_size', '200', 'integer', 'Default QR code size in pixels', true),
('session_timeout', '3600', 'integer', 'Session timeout in seconds', true),
('max_login_attempts', '5', 'integer', 'Maximum login attempts before lockout', true),
('enable_mobile_access', 'true', 'boolean', 'Enable mobile device access', true),
('enable_audit_logging', 'true', 'boolean', 'Enable audit trail logging', true),
('backup_retention_days', '30', 'integer', 'Number of days to keep backup files', true),
('notification_enabled', 'false', 'boolean', 'Enable email notifications', true);

-- Insert sample attendance records for current date (for testing)
INSERT IGNORE INTO attendance_records (student_id, attendance_date, morning_in, status) VALUES
('STU001', CURDATE(), CONCAT(CURDATE(), ' 07:30:00'), 'present'),
('STU002', CURDATE(), CONCAT(CURDATE(), ' 07:45:00'), 'present'),
('STU003', CURDATE(), CONCAT(CURDATE(), ' 08:15:00'), 'late');

-- Create views for commonly used queries
CREATE OR REPLACE VIEW daily_attendance_summary AS
SELECT 
    ar.attendance_date,
    s.grade_level,
    s.strand,
    s.section_block,
    COUNT(*) as total_students,
    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
    SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_count,
    ROUND((SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_percentage
FROM attendance_records ar
JOIN students s ON ar.student_id = s.student_id
GROUP BY ar.attendance_date, s.grade_level, s.strand, s.section_block
ORDER BY ar.attendance_date DESC, s.grade_level, s.strand, s.section_block;

CREATE OR REPLACE VIEW student_attendance_overview AS
SELECT 
    s.student_id,
    s.full_name,
    s.grade_level,
    s.strand,
    s.section_block,
    COUNT(ar.id) as total_days_recorded,
    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_days,
    SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
    SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_days,
    ROUND((SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) / NULLIF(COUNT(ar.id), 0)) * 100, 2) as attendance_percentage,
    MAX(ar.attendance_date) as last_attendance_date
FROM students s
LEFT JOIN attendance_records ar ON s.student_id = ar.student_id
GROUP BY s.student_id, s.full_name, s.grade_level, s.strand, s.section_block
ORDER BY s.grade_level, s.strand, s.section_block, s.full_name;