<?php
// student_dashboard.php - shows stats for students with same grade, strand, block/section, and adviser
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/page_security.php';

// Initialize page security
init_page_security();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}
$pdo = get_db();
$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
$stmt->execute([':id' => $uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { echo 'Student not found'; exit; }

// Find all students with same grade, strand, block/section

$students = $pdo->prepare('SELECT * FROM students WHERE grade_level = :grade AND strand = :strand AND section_block = :block ORDER BY full_name ASC');
$students->execute([':grade'=>$user['grade_level'], ':strand'=>$user['strand'], ':block'=>$user['section_block']]);
$studentList = $students->fetchAll(PDO::FETCH_ASSOC);
$count = count($studentList);
$male = 0;
$female = 0;
foreach($studentList as $s) {
    if(strtolower($s['gender']) === 'male') $male++;
    if(strtolower($s['gender']) === 'female') $female++;
}

// Find adviser (teacher) for this group

$adviser = $pdo->prepare('SELECT * FROM teachers WHERE grade_level = :grade AND strand = :strand AND section_block = :block LIMIT 1');
$adviser->execute([':grade'=>$user['grade_level'], ':strand'=>$user['strand'], ':block'=>$user['section_block']]);
$adviserRow = $adviser->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['edit_student']) && isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $lrn = trim($_POST['lrn']);
    $student_id = trim($_POST['student_id']);
    $email = trim($_POST['email']);
    $gender = trim($_POST['gender']);
    $update = $pdo->prepare('UPDATE students SET lrn = :lrn, student_id = :student_id, email = :email, gender = :gender WHERE id = :id');
    $update->execute([
        ':lrn'=>$lrn,
        ':student_id'=>$student_id,
        ':email'=>$email,
        ':gender'=>$gender,
        ':id'=>$edit_id
    ]);
    // Refresh to show updated info
    header('Location: student_dashboard.php?section=student_list');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Prevent browser caching and back button exploitation -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Student Dashboard - PNS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Manrope', 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background:
                radial-gradient(circle at 18% 18%, rgba(34, 211, 238, 0.12), transparent 38%),
                radial-gradient(circle at 78% -8%, rgba(34, 197, 94, 0.1), transparent 42%),
                linear-gradient(140deg, #0c1426 0%, #102035 50%, #0c2841 100%);
            min-height: 100vh;
            color: #0f172a;
        }
        .navbar {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 45%, #22c55e 100%);
            padding: 0;
            box-shadow: 0 14px 36px rgba(6, 182, 212, 0.28);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            border-radius: 0 0 16px 16px;
        }
        .navbar-brand {
            padding: 18px 32px;
            font-size: 1.3em;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.02em;
        }
        .navbar-links { display: flex; }
        .navbar a {
            color: #fff;
            padding: 18px 26px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.25s ease;
            border-bottom: 3px solid transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar a:hover {
            background: rgba(255,255,255,0.12);
            border-bottom-color: #e0f2fe;
        }
        .container {
            max-width: 1320px;
            margin: 32px auto;
            padding: 0 24px;
        }
        .page-header {
            background: #ffffff;
            border-radius: 22px;
            padding: 32px 40px;
            margin-bottom: 28px;
            box-shadow: 0 24px 70px rgba(8, 47, 73, 0.18);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e2e8f0;
        }
        .welcome-text {
            font-size: 2em;
            font-weight: 800;
            color: #0f172a;
        }
        .welcome-subtitle {
            font-size: 1em;
            color: #475569;
            margin-top: 6px;
        }
        .clock {
            font-size: 1.05em;
            color: #0284c7;
            font-weight: 700;
            background: #e0f2fe;
            padding: 12px 18px;
            border-radius: 12px;
            border: 1px solid #bae6fd;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.12);
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0ea5e9;
            transition: all 0.25s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 46px rgba(8, 47, 73, 0.16);
        }
        .stat-label {
            color: #475569;
            font-size: 0.86em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 2.2em;
            font-weight: 800;
            color: #0f172a;
        }
        .content-section {
            background: #ffffff;
            border-radius: 20px;
            padding: 32px 36px;
            margin-bottom: 28px;
            box-shadow: 0 20px 60px rgba(8, 47, 73, 0.14);
            border: 1px solid #e2e8f0;
        }
        .content-section h2 {
            color: #0f172a;
            font-size: 1.45em;
            font-weight: 800;
            margin-bottom: 22px;
            padding-bottom: 14px;
            border-bottom: 3px solid #e0f2fe;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }
        .info-item {
            padding: 14px 18px;
            background: #f7fbff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        }
        .info-item strong {
            color: #0f172a;
            display: block;
            margin-bottom: 4px;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-item span {
            color: #0f172a;
            font-size: 1.04em;
        }
        .student-list {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 14px;
        }
        .student-list li {
            background: linear-gradient(145deg, #ffffff 0%, #f7fbff 100%);
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0ea5e9;
            transition: all 0.25s ease;
            box-shadow: 0 10px 24px rgba(8, 47, 73, 0.12);
        }
        .student-list li:hover {
            transform: translateX(4px);
            box-shadow: 0 16px 30px rgba(8, 47, 73, 0.16);
        }
        @media (max-width: 768px) {
            body { padding: 0; }

            .navbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                flex-direction: column;
                align-items: stretch;
                z-index: 1000;
                border-radius: 0;
            }

            .navbar-brand {
                padding: 16px 20px;
                font-size: 1.1em;
                text-align: center;
                border-bottom: 1px solid rgba(255,255,255,0.2);
            }

            .navbar-links {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                width: 100%;
            }

            .navbar a {
                padding: 14px 12px;
                font-size: 0.86em;
                justify-content: center;
                text-align: center;
                border-bottom: 2px solid transparent;
                border-right: 1px solid rgba(255,255,255,0.1);
            }

            .navbar a:nth-child(2n) { border-right: none; }

            .container { margin-top: 160px; padding: 0 12px; }

            .page-header {
                flex-direction: column;
                gap: 16px;
                text-align: center;
                padding: 24px 20px;
                border-radius: 14px;
                margin-bottom: 20px;
            }

            .welcome-text { font-size: 1.5em; }
            .welcome-subtitle { font-size: 0.9em; }
            .clock { font-size: 0.95em; padding: 10px 16px; }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 12px;
                margin-bottom: 20px;
            }

            .stat-card { padding: 20px; }
            .stat-value { font-size: 2em; }

            .content-section {
                padding: 24px 20px;
                border-radius: 14px;
                margin-bottom: 20px;
            }

            .content-section h2 { font-size: 1.25em; margin-bottom: 18px; }
            .info-grid { grid-template-columns: 1fr; gap: 12px; }
            .info-item { padding: 12px 16px; }
            .student-list { grid-template-columns: 1fr; gap: 12px; }
            .student-list li { padding: 12px 16px; }

            table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                white-space: nowrap;
            }

            table thead,
            table tbody,
            table tr {
                display: table;
                width: 100%;
                table-layout: fixed;
            }
        }

        @media (max-width: 480px) {
            .navbar-brand { font-size: 1em; padding: 14px 16px; }
            .navbar a { font-size: 0.8em; padding: 12px 8px; }
            .container { margin-top: 150px; }
            .welcome-text { font-size: 1.28em; }
            .welcome-subtitle { font-size: 0.85em; }
            .stat-value { font-size: 1.82em; }
            .content-section h2 { font-size: 1.16em; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-brand">👨‍🎓 PNS Student Portal</div>
        <div class="navbar-links">
            <a href="student_dashboard.php"> Dashboard</a>
            <a href="student_qr.php"> My QR Code</a>
            <a href="review_center.php"> Review Center</a>
            <a href="logout.php"> Logout</a>
        </div>
    </div>
    <div class="container">
        <div class="page-header">
            <div>
                <div class="welcome-text">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</div>
                <div class="welcome-subtitle">Grade <?php echo htmlspecialchars($user['grade_level']); ?> - <?php echo htmlspecialchars($user['strand']); ?> - Section <?php echo htmlspecialchars($user['section_block']); ?></div>
            </div>
            <div id="clock" class="clock"></div>
        </div>
        <script>
        function updateClock() {
            var now = new Date();
            var date = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            var time = now.toLocaleTimeString();
            document.getElementById('clock').textContent = time;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Prevent back button from showing cached page
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
        };
        </script>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Classmates</div>
                <div class="stat-value"><?php echo $count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Male Students</div>
                <div class="stat-value"><?php echo $male; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Female Students</div>
                <div class="stat-value"><?php echo $female; ?></div>
            </div>
        </div>

        <div class="content-section">
            <h2>📚 My Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Student ID</strong>
                    <span><?php echo htmlspecialchars($user['student_id']); ?></span>
                </div>
                <div class="info-item">
                    <strong>LRN</strong>
                    <span><?php echo htmlspecialchars($user['lrn'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <strong>Grade Level</strong>
                    <span><?php echo htmlspecialchars($user['grade_level']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Strand</strong>
                    <span><?php echo htmlspecialchars($user['strand']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Section</strong>
                    <span><?php echo htmlspecialchars($user['section_block']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Email</strong>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
            </div>
        </div>

        <div class="content-section">
            <h2>👨‍🏫 Class Adviser</h2>
            <?php if($adviserRow): ?>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Name</strong>
                        <span><?php echo htmlspecialchars($adviserRow['full_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Email</strong>
                        <span><?php echo htmlspecialchars($adviserRow['email']); ?></span>
                    </div>
                    <?php if(isset($adviserRow['faculty'])): ?>
                    <div class="info-item">
                        <strong>Department</strong>
                        <span><?php echo htmlspecialchars($adviserRow['faculty']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
        <?php else: ?>
            <p style="color:#7f8c8d;padding:20px 0">No adviser assigned yet.</p>
        <?php endif; ?>
        </div>

        <div class="content-section">
            <h2>👥 My Classmates (<?php echo $count; ?>)</h2>
            <ul class="student-list">
                <?php foreach($studentList as $s): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($s['full_name']); ?></strong><br>
                        <span style="color:#5a6c7d;font-size:0.9em"><?php echo htmlspecialchars($s['email']); ?> • <?php echo htmlspecialchars($s['gender']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="content-section">
            <h2>📅 Monthly Attendance Summary</h2>
        <?php
        // Use the student's public student_id for attendance_records
        $sid = $user['student_id'];
        // Aggregate counts per month for each status
        $stmt = $pdo->prepare("SELECT
                    DATE_FORMAT(attendance_date, '%Y-%m') AS ym,
                    SUM(status='present') AS present_cnt,
                    SUM(status='absent') AS absent_cnt,
                    SUM(status='late') AS late_cnt,
                    SUM(status='excuse') AS excuse_cnt,
                    SUM(status='morning_half_day') AS morning_half_cnt,
                    SUM(status='afternoon_half_day') AS afternoon_half_cnt
                FROM attendance_records
                WHERE student_id = :sid
                GROUP BY ym
                ORDER BY ym DESC");
        $stmt->execute([':sid' => $sid]);
        $monthly = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if (!$monthly): ?>
            <p style="margin:0;color:#176617">No attendance records yet.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>Month</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Excused</th>
                <th>Morning Half-day</th>
                <th>Afternoon Half-day</th>
            </tr>
            <?php foreach ($monthly as $m):
                // Nicely format month name
                $monthLabel = date('F Y', strtotime($m['ym'].'-01'));
            ?>
            <tr>
                <td><?php echo htmlspecialchars($monthLabel); ?></td>
                <td style="color:#218c21;font-weight:600"><?php echo (int)$m['present_cnt']; ?></td>
                <td style="color:#b00;font-weight:600"><?php echo (int)$m['absent_cnt']; ?></td>
                <td style="color:#e6a800;font-weight:600"><?php echo (int)$m['late_cnt']; ?></td>
                <td style="color:#218c21"><?php echo (int)$m['excuse_cnt']; ?></td>
                <td style="color:#ff8c00"><?php echo (int)$m['morning_half_cnt']; ?></td>
                <td style="color:#6666ff"><?php echo (int)$m['afternoon_half_cnt']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
        </div>
    </div>


</body>
</html>
