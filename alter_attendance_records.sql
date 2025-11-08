-- ALTER TABLE to add columns for morning in/out, afternoon in/out
ALTER TABLE attendance_records
  ADD COLUMN morning_in DATETIME DEFAULT NULL,
  ADD COLUMN morning_out DATETIME DEFAULT NULL,
  ADD COLUMN afternoon_in DATETIME DEFAULT NULL,
  ADD COLUMN afternoon_out DATETIME DEFAULT NULL;

-- Remove time_period and attendance_time columns if not needed
-- ALTER TABLE attendance_records DROP COLUMN time_period;
-- ALTER TABLE attendance_records DROP COLUMN attendance_time;

-- status column already exists
-- ENUM('present','absent','late','excuse')

-- After running this, update PHP code to use these columns for scan events.