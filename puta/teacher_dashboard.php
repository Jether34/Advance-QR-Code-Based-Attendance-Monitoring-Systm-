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

$section = $_GET['section'] ?? 'dashboard';

function active($s, $section) { return $s === $section ? 'active' : ''; }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .sidebar {
            width: 210px;
            background: #218c21;
            color: #fff;
            min-height: 100vh;
            float: left;
            border-radius: 0 20px 20px 0;
            padding-top: 30px;
        }
        .sidebar a {
            display: block;
            color: #fff;
            padding: 14px 24px;
            text-decoration: none;
            font-weight: bold;
            border-left: 5px solid transparent;
            transition: background 0.2s, border 0.2s;
        }
        .sidebar a.active, .sidebar a:hover {
            background: #176617;
            border-left: 5px solid #b2e2b2;
        }
        .main {
            margin-left: 230px;
            padding: 30px 20px;
        }
        @media (max-width: 700px) {
            .sidebar { width: 100%; min-height: unset; float: none; border-radius: 0; }
            .main { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="teacher_dashboard.php?section=scanner" class="<?php echo active('scanner', $section); ?>">Scanner</a>
        <a href="teacher_dashboard.php?section=dashboard" class="<?php echo active('dashboard', $section); ?>">Dashboard</a>
        <a href="teacher_dashboard.php?section=today" class="<?php echo active('today', $section); ?>">Today's Attendance</a>
        <a href="teacher_dashboard.php?section=student_list" class="<?php echo active('student_list', $section); ?>">Student List</a>
        <a href="teacher_dashboard.php?section=analytics" class="<?php echo active('analytics', $section); ?>">Analytics</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main container">
        <?php if($section === 'scanner'): ?>
            <h2>Attendance Scanner</h2>
            <iframe id="scannerFrame" src="scan.php" style="width:100%;height:500px;border:none;background:#f6fff6"></iframe>
            <script>
            // Listen for scan success from scanner iframe
            window.addEventListener('message', function(e) {
                if(e.data === 'refreshAttendance') {
                    // Just show a success notification, don't redirect
                    console.log('Attendance recorded successfully');
                    // Optional: You could add a small notification here
                }
            });
            </script>
        <?php elseif($section === 'dashboard'): ?>
            <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?> (Teacher)</h1>
            <h2>Dashboard</h2>
            <?php
            // Get teacher's advisory group
            $grade = $user['grade_level'];
            $strand = $user['strand'];
            $block = $user['section_block'];
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
            <form method="get" style="margin-bottom:20px">
                <input type="hidden" name="section" value="dashboard">
                <input type="text" name="search" placeholder="Search student by name or ID..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="padding:8px;width:250px;border-radius:5px;border:1px solid #ccc;">
                <button type="submit" style="padding:8px 16px;border-radius:5px;background:#218c21;color:#fff;border:none">Search</button>
            </form>
            <div style="display:flex;gap:20px;margin-bottom:30px;flex-wrap:wrap">
            <!-- Student search results table -->
            <?php
            $search = trim($_GET['search'] ?? '');
            $filtered = $studentList;
            if ($search) {
                $filtered = array_filter($studentList, function($s) use ($search) {
                    return stripos($s['full_name'], $search) !== false || stripos($s['student_id'], $search) !== false;
                });
            }
            ?>
            <table style="width:100%;border-collapse:collapse;margin-top:20px">
                <tr style="background:#eaffea">
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Student ID</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Full Name</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Gender</th>
                </tr>
                <?php if (count($filtered) === 0): ?>
                <tr><td colspan="3" style="padding:10px;text-align:center;color:#b00">No students found.</td></tr>
                <?php else: foreach($filtered as $s): ?>
                <tr>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars($s['student_id']); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars($s['full_name']); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars($s['gender']); ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </table>
                <div style="background:#eaffea;padding:20px;border-radius:10px;min-width:150px;text-align:center;flex:1">
                    <h3>Total Students</h3>
                    <div style="font-size:2em;font-weight:bold"><?php echo $totalStudents; ?></div>
                </div>
                <div style="background:#d6f5d6;padding:20px;border-radius:10px;min-width:150px;text-align:center;flex:1">
                    <h3>Present</h3>
                    <div style="font-size:2em;font-weight:bold;color:#218c21"><?php echo $present; ?></div>
                </div>
                <div style="background:#ffeaea;padding:20px;border-radius:10px;min-width:150px;text-align:center;flex:1">
                    <h3>Absent</h3>
                    <div style="font-size:2em;font-weight:bold;color:#b00"><?php echo $absent; ?></div>
                </div>
                <div style="background:#fffbe6;padding:20px;border-radius:10px;min-width:150px;text-align:center;flex:1">
                    <h3>Late</h3>
                    <div style="font-size:2em;font-weight:bold;color:#e6a800"><?php echo $late; ?></div>
                </div>
                <div style="background:#eaf6ff;padding:20px;border-radius:10px;min-width:150px;text-align:center;flex:1">
                    <h3>Excuse</h3>
                    <div style="font-size:2em;font-weight:bold;color:#218c21"><?php echo $excuse; ?></div>
                </div>
                <div style="background:#fff4e6;padding:20px;border-radius:10px;min-width:150px;text-align:center;flex:1">
                    <h3>Morning Half Day</h3>
                    <div style="font-size:2em;font-weight:bold;color:#ff8c00"><?php echo $morning_half; ?></div>
                </div>
                <div style="background:#f0f0ff;padding:20px;border-radius:10px;min-width:150px;text-align:center;flex:1">
                    <h3>Afternoon Half Day</h3>
                    <div style="font-size:2em;font-weight:bold;color:#6666ff"><?php echo $afternoon_half; ?></div>
                </div>
            </div>
        <?php elseif($section === 'today'): ?>
            <?php if(isset($_GET['ajax'])) { ob_end_clean(); } ?>
            <h2>Today's Attendance</h2>
                <button id="printSF2Btn" style="float:right;margin-bottom:10px;padding:8px 18px;background:#1db954;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Print SF2</button>
                <script>
                document.getElementById('printSF2Btn').onclick = function() {
                    // Gather teacher info and current month
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
                switch($status) {
                    case 'present':
                        return '<span style="color:#218c21;font-weight:bold">Present</span>';
                    case 'morning_half_day':
                        return '<span style="color:#ff8c00;font-weight:bold">Morning Half Day</span>';
                    case 'afternoon_half_day':
                        return '<span style="color:#6666ff;font-weight:bold">Afternoon Half Day</span>';
                    case 'late':
                        return '<span style="color:#e6a800;font-weight:bold">Late</span>';
                    case 'excuse':
                        return '<span style="color:#218c21;font-weight:bold">Excused</span>';
                    case 'absent':
                    default:
                        return '<span style="color:#b00;font-weight:bold">Absent</span>';
                }
            }
            ?>
            <table style="width:100%;border-collapse:collapse">
                <tr style="background:#eaffea">
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Student ID</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Full Name</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Gender</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Morning In</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Morning Out</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Afternoon In</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Afternoon Out</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Status</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Actions</th>
                </tr>
                <tr><td colspan="9" style="background:#d6f5d6;font-weight:bold">Male</td></tr>
                <?php foreach($males as $s): 
                    $records = $attendanceData[$s['student_id']] ?? [];
                ?>
                <tr>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars($s['student_id']); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars($s['full_name']); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2">Male</td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_in')); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_out')); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_in')); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_out')); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo getStatusDisplay(getStatus($records)); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2">
                        <form method="post" action="update_attendance_status.php" style="display:inline">
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
                <tr><td colspan="9" style="background:#f5eaf6;font-weight:bold">Female</td></tr>
                <?php foreach($females as $s): 
                    $records = $attendanceData[$s['student_id']] ?? [];
                ?>
                <tr>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars($s['student_id']); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars($s['full_name']); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2">Female</td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_in')); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_out')); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_in')); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_out')); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo getStatusDisplay(getStatus($records)); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2">
                        <form method="post" action="update_attendance_status.php" style="display:inline">
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
            </table>
            </div>
            <?php
            $search = trim($_GET['search'] ?? '');
            $filtered = $studentList;
            if ($search) {
                $filtered = array_filter($studentList, function($s) use ($search) {
                    return stripos($s['full_name'], $search) !== false || stripos($s['student_id'], $search) !== false;
                });
            }
            ?>
            <table style="width:100%;border-collapse:collapse">
                <tr style="background:#eaffea">
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Student ID</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Full Name</th>
                    <th style="padding:6px 8px;border:1px solid #b2e2b2">Gender</th>
                </tr>
                <?php foreach($filtered as $s): ?>
                <tr>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars($s['student_id']); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars($s['full_name']); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars($s['gender']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
    <script>
    // Sidebar toggle logic
    const sidebar = document.getElementById('studentSidebar');
    const openBtn = document.getElementById('toggleSidebar');
    const closeBtn = document.getElementById('closeSidebar');
    openBtn.onclick = function(e) {
        e.preventDefault();
        sidebar.style.left = '0';
    };
    closeBtn.onclick = function() {
        sidebar.style.left = '-400px';
    };
    document.addEventListener('click', function(e) {
        if (!sidebar.contains(e.target) && e.target !== openBtn) {
            sidebar.style.left = '-400px';
        }
    });
    </script>
        <?php elseif($section === 'student_list'): ?>
            <h2>Student List</h2>
            <?php
            // Always define $studentList for this section
            $grade = $user['grade_level'];
            $strand = $user['strand'];
            $block = $user['section_block'];
            $students = $pdo->prepare('SELECT * FROM students WHERE grade_level = :grade AND strand = :strand AND section_block = :block ORDER BY full_name ASC');
            $students->execute([':grade'=>$grade, ':strand'=>$strand, ':block'=>$block]);
            $studentList = $students->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <table style="width:100%;border-collapse:collapse">
                <tr style="background-color:#f2f2f2">
                    <th style="padding:8px;border:1px solid #b2e2b2">LRN</th>
                    <th style="padding:8px;border:1px solid #b2e2b2">Student ID</th>
                    <th style="padding:8px;border:1px solid #b2e2b2">Full Name</th>
                    <th style="padding:8px;border:1px solid #b2e2b2">Email</th>
                    <th style="padding:8px;border:1px solid #b2e2b2">Gender</th>
                    <th style="padding:8px;border:1px solid #b2e2b2">Actions</th>
                </tr>
                <?php foreach($studentList as $s): ?>
                <tr>
                    <form method="post" action="update_attendance_status.php">
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><input type="text" name="lrn" value="<?php echo htmlspecialchars($s['lrn']); ?>" style="width:100px"></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><input type="text" name="student_id" value="<?php echo htmlspecialchars($s['student_id']); ?>" style="width:120px"></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><?php echo htmlspecialchars($s['full_name']); ?></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2"><input type="email" name="email" value="<?php echo htmlspecialchars($s['email']); ?>" style="width:180px"></td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2">
                        <select name="gender">
                            <option value="Male" <?php if($s['gender']==='Male') echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if($s['gender']==='Female') echo 'selected'; ?>>Female</option>
                            <option value="Other" <?php if($s['gender']==='Other') echo 'selected'; ?>>Other</option>
                        </select>
                    </td>
                    <td style="padding:6px 8px;border:1px solid #b2e2b2">
                        <input type="hidden" name="edit_id" value="<?php echo $s['id']; ?>">
                        <button type="submit" name="edit_student" style="padding:4px 12px;background:#218c21;color:#fff;border:none;border-radius:4px">Save</button>
                    </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php elseif($section === 'analytics'): ?>
            <h2>Analytics</h2>
            <p>Attendance analytics and charts will be shown here.</p>
        <?php endif; ?>
    </div>
</body>
</html>
