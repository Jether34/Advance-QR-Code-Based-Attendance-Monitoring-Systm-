<?php
// student_dashboard.php - shows stats for students with same grade, strand, block/section, and adviser
session_start();
require_once __DIR__ . '/db.php';
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
<html>
<head>
    <meta charset="utf-8">
    <title>Student Dashboard - School Attendance System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #d6f5d6 0%, #eaffea 100%);
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(90deg, #218c21 0%, #176617 100%);
            padding: 0;
            border-radius: 0;
            box-shadow: 0 2px 8px rgba(33, 140, 33, 0.3);
            display: flex;
            align-items: center;
        }
        .navbar a {
            color: #fff;
            padding: 18px 24px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        .navbar a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 3px solid #b2e2b2;
        }
        .container {
            max-width: 1200px;
            margin: 32px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(33, 140, 33, 0.2);
            padding: 32px;
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eaffea;
        }
        .welcome-text {
            font-size: 1.5em;
            font-weight: bold;
            color: #218c21;
        }
        .clock {
            font-size: 1.1em;
            color: #176617;
            font-weight: 600;
        }
        .info-section {
            background: #eaffea;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .info-section h3 {
            margin-top: 0;
            color: #218c21;
            font-size: 1.3em;
            margin-bottom: 16px;
        }
        .stats-row {
            display: flex;
            gap: 20px;
            margin-bottom: 16px;
        }
        .stat-box {
            background: #fff;
            border-radius: 8px;
            padding: 12px 16px;
            flex: 1;
            text-align: center;
            box-shadow: 0 2px 8px rgba(33, 140, 33, 0.1);
        }
        .stat-box strong {
            color: #218c21;
            display: block;
            margin-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            border-radius: 8px;
            overflow: hidden;
        }
        th {
            background: #218c21;
            color: #fff;
            padding: 14px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 12px 14px;
            border-bottom: 1px solid #eaffea;
        }
        tr:hover {
            background: #f8fff8;
        }
        .list-section {
            margin-top: 24px;
        }
        .list-section ul {
            list-style: none;
            padding: 0;
        }
        .list-section li {
            background: #f8fff8;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            border-left: 4px solid #218c21;
        }
        
        /* QR Code Styling */
        .qr-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin: 0 5px;
            transition: all 0.3s;
        }
        
        .qr-btn-primary {
            background: #218c21;
            color: white;
        }
        
        .qr-btn-primary:hover {
            background: #176617;
            transform: translateY(-2px);
        }
        
        .qr-btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .qr-btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }
        
        .qr-btn-refresh {
            background: #17a2b8;
            color: white;
        }
        
        .qr-btn-refresh:hover {
            background: #117a8b;
            transform: translateY(-2px);
        }
        
        #studentQRCanvas {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="student_dashboard.php">Dashboard</a>
        <a href="card.php?id=<?php echo $user['id']; ?>">QR Card</a>
        <a href="#qr-section" onclick="document.getElementById('studentQRCanvas').scrollIntoView({behavior: 'smooth'})">📱 Offline QR</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <div class="header-row">
            <div class="welcome-text">Welcome, <?php echo htmlspecialchars($user['full_name']); ?></div>
            <div id="clock" class="clock"></div>
        </div>
        <script>
        function updateClock() {
            var now = new Date();
            var date = now.toLocaleDateString();
            var time = now.toLocaleTimeString();
            document.getElementById('clock').textContent = date + ' ' + time;
        }
        setInterval(updateClock, 1000);
        updateClock();
        </script>
    <div class="info-section">
        <h3>📚 My Information</h3>
        <div class="stats-row">
            <div class="stat-box">
                <strong>Grade</strong>
                <?php echo htmlspecialchars($user['grade_level']); ?>
            </div>
            <div class="stat-box">
                <strong>Strand</strong>
                <?php echo htmlspecialchars($user['strand']); ?>
            </div>
            <div class="stat-box">
                <strong>Section</strong>
                <?php echo htmlspecialchars($user['section_block']); ?>
            </div>
        </div>
    </div>

    <!-- QR Code Generation Section -->
    <div class="info-section" id="qr-section">
        <h3>📱 My QR Code (100% Offline)</h3>
        <div style="text-align: center; margin: 20px 0;">
            <canvas id="studentQRCanvas" width="200" height="200" style="border: 2px solid #218c21; border-radius: 8px; background: white;"></canvas>
        </div>
        <div style="text-align: center; margin-top: 15px;">
            <button onclick="generateMyQR()" class="qr-btn qr-btn-primary">Generate QR Code</button>
            <button onclick="downloadMyQR()" class="qr-btn qr-btn-secondary">Download QR</button>
            <button onclick="refreshQR()" class="qr-btn qr-btn-refresh">Refresh</button>
        </div>
        <div id="qrStatus" style="text-align: center; margin-top: 10px; font-size: 0.9em; color: #666;"></div>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px;">
            <strong>QR Code Information:</strong><br>
            <small style="font-family: monospace; color: #666;">
                Student ID: <?php echo htmlspecialchars($user['student_id']); ?><br>
                LRN: <?php echo htmlspecialchars($user['lrn']); ?><br>
                Name: <?php echo htmlspecialchars($user['full_name']); ?>
            </small>
        </div>
    </div>
    
    <div class="info-section">
        <h3>👥 Classmates (<?php echo $count; ?>)</h3>
        <div class="stats-row">
            <div class="stat-box">
                <strong>Male</strong>
                <?php echo $male; ?>
            </div>
            <div class="stat-box">
                <strong>Female</strong>
                <?php echo $female; ?>
            </div>
        </div>
        <div class="list-section">
            <ul>
                <?php foreach($studentList as $s): ?>
                    <li><?php echo htmlspecialchars($s['full_name']); ?> - <?php echo htmlspecialchars($s['email']); ?> (<?php echo htmlspecialchars($s['gender']); ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <div class="info-section">
        <h3>👨‍🏫 Adviser</h3>
        <?php if($adviserRow): ?>
            <p style="font-size:1.1em"><strong><?php echo htmlspecialchars($adviserRow['full_name']); ?></strong> - <?php echo htmlspecialchars($adviserRow['email']); ?> (<?php echo htmlspecialchars($adviserRow['faculty']); ?>)</p>
        <?php else: ?>
            <p>No adviser assigned.</p>
        <?php endif; ?>
    </div>
    <div class="info-section">
        <h3>� Monthly Attendance Summary</h3>
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

    <!-- Binary QR Generator Script -->
    <script src="js/binary-qr-generator.js"></script>
    <script>
        // Student data from PHP
        const studentData = {
            id: <?php echo json_encode($user['id']); ?>,
            full_name: <?php echo json_encode($user['full_name']); ?>,
            lrn: <?php echo json_encode($user['lrn']); ?>,
            student_id: <?php echo json_encode($user['student_id']); ?>,
            grade_level: <?php echo json_encode($user['grade_level']); ?>,
            strand: <?php echo json_encode($user['strand']); ?>,
            section_block: <?php echo json_encode($user['section_block']); ?>,
            email: <?php echo json_encode($user['email']); ?>,
            gender: <?php echo json_encode($user['gender']); ?>
        };

        // QR Generation Functions
        function generateMyQR() {
            const statusDiv = document.getElementById('qrStatus');
            statusDiv.innerHTML = '<span style="color: #218c21;">⏳ Generating QR Code...</span>';
            
            try {
                // Use the binary QR generator
                const result = generateStudentQR(studentData, 'studentQRCanvas');
                
                if (result.success) {
                    statusDiv.innerHTML = '<span style="color: #218c21;">✅ QR Code Generated Successfully!</span>';
                    console.log('QR Data:', result.data);
                } else if (result.fallback) {
                    statusDiv.innerHTML = '<span style="color: #ff8c00;">⚠️ Using Fallback Pattern (Data Embedded)</span>';
                } else {
                    statusDiv.innerHTML = '<span style="color: #dc3545;">❌ Generation Failed</span>';
                }
            } catch (error) {
                console.error('QR Generation Error:', error);
                statusDiv.innerHTML = '<span style="color: #dc3545;">❌ Error: ' + error.message + '</span>';
            }
        }

        function downloadMyQR() {
            const canvas = document.getElementById('studentQRCanvas');
            if (!canvas) {
                alert('Please generate QR code first!');
                return;
            }
            
            // Check if canvas has content
            const ctx = canvas.getContext('2d');
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            let hasContent = false;
            
            for (let i = 0; i < imageData.data.length; i += 4) {
                if (imageData.data[i] !== 255 || imageData.data[i + 1] !== 255 || imageData.data[i + 2] !== 255) {
                    hasContent = true;
                    break;
                }
            }
            
            if (!hasContent) {
                alert('Please generate QR code first!');
                return;
            }
            
            // Download QR code
            const filename = `qr-${studentData.student_id}-${new Date().toISOString().split('T')[0]}.png`;
            downloadQR('studentQRCanvas', filename);
            
            document.getElementById('qrStatus').innerHTML = '<span style="color: #218c21;">📥 QR Code Downloaded!</span>';
        }

        function refreshQR() {
            // Clear canvas and regenerate
            const canvas = document.getElementById('studentQRCanvas');
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            document.getElementById('qrStatus').innerHTML = '<span style="color: #666;">Canvas cleared. Click "Generate QR Code" to create new QR.</span>';
        }

        // Auto-generate QR on page load
        window.addEventListener('load', function() {
            setTimeout(generateMyQR, 500); // Small delay to ensure canvas is ready
        });

        // Test QR generation capability on load
        window.addEventListener('load', function() {
            console.log('Binary QR Generator Status:');
            console.log('- BinaryQRGenerator available:', typeof window.BinaryQRGenerator !== 'undefined');
            console.log('- generateStudentQR available:', typeof window.generateStudentQR !== 'undefined');
            console.log('- Student Data:', studentData);
        });
    </script>
</body>
</html>
