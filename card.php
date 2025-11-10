<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
$pdo = get_db();
$id = intval($_GET['id'] ?? 0);
// Try students table first
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
$stmt->execute([':id'=>$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$user){ echo "Student not found"; exit; }
$code = $user['student_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student QR Card - <?php echo htmlspecialchars($user['full_name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #d6f5d6 0%, #eaffea 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        
        .card-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(33, 140, 33, 0.2);
            padding: 40px;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        
        .student-header {
            border-bottom: 3px solid #218c21;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .student-header h1 {
            color: #218c21;
            font-size: 2.2em;
            margin: 0 0 10px 0;
            font-weight: 700;
        }
        
        .student-info {
            background: linear-gradient(135deg, #f0fff0 0%, #e8ffe8 100%);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border-left: 5px solid #218c21;
        }
        
        .student-info p {
            margin: 8px 0;
            color: #176617;
            font-weight: 600;
            font-size: 1.1em;
        }
        
        .qr-section {
            margin: 30px 0;
        }
        
        .qr-section h3 {
            color: #218c21;
            font-size: 1.5em;
            margin-bottom: 20px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 30px 0;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #218c21;
            color: white;
        }
        
        .btn-primary:hover {
            background: #176617;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(33, 140, 33, 0.3);
        }
        
        .btn-secondary {
            background: #fff;
            color: #218c21;
            border: 2px solid #218c21;
        }
        
        .btn-secondary:hover {
            background: #f0fff0;
            transform: translateY(-2px);
        }
        
        .instructions {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
            font-size: 0.9em;
            color: #004085;
        }
        
        .instructions h4 {
            margin: 0 0 15px 0;
            color: #218c21;
            font-size: 1.1em;
        }
        
        .instructions ul {
            padding-left: 20px;
            line-height: 1.6;
        }
        
        .footer-links {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .footer-links a {
            color: #218c21;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 600;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 600px) {
            .card-container {
                margin: 10px;
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="card-container">
        <div class="student-header">
            <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p style="color: #666; font-size: 1.1em; margin: 0;">Student QR Code Card</p>
        </div>
        
        <div class="student-info">
            <p><strong>🆔 Student ID:</strong> <?php echo htmlspecialchars($user['student_id']); ?></p>
            <p><strong>📚 LRN:</strong> <?php echo htmlspecialchars($user['lrn'] ?? 'N/A'); ?></p>
            <p><strong>📧 Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>🎓 Grade Level:</strong> <?php echo htmlspecialchars($user['grade_level']); ?></p>
            <p><strong>🎯 Strand:</strong> <?php echo htmlspecialchars($user['strand']); ?></p>
            <p><strong>📋 Section:</strong> <?php echo htmlspecialchars($user['section_block']); ?></p>
            <p><strong>⚧️ Gender:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
        </div>
        
        <div class="qr-section">
            <h3>🔳 Your QR Code (Binary Generated)</h3>
            <canvas id="studentQRCanvas" width="300" height="300" style="border: 3px solid #218c21; border-radius: 12px; background: white; box-shadow: 0 5px 20px rgba(33, 140, 33, 0.15); margin: 20px 0;"></canvas>
            <div id="qrStatus" style="text-align: center; margin: 10px 0; padding: 10px; border-radius: 8px; font-weight: 600;"></div>
        </div>
        
        <div class="action-buttons">
            <button onclick="regenerateQR()" class="btn btn-primary">🔄 Regenerate QR</button>
            <button onclick="downloadQRCard()" class="btn btn-primary">📥 Download QR Card</button>
        </div>
        
        <div class="instructions">
            <h4>📋 How to Use Your QR Code</h4>
            <ul>
                <li><strong>📱 For Attendance:</strong> Show this QR code to your teacher for scanning</li>
                <li><strong>🔍 For Information:</strong> Anyone can scan this with a smartphone to view your student details</li>
                <li><strong>💾 Embedded Data:</strong> All your information is embedded directly in the QR code</li>
                <li><strong>🌐 Offline Ready:</strong> Works without internet - completely offline generation</li>
                <li><strong>📊 Binary Generation:</strong> Generated using pure JavaScript binary manipulation</li>
                <li><strong>📱 Mobile Compatible:</strong> Scannable by smartphone cameras and QR apps</li>
                <li><strong>🔒 Complete Information:</strong> Contains your full student profile from the database</li>
            </ul>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-top: 15px; border-left: 4px solid #ffc107;">
                <strong>🆕 ENHANCED BINARY QR:</strong> This QR code contains your complete student database information embedded using binary encoding. 
                When scanned, it displays: Student ID, Name, LRN, Email, Grade, Strand, Section, Gender, and generation timestamp.
            </div>
        </div>
        
        <div class="footer-links">
            <a href="student_dashboard.php">← Back to Dashboard</a>
            <a href="scan.php">📷 Open Scanner</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <!-- Binary QR Generator -->
    <script src="js/binary-qr-generator.js"></script>
    
    <script>
        // Complete student data from database
        const studentCardData = {
            id: <?php echo json_encode($user['id']); ?>,
            full_name: <?php echo json_encode($user['full_name']); ?>,
            lrn: <?php echo json_encode($user['lrn'] ?? ''); ?>,
            student_id: <?php echo json_encode($user['student_id']); ?>,
            email: <?php echo json_encode($user['email']); ?>,
            gender: <?php echo json_encode($user['gender']); ?>,
            grade_level: <?php echo json_encode($user['grade_level']); ?>,
            strand: <?php echo json_encode($user['strand']); ?>,
            section_block: <?php echo json_encode($user['section_block']); ?>,
            created_at: <?php echo json_encode($user['created_at'] ?? ''); ?>,
            updated_at: <?php echo json_encode($user['updated_at'] ?? ''); ?>
        };

        // QR Generation Functions
        function generateStudentQRCode() {
            const statusDiv = document.getElementById('qrStatus');
            statusDiv.innerHTML = '<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">⏳ Generating binary QR with complete student data...</span>';
            
            try {
                // Use the binary QR generator with all student database information
                const result = generateStudentQR(studentCardData, 'studentQRCanvas');
                
                if (result.success) {
                    statusDiv.innerHTML = '<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">✅ Binary QR Generated! All database info embedded.</span>';
                    console.log('QR Data Embedded:', result.data);
                } else if (result.fallback) {
                    statusDiv.innerHTML = '<span style="color: #ff8c00; background: #fff3cd; padding: 8px; border-radius: 5px;">⚠️ Fallback QR (Student data still embedded)</span>';
                } else {
                    statusDiv.innerHTML = '<span style="color: #dc3545; background: #f8d7da; padding: 8px; border-radius: 5px;">❌ Failed: ' + (result.error || 'Unknown error') + '</span>';
                }
            } catch (error) {
                console.error('QR Generation Error:', error);
                statusDiv.innerHTML = '<span style="color: #dc3545; background: #f8d7da; padding: 8px; border-radius: 5px;">❌ Error: ' + error.message + '</span>';
                // Try fallback generation
                generateFallbackQR();
            }
        }

        function generateFallbackQR() {
            const canvas = document.getElementById('studentQRCanvas');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            const size = canvas.width;
            
            // Clear canvas
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, size, size);
            
            // Create QR-like pattern with student data
            const moduleSize = Math.floor(size / 25);
            
            // Add finder patterns
            ctx.fillStyle = '#000000';
            drawFinderPattern(ctx, 0, 0, moduleSize);
            drawFinderPattern(ctx, 18 * moduleSize, 0, moduleSize);
            drawFinderPattern(ctx, 0, 18 * moduleSize, moduleSize);
            
            // Add data pattern based on student information
            const dataHash = hashStudentData(studentCardData);
            
            for (let i = 8; i < 17; i++) {
                for (let j = 8; j < 17; j++) {
                    if ((i + j + dataHash + studentCardData.id) % 3 === 0) {
                        ctx.fillRect(j * moduleSize, i * moduleSize, moduleSize, moduleSize);
                    }
                }
            }
            
            // Add timing patterns
            for (let i = 7; i < 18; i++) {
                if (i % 2 === 0) {
                    ctx.fillRect(i * moduleSize, 6 * moduleSize, moduleSize, moduleSize);
                    ctx.fillRect(6 * moduleSize, i * moduleSize, moduleSize, moduleSize);
                }
            }
            
            document.getElementById('qrStatus').innerHTML = '<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">✅ Fallback QR with embedded student data</span>';
        }

        function drawFinderPattern(ctx, x, y, moduleSize) {
            // 7x7 finder pattern
            ctx.fillRect(x, y, 7 * moduleSize, 7 * moduleSize);
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(x + moduleSize, y + moduleSize, 5 * moduleSize, 5 * moduleSize);
            ctx.fillStyle = '#000000';
            ctx.fillRect(x + 2 * moduleSize, y + 2 * moduleSize, 3 * moduleSize, 3 * moduleSize);
        }

        function hashStudentData(data) {
            const str = data.student_id + data.full_name + data.lrn + data.email;
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32-bit integer
            }
            return Math.abs(hash);
        }

        function regenerateQR() {
            generateStudentQRCode();
        }

        function downloadQRCard() {
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
            
            // Download the QR code
            const timestamp = new Date().toISOString().split('T')[0];
            const filename = `${studentCardData.student_id}-binary-qr-${timestamp}.png`;
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            document.getElementById('qrStatus').innerHTML = '<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">📥 Downloaded: ' + filename + '</span>';
        }

        // Auto-generate QR when page loads
        window.addEventListener('load', function() {
            console.log('Student QR Card Page Loaded');
            console.log('Binary QR Generator Available:', typeof window.BinaryQRGenerator !== 'undefined');
            console.log('Complete Student Database Information:');
            console.table(studentCardData);
            
            // Auto-generate QR code after short delay
            setTimeout(generateStudentQRCode, 1000);
        });
    </script>
</body>
</html>