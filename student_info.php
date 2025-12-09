<?php
// student_info.php - Public student information display for QR code scanning
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

$pdo = get_db();
$student_id = $_GET['id'] ?? '';

if (!$student_id) {
    http_response_code(404);
    echo "<h1>Invalid QR Code</h1><p>No student ID provided.</p>";
    exit;
}

// Get student information
try {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = :student_id');
    $stmt->execute([':student_id' => $student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        http_response_code(404);
        echo "<h1>Student Not Found</h1><p>No student found with ID: " . htmlspecialchars($student_id) . "</p>";
        exit;
    }
    
    // Get recent attendance summary (last 30 days)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN status = 'excuse' THEN 1 ELSE 0 END) as excused_days,
            MAX(attendance_date) as last_attendance
        FROM attendance_records 
        WHERE student_id = :student_id 
        AND attendance_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute([':student_id' => $student_id]);
    $attendance_summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get teacher/adviser info
    $stmt = $pdo->prepare("
        SELECT * FROM teachers 
        WHERE grade_level = :grade 
        AND strand = :strand 
        AND section_block = :section
        LIMIT 1
    ");
    $stmt->execute([
        ':grade' => $student['grade_level'],
        ':strand' => $student['strand'],
        ':section' => $student['section_block']
    ]);
    $adviser = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    http_response_code(500);
    echo "<h1>Database Error</h1><p>Unable to retrieve student information.</p>";
    exit;
}

// Calculate attendance percentage
$attendance_percentage = 0;
if ($attendance_summary['total_days'] > 0) {
    $attendance_percentage = round(($attendance_summary['present_days'] / $attendance_summary['total_days']) * 100, 1);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information - <?php echo htmlspecialchars($student['full_name']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #d6f5d6 0%, #eaffea 100%);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(33, 140, 33, 0.2);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .header {
            background: linear-gradient(135deg, #218c21, #176617);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.05) 10px,
                rgba(255,255,255,0.05) 20px
            );
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }
        
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        .content {
            padding: 40px;
        }
        
        .student-card {
            background: linear-gradient(135deg, #f8fff8, #eaffea);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 5px solid #218c21;
            box-shadow: 0 5px 20px rgba(33, 140, 33, 0.1);
        }
        
        .student-card h2 {
            color: #218c21;
            font-size: 1.8em;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .info-item {
            background: rgba(255, 255, 255, 0.7);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e0f2e0;
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 140, 33, 0.15);
        }
        
        .info-label {
            font-weight: 600;
            color: #176617;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1.1em;
            color: #333;
            font-weight: 500;
        }
        
        .attendance-section {
            background: linear-gradient(135deg, #fff3cd, #fef9e7);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 5px solid #f0ad4e;
            box-shadow: 0 5px 20px rgba(240, 173, 78, 0.1);
        }
        
        .attendance-section h2 {
            color: #b8860b;
            font-size: 1.8em;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .attendance-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9em;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .present { color: #218c21; }
        .late { color: #f0ad4e; }
        .absent { color: #d9534f; }
        .excused { color: #5bc0de; }
        
        .attendance-percentage {
            background: linear-gradient(135deg, #218c21, #2ecc71);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-top: 20px;
        }
        
        .percentage-number {
            font-size: 3em;
            font-weight: bold;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .adviser-section {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 5px solid #2196f3;
            box-shadow: 0 5px 20px rgba(33, 150, 243, 0.1);
        }
        
        .adviser-section h2 {
            color: #1976d2;
            font-size: 1.8em;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .footer p {
            color: #6c757d;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        
        .qr-info {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            color: #155724;
        }
        
        .scan-time {
            font-size: 0.8em;
            color: #666;
            margin-top: 10px;
            font-style: italic;
        }
        
        /* Mobile-first responsive design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .container {
                margin: 0;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(33, 140, 33, 0.15);
            }
            
            .header {
                padding: 20px 15px;
            }
            
            .header h1 {
                font-size: 1.8em;
            }
            
            .header p {
                font-size: 1em;
            }
            
            .content {
                padding: 15px;
            }
            
            .info-grid,
            .attendance-stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .student-card,
            .attendance-section,
            .adviser-section {
                padding: 20px 15px;
                margin-bottom: 20px;
            }
            
            .student-card h2,
            .attendance-section h2,
            .adviser-section h2 {
                font-size: 1.5em;
            }
            
            .stat-number {
                font-size: 2em;
            }
            
            .percentage-number {
                font-size: 2.5em;
            }
            
            .qr-info {
                padding: 12px;
                font-size: 0.9em;
            }
        }
        
        /* Extra small devices */
        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.5em;
            }
            
            .info-item {
                padding: 12px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-number {
                font-size: 1.8em;
            }
            
            .percentage-number {
                font-size: 2em;
            }
        }
        
        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .header::before {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏫 Student Information</h1>
            <p>QR Code Scan Result</p>
        </div>
        
        <div class="content">
            <!-- Student Personal Information -->
            <div class="student-card">
                <h2>👨‍🎓 Student Profile</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['full_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Student ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['student_id']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">LRN (Learner Reference Number)</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['lrn'] ?? 'Not set'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['gender']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Grade Level</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['grade_level']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Strand/Track</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['strand']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Section/Block</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['section_block']); ?></div>
                    </div>
                </div>
                
                <div class="qr-info">
                    <strong>✅ QR Code Successfully Scanned!</strong><br>
                    This information is displayed from scanning the student's QR code. 
                    The QR code contains the student ID which links to this comprehensive information.
                    <div class="scan-time">
                        Scanned on: <?php echo date('F j, Y \a\t g:i A'); ?>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Summary (Last 30 Days) -->
            <div class="attendance-section">
                <h2>📊 Recent Attendance Summary</h2>
                <p style="margin-bottom: 20px; color: #856404;">Last 30 days attendance record</p>
                
                <div class="attendance-stats">
                    <div class="stat-card">
                        <div class="stat-number present"><?php echo $attendance_summary['present_days']; ?></div>
                        <div class="stat-label">Present Days</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number late"><?php echo $attendance_summary['late_days']; ?></div>
                        <div class="stat-label">Late Days</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number absent"><?php echo $attendance_summary['absent_days']; ?></div>
                        <div class="stat-label">Absent Days</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number excused"><?php echo $attendance_summary['excused_days']; ?></div>
                        <div class="stat-label">Excused Days</div>
                    </div>
                </div>
                
                <?php if ($attendance_summary['total_days'] > 0): ?>
                    <div class="attendance-percentage">
                        <div style="font-size: 1.2em; margin-bottom: 10px;">Overall Attendance Rate</div>
                        <div class="percentage-number"><?php echo $attendance_percentage; ?>%</div>
                        <div>Based on <?php echo $attendance_summary['total_days']; ?> recorded school days</div>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 30px; color: #856404;">
                        <strong>No attendance records found for the last 30 days.</strong><br>
                        This student may be newly enrolled or no attendance has been recorded yet.
                    </div>
                <?php endif; ?>
                
                <?php if ($attendance_summary['last_attendance']): ?>
                    <div style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.8); border-radius: 8px;">
                        <strong>Last Attendance:</strong> <?php echo date('F j, Y', strtotime($attendance_summary['last_attendance'])); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Adviser Information -->
            <div class="adviser-section">
                <h2>👨‍🏫 Class Adviser</h2>
                <?php if ($adviser): ?>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Adviser Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($adviser['full_name']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($adviser['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Faculty/Department</div>
                            <div class="info-value"><?php echo htmlspecialchars($adviser['faculty'] ?? 'Not specified'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Handles Class</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($adviser['grade_level']) . ' - ' . 
                                           htmlspecialchars($adviser['strand']) . ' - ' . 
                                           htmlspecialchars($adviser['section_block']); ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 30px; color: #1976d2;">
                        <strong>No adviser information available</strong><br>
                        Contact the school office for adviser assignment details.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <div style="margin-bottom: 20px;">
                <a href="javascript:history.back()" style="
                    background: linear-gradient(135deg, #218c21, #2ecc71);
                    color: white;
                    padding: 12px 24px;
                    border-radius: 25px;
                    text-decoration: none;
                    display: inline-block;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(33, 140, 33, 0.3);
                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(33, 140, 33, 0.4)'" 
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(33, 140, 33, 0.3)'">
                    ⬅️ Go Back
                </a>
                <a href="index.php" style="
                    background: #fff;
                    color: #218c21;
                    border: 2px solid #218c21;
                    padding: 12px 24px;
                    border-radius: 25px;
                    text-decoration: none;
                    display: inline-block;
                    font-weight: 600;
                    margin-left: 10px;
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='#f0fff0'; this.style.transform='translateY(-2px)'" 
                   onmouseout="this.style.background='#fff'; this.style.transform='translateY(0)'">
                    🏠 Home
                </a>
            </div>
            
            <p><strong>📱 QR Code Information System</strong></p>
            <p>This page displays comprehensive student information retrieved by scanning the student's unique QR code.</p>
            <p>For questions or concerns, please contact the school administration office.</p>
            <p style="margin-top: 15px; font-size: 0.8em;">
                Generated on <?php echo date('F j, Y \a\t g:i A'); ?> | 
                System: School Attendance QR System v2.0
            </p>
        </div>
    </div>
</body>
</html>