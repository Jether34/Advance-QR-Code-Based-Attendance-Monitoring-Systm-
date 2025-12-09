<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/logging.php';
require_once __DIR__ . '/auto_reset_7pm.php'; // Auto-reset system
date_default_timezone_set('Asia/Manila');
session_start();

$request_start = microtime(true);

// Use attendance date (auto-adjusts after 7PM for next day)
$attendance_date = get_attendance_date();

// Accept JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if(!$data){
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
    exit;
}
$code = $data['code'] ?? null;
// ensure response is JSON
header('Content-Type: application/json; charset=utf-8');
$type = $data['type'] ?? 'qr';

if(!$code){
    try {
        $pdo = get_db();
        log_event($pdo, 'attendance_scan', [
            'success' => 0,
            'message' => 'Missing code',
            'context_json' => ['type' => $type],
        ]);
    } catch (Throwable $ignored) {}
    echo json_encode(['success'=>false,'error'=>'Missing code']); exit;
}

// Time-based attendance rules
function determineAttendancePeriod() {
    $current_time = date('H:i:s');
    $hour = (int)date('H');
    $minute = (int)date('i');
    $time_minutes = ($hour * 60) + $minute;
    
    // Convert time boundaries to minutes
    $morning_in_start = 6 * 60;          // 6:00 AM = 360 minutes
    $morning_in_end = 11 * 60 + 35;      // 11:35 AM = 695 minutes
    $morning_out_start = 11 * 60 + 36;   // 11:36 AM = 696 minutes
    $morning_out_end = 12 * 60 + 45;     // 12:45 PM = 765 minutes
    $afternoon_in_start = 12 * 60 + 46;  // 12:46 PM = 766 minutes
    $afternoon_in_end = 15 * 60 + 45;    // 3:45 PM = 945 minutes
    $afternoon_out_start = 15 * 60 + 46; // 3:46 PM = 946 minutes
    $afternoon_out_end = 19 * 60;        // 7:00 PM = 1140 minutes
    
    // Morning In: 6:00 AM to 11:35 AM
    if ($time_minutes >= $morning_in_start && $time_minutes <= $morning_in_end) {
        // Check if it's half day present (6:00-9:00) or late (9:01-11:35)
        $half_day_end = 9 * 60; // 9:00 AM
        if ($time_minutes <= $half_day_end) {
            return ['period' => 'morning_in', 'sub_status' => 'present', 'description' => 'Morning In (On Time)'];
        } else {
            return ['period' => 'morning_in', 'sub_status' => 'late', 'description' => 'Morning In (Late)'];
        }
    }
    
    // Morning Out: 11:36 AM to 12:45 PM
    if ($time_minutes >= $morning_out_start && $time_minutes <= $morning_out_end) {
        return ['period' => 'morning_out', 'sub_status' => 'present', 'description' => 'Morning Out'];
    }
    
    // Afternoon In: 12:46 PM to 3:45 PM
    if ($time_minutes >= $afternoon_in_start && $time_minutes <= $afternoon_in_end) {
        return ['period' => 'afternoon_in', 'sub_status' => 'present', 'description' => 'Afternoon In'];
    }
    
    // Afternoon Out: 3:46 PM to 7:00 PM
    if ($time_minutes >= $afternoon_out_start && $time_minutes <= $afternoon_out_end) {
        return ['period' => 'afternoon_out', 'sub_status' => 'present', 'description' => 'Afternoon Out'];
    }
    
    // Outside valid scanning hours
    return ['period' => null, 'sub_status' => 'invalid', 'description' => 'Outside scanning hours'];
}

// Function to calculate final attendance status based on scan patterns
function calculateAttendanceStatus($record) {
    $morning_in = !empty($record['morning_in']);
    $morning_out = !empty($record['morning_out']);
    $afternoon_in = !empty($record['afternoon_in']);
    $afternoon_out = !empty($record['afternoon_out']);
    
    // Check if morning in was late (9:01 AM - 11:35 AM)
    $morning_late = false;
    if ($morning_in) {
        $morning_time = strtotime($record['morning_in']);
        $hour = (int)date('H', $morning_time);
        $minute = (int)date('i', $morning_time);
        $time_minutes = ($hour * 60) + $minute;
        if ($time_minutes > (9 * 60)) { // After 9:00 AM
            $morning_late = true;
        }
    }
    
    // Perfect attendance: All 4 scans completed = PRESENT (regardless of late)
    if ($morning_in && $morning_out && $afternoon_in && $afternoon_out) {
        return 'present';
    }
    
    // Full day with missing out scans: both morning and afternoon IN scans
    if ($morning_in && $afternoon_in) {
        if ($morning_late) {
            return 'late'; // Late if morning in was late
        }
        return 'present';
    }
    
    // Morning half day: only morning scans (in required, out optional)
    if ($morning_in && !$afternoon_in) {
        return 'morning_half_day';
    }
    
    // Afternoon half day: only afternoon scans (in required, out optional)
    if (!$morning_in && $afternoon_in) {
        return 'afternoon_half_day';
    }
    
    // No valid scans = absent
    return 'absent';
}

try {

    $pdo = get_db();
    $teacher_id = $_SESSION['user_id'] ?? null;
    if (!$teacher_id) {
        echo json_encode(['success'=>false,'error'=>'Teacher not logged in']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>'DB connection failed: ' . $e->getMessage()]);
    exit;
}

// Validate student ID in students table 

try {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = :code LIMIT 1');
    $stmt->execute([':code'=>$code]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>'Student query failed: ' . $e->getMessage()]);
    exit;
}
if(!$student){
    log_event($pdo, 'attendance_scan', [
        'success' => 0,
        'message' => 'Student not found',
        'object_type' => 'students',
        'object_id' => $code,
        'context_json' => ['type' => $type, 'scan_source' => $type],
    ]);
    echo json_encode(['success'=>false,'error'=>'Student not found']); exit;
}

// Determine current scanning period based on time
$period_info = determineAttendancePeriod();
$period = $period_info['period'];
$period_description = $period_info['description'];

// Check if scanning is allowed at this time
if ($period === null) {
    log_event($pdo, 'attendance_scan', [
        'success' => 0,
        'message' => 'Scan attempted outside valid hours',
        'object_type' => 'students',
        'object_id' => $student['student_id'],
        'context_json' => [
            'type' => $type,
            'current_time' => date('H:i:s'),
            'reason' => 'Outside scanning hours (6AM-7PM)'
        ],
    ]);
    echo json_encode([
        'success' => false,
        'error' => 'Scanning not allowed at this time',
        'message' => 'Valid scanning hours: 6:00 AM - 7:00 PM',
        'current_time' => date('h:i A'),
        'valid_periods' => [
            'Morning In: 6:00 AM - 11:35 AM',
            'Morning Out: 11:36 AM - 12:45 PM',
            'Afternoon In: 12:46 PM - 3:45 PM',
            'Afternoon Out: 3:46 PM - 7:00 PM'
        ]
    ]);
    exit;
}

// Record attendance
$now = date('Y-m-d H:i:s');

try {
    // Check if attendance already recorded for current tracking date (respects 7PM reset)
    $check = $pdo->prepare('SELECT * FROM attendance_records WHERE student_id = :sid AND attendance_date = :date');
    $check->execute([
        ':sid'=>$student['student_id'],
        ':date'=>$attendance_date
    ]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    // Check if student already scanned for this specific period
    if ($existing && !empty($existing[$period])) {
        // Already scanned for this period - reject duplicate scan
        log_event($pdo, 'attendance_scan', [
            'success' => 0,
            'message' => 'Duplicate scan rejected',
            'object_type' => 'attendance_records',
            'object_id' => $existing['id'],
            'context_json' => [
                'student_id' => $student['student_id'],
                'period' => $period,
                'previous_scan' => $existing[$period],
                'reason' => 'Already scanned for this period'
            ],
        ]);
        
        echo json_encode([
            'success' => false,
            'error' => 'Already scanned for ' . str_replace('_', ' ', $period),
            'message' => 'You have already scanned for this period',
            'period' => $period_description,
            'previous_scan_time' => date('h:i A', strtotime($existing[$period])),
            'current_status' => calculateAttendanceStatus($existing)
        ]);
        exit;
    }

    if ($existing) {
        // Update existing record with new scan time
        $update = $pdo->prepare("UPDATE attendance_records SET $period = :time WHERE id = :id");
        $update->execute([
            ':time'=>$now,
            ':id'=>$existing['id']
        ]);
        
        // Get updated record to calculate status
        $check = $pdo->prepare('SELECT * FROM attendance_records WHERE id = :id');
        $check->execute([':id'=>$existing['id']]);
        $updated_record = $check->fetch(PDO::FETCH_ASSOC);
        
    } else {
        // Insert new record
        $fields = 'student_id, attendance_date, attendance_time, ' . $period;
        $values = ':sid, :date, :now, :time';
        $ins = $pdo->prepare("INSERT INTO attendance_records ($fields) VALUES ($values)");
        $ins->execute([
            ':sid'=>$student['student_id'],
            ':date'=>$attendance_date,
            ':now'=>$now,
            ':time'=>$now
        ]);
        
        
        // Get the newly inserted record
        $record_id = $pdo->lastInsertId();
        $check = $pdo->prepare('SELECT * FROM attendance_records WHERE id = :id');
        $check->execute([':id'=>$record_id]);
        $updated_record = $check->fetch(PDO::FETCH_ASSOC);
    }
    
    // Calculate attendance status based on scan pattern
    $status = calculateAttendanceStatus($updated_record);
    
    // Update the status
    $status_update = $pdo->prepare("UPDATE attendance_records SET status = :status WHERE id = :id");
    $status_update->execute([
        ':status'=>$status,
        ':id'=>$updated_record['id']
    ]);

    // Log successful scan
    $latency_ms = (int)((microtime(true) - $request_start) * 1000);
    log_event($pdo, 'attendance_scan', [
        'user_role' => 'teacher',
        'user_id' => $teacher_id,
        'object_type' => 'attendance_records',
        'object_id' => $updated_record['id'],
        'latency_ms' => $latency_ms,
        'context_json' => [
            'student_id' => $student['student_id'],
            'scan_source' => $type,
            'period' => $period,
            'period_description' => $period_description,
            'status' => $status,
            'scan_time' => date('H:i:s')
        ],
    ]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>'Attendance save failed: ' . $e->getMessage()]);
    exit;
}

// Return detailed student information
$response = [
    'success' => true,
    'student_info' => [
        'id' => $student['id'],
        'student_id' => $student['student_id'],
        'full_name' => $student['full_name'],
        'email' => $student['email'],
        'gender' => $student['gender'],
        'grade_level' => $student['grade_level'],
        'strand' => $student['strand'],
        'section_block' => $student['section_block'],
        'created_at' => $student['created_at']
    ],
    'attendance_info' => [
        'status' => $status,
        'period' => $period,
        'period_description' => $period_description,
        'timestamp' => $now,
        'date' => $attendance_date,
        'morning_in' => $updated_record['morning_in'],
        'morning_out' => $updated_record['morning_out'],
        'afternoon_in' => $updated_record['afternoon_in'],
        'afternoon_out' => $updated_record['afternoon_out']
    ],
    'message' => 'Attendance recorded successfully for ' . $period_description
];

echo json_encode($response);
