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
$period = $data['period'] ?? 'morning_in'; // Default to morning in if not provided

if(!$code){
    try {
        $pdo = get_db();
        log_event($pdo, 'attendance_scan', [
            'success' => 0,
            'message' => 'Missing code',
            'context_json' => ['type' => $type, 'period' => $period],
        ]);
    } catch (Throwable $ignored) {}
    echo json_encode(['success'=>false,'error'=>'Missing code']); exit;
}

// Function to calculate attendance status based on scan patterns
function calculateAttendanceStatus($record) {
    $morning_in = !empty($record['morning_in']);
    $morning_out = !empty($record['morning_out']);
    $afternoon_in = !empty($record['afternoon_in']);
    $afternoon_out = !empty($record['afternoon_out']);
    
    // Full day present: both morning (in+out) and afternoon (in+out)
    if ($morning_in && $morning_out && $afternoon_in && $afternoon_out) {
        return 'present';
    }
    
    // Morning half day: only morning in and out, no afternoon
    if ($morning_in && $morning_out && !$afternoon_in && !$afternoon_out) {
        return 'morning_half_day';
    }
    
    // Afternoon half day: only afternoon in and out, no morning
    if (!$morning_in && !$morning_out && $afternoon_in && $afternoon_out) {
        return 'afternoon_half_day';
    }
    
    // Late: has some scans but incomplete pattern
    if ($morning_in || $morning_out || $afternoon_in || $afternoon_out) {
        return 'late';
    }
    
    // Default absent
    // if the student qr code wasn't perform scanns for all day it should automatically absent
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
// the system should automacally validate only tje student id 

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
        'context_json' => ['type' => $type, 'period' => $period, 'scan_source' => $type],
    ]);
    echo json_encode(['success'=>false,'error'=>'Student not found']); exit;
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

    // Determine which column to update
    $columns = [
        'morning_in', 'morning_out', 'afternoon_in', 'afternoon_out'
    ];
    if (!in_array($period, $columns)) {
        $period = 'morning_in'; // fallback
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
            'status' => $status,
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
        'timestamp' => $now,
        'date' => $today,
        'morning_in' => $updated_record['morning_in'],
        'morning_out' => $updated_record['morning_out'],
        'afternoon_in' => $updated_record['afternoon_in'],
        'afternoon_out' => $updated_record['afternoon_out']
    ]
];

echo json_encode($response);
