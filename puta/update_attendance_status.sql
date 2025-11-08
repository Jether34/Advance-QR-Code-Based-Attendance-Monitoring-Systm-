-- Update attendance_records table to support new status types
ALTER TABLE attendance_records 
MODIFY COLUMN status ENUM('present','absent','late','excuse','morning_half_day','afternoon_half_day') DEFAULT 'absent';

-- Add teacher_id column (for tracking which teacher recorded the attendance)
ALTER TABLE attendance_records 
ADD COLUMN teacher_id INT DEFAULT NULL;

-- Add foreign key constraint
ALTER TABLE attendance_records 
ADD CONSTRAINT fk_attendance_teacher 
FOREIGN KEY (teacher_id) REFERENCES teachers(id);