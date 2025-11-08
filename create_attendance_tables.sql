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
    section_block VARCHAR(20)
);

-- Table for attendance records
CREATE TABLE IF NOT EXISTS attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    attendance_date DATE NOT NULL,
    attendance_time DATETIME NOT NULL,
    status ENUM('present','absent','late','excuse') DEFAULT 'present',
    time_period VARCHAR(20) DEFAULT 'scan',
    FOREIGN KEY (student_id) REFERENCES students(student_id)
);
