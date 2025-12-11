-- Add correction tracking columns to attendance_records table
ALTER TABLE attendance_records
ADD COLUMN IF NOT EXISTS correction_reason VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS corrected_by VARCHAR(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS corrected_at DATETIME DEFAULT NULL;

-- Create an index for faster lookups
ALTER TABLE attendance_records
ADD INDEX IF NOT EXISTS idx_correction_tracking (corrected_by, corrected_at);
