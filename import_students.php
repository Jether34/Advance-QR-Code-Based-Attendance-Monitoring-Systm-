<?php
require_once __DIR__ . '/bootstrap.php';
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security_utils.php';

$csrf_token = generate_csrf_token();

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

$messages = [];
$uploaded_count = 0;
$error_count = 0;

// Handle CSV file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $messages[] = ['error' => '❌ Invalid CSRF token.'];
    } else {
        $file = $_FILES['csv_file'];
        
        if ($file['error'] === 0 && in_array(pathinfo($file['name'], PATHINFO_EXTENSION), ['csv', 'txt'])) {
            $handle = fopen($file['tmp_name'], 'r');
            
            // Skip header row
            $header = fgetcsv($handle);
            $expectedHeaders = ['student_id', 'full_name', 'grade_level', 'strand', 'section_block'];
            
            if ($header !== $expectedHeaders) {
                $messages[] = ['error' => '❌ CSV format invalid. Expected columns: ' . implode(', ', $expectedHeaders)];
            } else {
                $pdo->beginTransaction();
                
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) < 5 || empty($row[0])) continue;
                    
                    $studentId = trim($row[0]);
                    $fullName = trim($row[1]);
                    $gradeLevel = trim($row[2]);
                    $strand = trim($row[3]);
                    $sectionBlock = trim($row[4]);
                    
                    // Validate data
                    if (!$studentId || !$fullName) {
                        $error_count++;
                        continue;
                    }
                    
                    try {
                        // Check if exists
                        $checkStmt = $pdo->prepare('SELECT id FROM students WHERE student_id = :sid');
                        $checkStmt->execute([':sid' => $studentId]);
                        
                        if ($checkStmt->fetch()) {
                            // Update existing
                            $updateStmt = $pdo->prepare('UPDATE students SET full_name = :name, grade_level = :grade, strand = :strand, section_block = :block WHERE student_id = :sid');
                            $updateStmt->execute([
                                ':name' => $fullName,
                                ':grade' => $gradeLevel,
                                ':strand' => $strand,
                                ':block' => $sectionBlock,
                                ':sid' => $studentId
                            ]);
                        } else {
                            // Insert new
                            $insertStmt = $pdo->prepare('INSERT INTO students (student_id, full_name, grade_level, strand, section_block) VALUES (:sid, :name, :grade, :strand, :block)');
                            $insertStmt->execute([
                                ':sid' => $studentId,
                                ':name' => $fullName,
                                ':grade' => $gradeLevel,
                                ':strand' => $strand,
                                ':block' => $sectionBlock
                            ]);
                        }
                        $uploaded_count++;
                    } catch (Exception $e) {
                        $error_count++;
                    }
                }
                
                $pdo->commit();
                fclose($handle);
                $messages[] = ['success' => "✅ Imported {$uploaded_count} students. Errors: {$error_count}"];
            }
        } else {
            $messages[] = ['error' => '❌ Invalid file. Please upload a CSV file.'];
        }
    }
}

// Generate CSV template
if (isset($_GET['download_template'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="student_template.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['student_id', 'full_name', 'grade_level', 'strand', 'section_block']);
    fputcsv($output, ['12345', 'Juan Dela Cruz', '11', 'ICT-CSS', '1']);
    fputcsv($output, ['12346', 'Maria Santos', '11', 'ICT-PROGRAMMING', '1']);
    fputcsv($output, ['12347', 'Pedro Reyes', '12', 'AFA', '2']);
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Student Import - PNS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            background: linear-gradient(135deg, #1e5128 0%, #2d6a4f 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 32px;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 24px;
            text-align: center;
        }
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            border-left: 4px solid;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }
        .upload-box {
            border: 2px dashed #2d6a4f;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f9f9f9;
        }
        .upload-box:hover {
            background: #f0f7f4;
            border-color: #1e5128;
        }
        .upload-box input[type="file"] {
            display: none;
        }
        .file-icon {
            font-size: 3em;
            margin-bottom: 16px;
        }
        .upload-text {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .upload-hint {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        button {
            flex: 1;
            padding: 12px 16px;
            border: none;
            border-radius: 6px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-upload {
            background: #2d6a4f;
            color: white;
        }
        .btn-upload:hover {
            background: #1e5128;
        }
        .btn-template {
            background: #95a5a6;
            color: white;
        }
        .btn-template:hover {
            background: #7f8c8d;
        }
        .btn-back {
            background: #ecf0f1;
            color: #2c3e50;
        }
        .btn-back:hover {
            background: #bdc3c7;
        }
        .instructions {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            color: #1565c0;
        }
        .instructions h3 {
            margin-top: 0;
            color: #1565c0;
        }
        .instructions ol {
            margin-left: 20px;
            margin-bottom: 0;
        }
        .instructions li {
            margin-bottom: 8px;
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Batch Student Upload</h1>

        <?php foreach ($messages as $msg): ?>
            <div class="message <?php echo isset($msg['success']) ? 'success' : 'error'; ?>">
                <?php echo $msg['success'] ?? $msg['error']; ?>
            </div>
        <?php endforeach; ?>

        <div class="instructions">
            <h3>📋 Instructions:</h3>
            <ol>
                <li>Download the CSV template using the button below</li>
                <li>Fill in student data with columns: student_id, full_name, grade_level, strand, section_block</li>
                <li>Save the file as CSV format</li>
                <li>Upload the file here</li>
                <li>All changes are logged automatically</li>
            </ol>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="upload-box" onclick="document.getElementById('csv_file').click()">
                <div class="file-icon">📁</div>
                <div class="upload-text">Click to select CSV file</div>
                <div class="upload-hint">or drag and drop CSV file here</div>
                <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" required>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-upload">📤 Upload & Import</button>
                <a href="?download_template=1" style="flex: 1; text-decoration: none;">
                    <button type="button" class="btn-template">📥 Download Template</button>
                </a>
            </div>
        </form>

        <div class="button-group" style="margin-top: 16px;">
            <a href="teacher_dashboard.php" style="flex: 1; text-decoration: none;">
                <button type="button" class="btn-back">← Back to Dashboard</button>
            </a>
        </div>
    </div>

    <script>
        const uploadBox = document.querySelector('.upload-box');
        const fileInput = document.getElementById('csv_file');

        // Drag and drop
        uploadBox.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadBox.style.background = '#f0f7f4';
            uploadBox.style.borderColor = '#1e5128';
        });

        uploadBox.addEventListener('dragleave', () => {
            uploadBox.style.background = '#f9f9f9';
            uploadBox.style.borderColor = '#2d6a4f';
        });

        uploadBox.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadBox.style.background = '#f9f9f9';
            uploadBox.style.borderColor = '#2d6a4f';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
            }
        });
    </script>
</body>
</html>
