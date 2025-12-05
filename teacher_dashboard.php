<?php
// teacher_dashboard.php - main dashboard for teachers with sidebar
session_start();
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}
$pdo = get_db();
$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM teachers WHERE id = :id');
$stmt->execute([':id' => $uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { echo 'Teacher not found'; exit; }

$errors = [];
$success = '';

// Handle student update
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='update_student'){
    $sid = (int)($_POST['student_id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $lrn = trim($_POST['lrn'] ?? '');
    
    // Verify student belongs to teacher's class
    $checkStmt = $pdo->prepare('SELECT id FROM students WHERE id = :id AND grade_level = :grade AND strand = :strand AND section_block = :block');
    $checkStmt->execute([':id'=>$sid, ':grade'=>$user['grade_level'], ':strand'=>$user['strand'], ':block'=>$user['section_block']]);
    if(!$checkStmt->fetch()){
        $errors[] = 'You can only edit students in your class';
    } else {
        foreach(['full_name','email'] as $req){ if(empty($$req)) $errors[] = "$req is required"; }
        if($email && !filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Invalid email format';
        if(!$errors){
            $updates = [];$params=[':id'=>$sid];
            $updates[]='full_name = :full_name'; $params[':full_name']=$full_name;
            $updates[]='email = :email'; $params[':email']=$email;
            if($password!==''){ $updates[]='password = :password'; $params[':password']=password_hash($password,PASSWORD_DEFAULT); }
            if($gender!==''){ $updates[]='gender = :gender'; $params[':gender']=$gender; }
            if($lrn!==''){ $updates[]='lrn = :lrn'; $params[':lrn']=$lrn; }
            $sql = 'UPDATE students SET '.implode(', ',$updates).' WHERE id = :id';
            try { $stmt=$pdo->prepare($sql); $stmt->execute($params); $success='Student updated successfully'; }
            catch(Exception $e){ $errors[]='Update failed: '.$e->getMessage(); }
        }
    }
}

$section = $_GET['section'] ?? 'dashboard';
// here are the css styles for sidebar and main content

function active($s, $section) { return $s === $section ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - PNS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            background: linear-gradient(135deg, #1e5128 0%, #2d6a4f 100%);
            min-height: 100vh;
            display: flex;
            color: #2c3e50;
        }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e5128 0%, #2d6a4f 100%);
            color: #fff;
            min-height: 100vh;
            padding: 24px 0;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            position: fixed;
            left: 0;
            top: 0;
        }
        .sidebar-header {
            padding: 0 24px 24px;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar-header h2 {
            font-size: 1.4em;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .sidebar-header p {
            font-size: 0.85em;
            color: #d8f3dc;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            padding: 14px 24px;
            text-decoration: none;
            font-weight: 600;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            font-size: 0.95em;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: #d8f3dc;
            padding-left: 28px;
        }
        .sidebar a.active {
            background: rgba(255,255,255,0.15);
            border-left-color: #d8f3dc;
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.1);
        }
        .sidebar a.logout {
            margin-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            color: #ffcccc;
        }
        .sidebar a.logout:hover {
            background: rgba(231,76,60,0.2);
            border-left-color: #e74c3c;
        }
        .main {
            margin-left: 260px;
            flex: 1;
            padding: 28px 32px;
            width: calc(100% - 260px);
        }
        .page-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
            border-radius: 16px;
            padding: 24px 32px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.8);
        }
        .page-header h1 {
            color: #1e5128;
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .page-header p {
            color: #5a6c7d;
            font-size: 0.95em;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
            border-radius: 14px;
            padding: 20px 18px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .stat-card.present { border-left-color: #28a745; }
        .stat-card.late { border-left-color: #ffc107; }
        .stat-card.absent { border-left-color: #dc3545; }
        .stat-card.excuse { border-left-color: #17a2b8; }
        .stat-value {
            font-size: 2em;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .stat-card.present .stat-value { color: #28a745; }
        .stat-card.late .stat-value { color: #ffc107; }
        .stat-card.absent .stat-value { color: #dc3545; }
        .stat-card.excuse .stat-value { color: #17a2b8; }
        .stat-label {
            color: #5a6c7d;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .content-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            border: 1px solid rgba(45, 106, 79, 0.08);
        }
        .content-section h2 {
            color: #1e5128;
            font-size: 1.4em;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 3px solid #d8f3dc;
        }
        .search-bar {
            margin-bottom: 20px;
        }
        .search-bar input {
            width: 100%;
            max-width: 400px;
            padding: 12px 16px;
            border: 2px solid #d8f3dc;
            border-radius: 10px;
            font-size: 0.95em;
            background: #f6fff7;
            transition: all 0.3s;
        }
        .search-bar input:focus {
            outline: none;
            border-color: #2d6a4f;
            box-shadow: 0 0 0 4px rgba(45,106,79,0.12);
            background: #fff;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
        }
        table th {
            background: linear-gradient(135deg, #d8f3dc 0%, #b7e4c7 100%);
            padding: 14px 12px;
            text-align: left;
            font-weight: 700;
            color: #1e5128;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #e8f5e9;
            font-size: 0.9em;
            background: #fff;
        }
        table tr:hover td {
            background: #f0f7f4;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9em;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #2d6a4f 0%, #1e5128 100%);
            color: #fff;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45,106,79,0.4);
        }
        iframe {
            width: 100%;
            height: 500px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        @media (max-width: 900px) {
            .sidebar {
                width: 100%;
                min-height: auto;
                position: relative;
            }
            .main {
                margin-left: 0;
                width: 100%;
                padding: 12px;
            }
            
            /* Enhanced mobile styles */
            .sidebar {
                width: 100%;
                padding: 12px 0;
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1000;
                min-height: auto;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            
            .sidebar-header {
                padding: 12px 16px;
                margin-bottom: 8px;
            }
            
            .sidebar-header h2 {
                font-size: 1.1em;
            }
            
            .sidebar-header p {
                font-size: 0.8em;
            }
            
            .sidebar a {
                padding: 10px 16px;
                font-size: 0.85em;
                gap: 8px;
            }
            
            .sidebar a:hover {
                padding-left: 16px;
            }
            
            body {
                padding-top: 200px; /* Account for fixed sidebar */
            }
            
            .page-header {
                padding: 16px 20px;
                margin-bottom: 16px;
            }
            
            .page-header h1 {
                font-size: 1.3em;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .stat-card {
                padding: 16px;
            }
            
            .stat-card h3 {
                font-size: 1em;
            }
            
            .stat-card .number {
                font-size: 2em;
            }
            
            .card {
                padding: 16px;
                margin-bottom: 16px;
            }
            
            .card h2 {
                font-size: 1.2em;
            }
            
            table {
                font-size: 0.8em;
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            th, td {
                padding: 8px 6px;
                white-space: nowrap;
            }
            
            .btn, button {
                font-size: 0.9em;
                padding: 10px 16px;
            }
            
            .search-box {
                margin-bottom: 12px;
            }
            
            input, select {
                font-size: 16px; /* Prevent iOS zoom */
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>👨‍🏫 Teacher</h2>
            <p><?php echo htmlspecialchars($user['full_name']); ?></p>
        </div>
        <a href="teacher_dashboard.php?section=scanner" class="<?php echo active('scanner', $section); ?>">📷 Scanner</a>
        <a href="teacher_dashboard.php?section=dashboard" class="<?php echo active('dashboard', $section); ?>">📊 Dashboard</a>
        <a href="teacher_dashboard.php?section=today" class="<?php echo active('today', $section); ?>">📅 Today's Attendance</a>
        <a href="teacher_dashboard.php?section=student_list" class="<?php echo active('student_list', $section); ?>">👥 Student List</a>
        <a href="teacher_dashboard.php?section=analytics" class="<?php echo active('analytics', $section); ?>">📈 Analytics</a>
        <a href="logout.php" class="logout">🚪 Logout</a>
    </div>
    <div class="main container">
        <?php if($section === 'scanner'): ?>
            <div class="page-header">
                <h1>📷 Attendance Scanner</h1>
                <p>Scan student QR codes to record attendance</p>
            </div>
            <div class="content-section">
                <iframe id="scannerFrame" src="scan.php"></iframe>
                <script>
                window.addEventListener('message', function(e) {
                    if(e.data === 'refreshAttendance') {
                        console.log('Attendance recorded successfully');
                    }
                });
                </script>
            </div>
        <?php elseif($section === 'dashboard'): ?>
            <?php
            // Get teacher's advisory group
            $grade = $user['grade_level'];
            $strand = $user['strand'];
            $block = $user['section_block'];
            ?>
            <div class="page-header">
                <h1>📊 Dashboard Overview</h1>
                <p>Grade <?php echo htmlspecialchars($grade); ?> - <?php echo htmlspecialchars($strand); ?> - Section <?php echo htmlspecialchars($block); ?></p>
            </div>
            <?php
            // Get all students in this group
            $students = $pdo->prepare('SELECT * FROM students WHERE grade_level = :grade AND strand = :strand AND section_block = :block');
            $students->execute([':grade'=>$grade, ':strand'=>$strand, ':block'=>$block]);
            $studentList = $students->fetchAll(PDO::FETCH_ASSOC);
            $studentIds = array_column($studentList, 'student_id');
            $totalStudents = count($studentList);
            // Get today's attendance records for these students
            $today = date('Y-m-d');
            $attendance = $pdo->prepare('SELECT * FROM attendance_records WHERE attendance_date = :today AND student_id IN ("' . implode('","', $studentIds) . '")');
            $attendance->execute([':today'=>$today]);
            $present = $absent = $late = $excuse = $morning_half = $afternoon_half = 0;
            foreach($attendance->fetchAll(PDO::FETCH_ASSOC) as $row) {
                switch($row['status']) {
                    case 'present': $present++; break;
                    case 'absent': $absent++; break;
                    case 'late': $late++; break;
                    case 'excuse': $excuse++; break;
                    case 'morning_half_day': $morning_half++; break;
                    case 'afternoon_half_day': $afternoon_half++; break;
                }
            }
            ?>
            <div class="stats-grid">
                <div class="stat-card present">
                    <div class="stat-value"><?php echo $present; ?></div>
                    <div class="stat-label">Present</div>
                </div>
                <div class="stat-card late">
                    <div class="stat-value"><?php echo $late; ?></div>
                    <div class="stat-label">Late</div>
                </div>
                <div class="stat-card absent">
                    <div class="stat-value"><?php echo $absent; ?></div>
                    <div class="stat-label">Absent</div>
                </div>
                <div class="stat-card excuse">
                    <div class="stat-value"><?php echo $excuse; ?></div>
                    <div class="stat-label">Excused</div>
                </div>
            </div>
            <div class="content-section">
                <h2>Student Search</h2>
                <form method="get" class="search-bar">
                    <input type="hidden" name="section" value="dashboard">
                    <input type="text" name="search" placeholder="🔍 Search student by name or ID..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </form>
            </div>
            <div class="content-section">
                <h2>📋 Student Directory</h2>
                <?php
                $search = trim($_GET['search'] ?? '');
                $filtered = $studentList;
                if ($search) {
                    $filtered = array_filter($studentList, function($s) use ($search) {
                        return stripos($s['full_name'], $search) !== false || stripos($s['student_id'], $search) !== false;
                    });
                }
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Gender</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($filtered) === 0): ?>
                        <tr><td colspan="3" style="padding:20px;text-align:center;color:#e74c3c">No students found.</td></tr>
                        <?php else: foreach($filtered as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($s['gender'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif($section === 'today'): ?>
            <div class="page-header">
                <h1>📅 Today's Attendance</h1>
                <p><?php echo date('F d, Y'); ?></p>
            </div>
            <div class="content-section" style="text-align:right;padding:16px 32px;margin-bottom:16px;">
                <button id="printSF2Btn" class="btn btn-primary">🖨️ Print SF2</button>
                <script>
                document.getElementById('printSF2Btn').onclick = function() {
                    var params = new URLSearchParams({
                        month: new Date().toISOString().slice(0,7),
                        strand: '<?php echo addslashes($user['strand']); ?>',
                        block: '<?php echo addslashes($user['section_block']); ?>',
                        grade_level: '<?php echo addslashes($user['grade_level']); ?>',
                        teacher_name: '<?php echo addslashes($user['full_name']); ?>'
                    });
                    window.open('print_monthly_sf2.php?' + params.toString(), '_blank');
                };
                </script>
            </div>
            <?php
            // Get teacher's advisory group
            $grade = $user['grade_level'];
            $strand = $user['strand'];
            $block = $user['section_block'];
            // Get all students in this group
            $students = $pdo->prepare('SELECT * FROM students WHERE grade_level = :grade AND strand = :strand AND section_block = :block');
            $students->execute([':grade'=>$grade, ':strand'=>$strand, ':block'=>$block]);
            $studentList = $students->fetchAll(PDO::FETCH_ASSOC);
            // Group by gender and sort alphabetically
            $males = array_filter($studentList, function($s){ return strtolower($s['gender']) === 'male'; });
            $females = array_filter($studentList, function($s){ return strtolower($s['gender']) === 'female'; });
            usort($males, function($a,$b){ return strcmp($a['full_name'], $b['full_name']); });
            usort($females, function($a,$b){ return strcmp($a['full_name'], $b['full_name']); });
            // Get today's attendance records for these students
            $today = date('Y-m-d');
            $attendance = $pdo->prepare('SELECT * FROM attendance_records WHERE attendance_date = :today');
            $attendance->execute([':today'=>$today]);
            $attendanceData = [];
            foreach($attendance->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $attendanceData[$row['student_id']][] = $row;
            }
            function getPeriodCol($record, $col) {
                if (!empty($record[$col])) {
                    // Extract only time part (HH:MM:SS)
                    $dt = date_create($record[$col]);
                    return $dt ? date_format($dt, 'H:i:s') : $record[$col];
                }
                return '';
            }
            function getScanTime($records) {
                foreach($records as $r) {
                    if($r['time_period'] === 'scan') return $r['attendance_time'];
                }
                return '';
            }
            function getStatus($records) {
                return $records[0]['status'] ?? 'absent';
            }
            function getStatusDisplay($status) {
                // Return styled badge markup
                switch($status) {
                    case 'present':
                        return '<span class="status-badge status-present">Present</span>';
                    case 'morning_half_day':
                        return '<span class="status-badge status-morning_half_day">Morning Half Day</span>';
                    case 'afternoon_half_day':
                        return '<span class="status-badge status-afternoon_half_day">Afternoon Half Day</span>';
                    case 'late':
                        return '<span class="status-badge status-late">Late</span>';
                    case 'excuse':
                        return '<span class="status-badge status-excuse">Excused</span>';
                    case 'absent':
                    default:
                        return '<span class="status-badge status-absent">Absent</span>';
                }
            }
            ?>
            <style>
            /* Attendance table redesign */
            .attendance-table {width:100%;border-collapse:collapse;}
            .attendance-table thead th {background:#1e5128;color:#fff;padding:10px 12px;font-size:12px;letter-spacing:.5px;text-transform:uppercase;border:1px solid #1e5128;font-weight:600;}
            .attendance-table tbody tr {background:#ffffff;}
            .attendance-table tbody td {padding:10px 12px;border:1px solid #d8f3dc;font-size:14px;}
            .attendance-table tr.gender-header td {background:linear-gradient(90deg,#2d6a4f,#52b788);color:#fff;font-weight:600;letter-spacing:.5px;font-size:13px;padding:8px 12px;}
            .status-badge {display:inline-block;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.3px;}
            .status-present {background:#d8f3dc;color:#1b5e20;}
            .status-late {background:#fff4d5;color:#b26a00;}
            .status-absent {background:#ffe3e3;color:#b00020;}
            .status-excuse {background:#e0f7fa;color:#006064;}
            .status-morning_half_day {background:#e8f0fe;color:#1e40af;}
            .status-afternoon_half_day {background:#ede7f6;color:#4a148c;}
            .attendance-actions form {display:flex;flex-wrap:wrap;gap:6px;align-items:center;}
            .attendance-actions select {padding:6px 8px;border:1px solid #ccc;border-radius:6px;background:#f8f9fa;font-size:13px;}
            .attendance-actions button {background:#1e5128;color:#fff;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-weight:600;}
            .attendance-actions button:hover {background:#2d6a4f;}
            @media (max-width: 950px){
                .attendance-table thead {display:none;}
                .attendance-table tbody tr {display:grid;grid-template-columns:repeat(2,1fr);gap:8px;}
                .attendance-table tbody tr.gender-header {display:block;}
                .attendance-table tbody td {position:relative;padding:8px 10px;}
                .attendance-table tbody td[data-label]:before {content:attr(data-label);display:block;font-size:10px;font-weight:600;color:#2d6a4f;margin-bottom:2px;text-transform:uppercase;letter-spacing:.5px;}
            }
            </style>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Gender</th>
                        <th>Morning In</th>
                        <th>Morning Out</th>
                        <th>Afternoon In</th>
                        <th>Afternoon Out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <tr class="gender-header"><td colspan="9">Male</td></tr>
                <?php foreach($males as $s): 
                    $records = $attendanceData[$s['student_id']] ?? [];
                ?>
                <tr>
                    <td data-label="Student ID"><?php echo htmlspecialchars($s['student_id']); ?></td>
                    <td data-label="Full Name"><?php echo htmlspecialchars($s['full_name']); ?></td>
                    <td data-label="Gender">Male</td>
                    <td data-label="Morning In"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_in')); ?></td>
                    <td data-label="Morning Out"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_out')); ?></td>
                    <td data-label="Afternoon In"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_in')); ?></td>
                    <td data-label="Afternoon Out"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_out')); ?></td>
                    <td data-label="Status"><?php echo getStatusDisplay(getStatus($records)); ?></td>
                    <td data-label="Actions" class="attendance-actions">
                        <form method="post" action="update_attendance_status.php">
                            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($s['student_id']); ?>">
                            <select name="status">
                                <option value="present" <?php if(getStatus($records)==='present') echo 'selected'; ?>>Present</option>
                                <option value="morning_half_day" <?php if(getStatus($records)==='morning_half_day') echo 'selected'; ?>>Morning Half Day</option>
                                <option value="afternoon_half_day" <?php if(getStatus($records)==='afternoon_half_day') echo 'selected'; ?>>Afternoon Half Day</option>
                                <option value="absent" <?php if(getStatus($records)==='absent') echo 'selected'; ?>>Absent</option>
                                <option value="late" <?php if(getStatus($records)==='late') echo 'selected'; ?>>Late</option>
                                <option value="excuse" <?php if(getStatus($records)==='excuse') echo 'selected'; ?>>Excuse</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr class="gender-header"><td colspan="9">Female</td></tr>
                <?php foreach($females as $s): 
                    $records = $attendanceData[$s['student_id']] ?? [];
                ?>
                <tr>
                    <td data-label="Student ID"><?php echo htmlspecialchars($s['student_id']); ?></td>
                    <td data-label="Full Name"><?php echo htmlspecialchars($s['full_name']); ?></td>
                    <td data-label="Gender">Female</td>
                    <td data-label="Morning In"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_in')); ?></td>
                    <td data-label="Morning Out"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_out')); ?></td>
                    <td data-label="Afternoon In"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_in')); ?></td>
                    <td data-label="Afternoon Out"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_out')); ?></td>
                    <td data-label="Status"><?php echo getStatusDisplay(getStatus($records)); ?></td>
                    <td data-label="Actions" class="attendance-actions">
                        <form method="post" action="update_attendance_status.php">
                            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($s['student_id']); ?>">
                            <select name="status">
                                <option value="present" <?php if(getStatus($records)==='present') echo 'selected'; ?>>Present</option>
                                <option value="morning_half_day" <?php if(getStatus($records)==='morning_half_day') echo 'selected'; ?>>Morning Half Day</option>
                                <option value="afternoon_half_day" <?php if(getStatus($records)==='afternoon_half_day') echo 'selected'; ?>>Afternoon Half Day</option>
                                <option value="late" <?php if(getStatus($records)==='late') echo 'selected'; ?>>Late</option>
                                <option value="excuse" <?php if(getStatus($records)==='excuse') echo 'selected'; ?>>Excuse</option>
                                <option value="absent" <?php if(getStatus($records)==='absent') echo 'selected'; ?>>Absent</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif($section === 'student_list'): ?>
            <div class="page-header">
                <h1>👥 Student List</h1>
                <p>Manage students in your class</p>
            </div>
            <?php if(!empty($errors)): ?>
                <div style="padding:14px 20px;background:linear-gradient(135deg,#f8d7da 0%,#f5c6cb 100%);border-left:4px solid #dc3545;margin-bottom:18px;border-radius:12px">
                    <?php foreach($errors as $err): ?><p style="margin:3px 0;color:#721c24;font-weight:600"><?= htmlspecialchars($err) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if($success): ?>
                <div style="padding:14px 20px;background:linear-gradient(135deg,#d4edda 0%,#c3e6cb 100%);border-left:4px solid #28a745;margin-bottom:18px;border-radius:12px;color:#155724;font-weight:600">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php
            $grade = $user['grade_level'];
            $strand = $user['strand'];
            $block = $user['section_block'];
            $students = $pdo->prepare('SELECT * FROM students WHERE grade_level = :grade AND strand = :strand AND section_block = :block ORDER BY full_name ASC');
            $students->execute([':grade'=>$grade, ':strand'=>$strand, ':block'=>$block]);
            $studentList = $students->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="content-section">
                <h2>📋 Student Records</h2>
                <table>
                    <thead>
                        <tr>
                            <th>LRN</th>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Gender</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($studentList as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['lrn'] ?? '') ?></td>
                            <td><?= htmlspecialchars($s['student_id'] ?? '') ?></td>
                            <td><?= htmlspecialchars($s['full_name']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= htmlspecialchars($s['gender'] ?? '') ?></td>
                            <td>
                                <button type="button" onclick='openEditModal(<?= json_encode($s) ?>)' class="btn btn-primary" style="padding:8px 16px;font-size:0.85em">✏️ Edit</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Edit Student Modal -->
            <div id="editModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.65);z-index:10000;justify-content:center;align-items:center">
                <div style="background:linear-gradient(135deg,#ffffff 0%,#f8fffe 100%);max-width:550px;width:90%;margin:50px auto;padding:32px 40px;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
                    <h3 style="margin:0 0 24px;color:#1e5128;font-size:1.5em;font-weight:700;padding-bottom:14px;border-bottom:3px solid #d8f3dc">✏️ Edit Student</h3>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="update_student">
                        <input type="hidden" name="student_id" id="edit_student_id">
                        
                        <label style="display:block;margin-bottom:6px;color:#1e5128;font-weight:700;font-size:0.8em;text-transform:uppercase;letter-spacing:0.5px">Full Name:</label>
                        <input type="text" name="full_name" id="edit_full_name" required style="width:100%;padding:12px 14px;margin-bottom:14px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:0.9em;transition:all 0.3s">
                        
                        <label style="display:block;margin-bottom:6px;color:#1e5128;font-weight:700;font-size:0.8em;text-transform:uppercase;letter-spacing:0.5px">LRN:</label>
                        <input type="text" name="lrn" id="edit_lrn" style="width:100%;padding:12px 14px;margin-bottom:14px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:0.9em;transition:all 0.3s">
                        
                        <label style="display:block;margin-bottom:6px;color:#1e5128;font-weight:700;font-size:0.8em;text-transform:uppercase;letter-spacing:0.5px">Email:</label>
                        <input type="email" name="email" id="edit_email" required style="width:100%;padding:12px 14px;margin-bottom:14px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:0.9em;transition:all 0.3s">
                        
                        <label style="display:block;margin-bottom:6px;color:#1e5128;font-weight:700;font-size:0.8em;text-transform:uppercase;letter-spacing:0.5px">Gender:</label>
                        <select name="gender" id="edit_gender" style="width:100%;padding:12px 14px;margin-bottom:14px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:0.9em;transition:all 0.3s">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                        
                        <label style="display:block;margin-bottom:6px;color:#1e5128;font-weight:700;font-size:0.8em;text-transform:uppercase;letter-spacing:0.5px">Change Password (leave blank to keep current):</label>
                        <input type="password" name="password" id="edit_password" placeholder="New password" style="width:100%;padding:12px 14px;margin-bottom:18px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:0.9em;transition:all 0.3s">
                        
                        <div style="display:flex;gap:12px">
                            <button type="submit" class="btn btn-primary" style="flex:1;padding:13px">Update Student</button>
                            <button type="button" onclick="closeEditModal()" style="flex:1;padding:13px;background:linear-gradient(135deg,#95a5a6 0%,#7f8c8d 100%);color:#fff;border:none;border-radius:11px;font-weight:600;cursor:pointer;transition:all 0.3s">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <script>
            function openEditModal(student) {
                document.getElementById('edit_student_id').value = student.id;
                document.getElementById('edit_full_name').value = student.full_name || '';
                document.getElementById('edit_lrn').value = student.lrn || '';
                document.getElementById('edit_email').value = student.email || '';
                document.getElementById('edit_gender').value = student.gender || '';
                document.getElementById('edit_password').value = '';
                document.getElementById('editModal').style.display = 'flex';
            }
            function closeEditModal() {
                document.getElementById('editModal').style.display = 'none';
            }
            </script>
        <?php elseif($section === 'analytics'): ?>
            <div class="page-header">
                <h1>📈 Analytics</h1>
                <p>Attendance insights and reports</p>
            </div>
            <div class="content-section">
                <h2>📊 Coming Soon</h2>
                <p style="color:#5a6c7d;padding:20px 0">Attendance analytics and visualizations will be displayed here.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
