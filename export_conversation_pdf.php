<?php
// export_conversation_pdf.php - Export conversation as printable page
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

$pdo = get_db();
$uid = (int)$_SESSION['user_id'];
$convoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$convoId) {
    die('Invalid conversation ID');
}

// Fetch conversation
$stmt = $pdo->prepare('SELECT question, response, created_at, updated_at FROM reviewer_conversations WHERE id = ? AND user_id = ?');
$stmt->execute([$convoId, $uid]);
$convo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$convo) {
    die('Conversation not found');
}

// Extract topic from first question line
$firstQuestion = explode("\n", $convo['question'])[0];
$topic = preg_replace('/[^a-zA-Z0-9\s]/', '', $firstQuestion);
$topic = substr($topic, 0, 50); // Limit to 50 chars
$topic = trim($topic) ?: 'Conversation';
$fileName = preg_replace('/\s+/', '_', $topic) . '_' . date('Y-m-d') . '.pdf';

// NO PDF headers - this is HTML that will be printed to PDF by browser
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($topic); ?> - Jether AI Review</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 40px; line-height: 1.6; color: #2c3e50; max-width: 900px; }
        h1 { color: #1e5128; border-bottom: 3px solid #2d6a4f; padding-bottom: 10px; margin-bottom: 20px; }
        .header-info { background: #f8fffe; padding: 15px; border-left: 4px solid #218c21; margin-bottom: 20px; }
        .meta { color: #5a6c7d; font-size: 14px; margin-bottom: 20px; }
        .section { margin: 20px 0; padding: 15px; background: #f8fffe; border-left: 4px solid #2d6a4f; }
        .label { font-weight: bold; color: #218c21; margin-bottom: 8px; font-size: 16px; }
        .content { white-space: pre-wrap; line-height: 1.8; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #d8f3dc; text-align: center; color: #5a6c7d; font-size: 12px; }
        .button-container { margin: 30px 0; text-align: center; }
        .btn { background: #218c21; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; margin: 0 5px; text-decoration: none; display: inline-block; }
        .btn:hover { background: #1e5128; }
        .btn-secondary { background: #5a6c7d; }
        .btn-secondary:hover { background: #3d4a5c; }
        @media print {
            body { margin: 20px; }
            .no-print { display: none; }
            .button-container { display: none; }
        }
    </style>
</head>
<body>
    <div class="header-info">
        <h1>🎓 Jether AI Review Session</h1>
        <div style="font-size: 18px; color: #2d6a4f; font-weight: 600;"><?php echo htmlspecialchars($topic); ?></div>
    </div>
    
    <div class="meta">
        <strong>📅 Created:</strong> <?php echo date('F d, Y h:i A', strtotime($convo['created_at'])); ?><br>
        <strong>🔄 Last Updated:</strong> <?php echo date('F d, Y h:i A', strtotime($convo['updated_at'])); ?><br>
        <strong>👤 Student:</strong> <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Student'); ?>
    </div>
    
    <div class="section">
        <div class="label">📝 Your Questions:</div>
        <div class="content"><?php echo nl2br(htmlspecialchars($convo['question'])); ?></div>
    </div>
    
    <div class="section">
        <div class="label">🤖 Jether AI's Response:</div>
        <div class="content"><?php echo nl2br(htmlspecialchars($convo['response'])); ?></div>
    </div>
    
    <div class="footer no-print">
        <p>💚 Powered by Jether AI - Palawan National School Review Center</p>
        <p style="font-size: 11px;">Created by Jether Garque for PNS Students</p>
    </div>
    
    <div class="button-container no-print">
        <button onclick="window.print()" class="btn">
            🖨️ Print / Save as PDF
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            ✖️ Close Window
        </button>
        <a href="recent_conversation.php" class="btn btn-secondary">
            📋 Back to Conversations
        </a>
    </div>
    
    <script>
        // Show instructions for saving as PDF
        window.addEventListener('load', function() {
            if (window.matchMedia) {
                const mediaQueryList = window.matchMedia('print');
                mediaQueryList.addListener(function(mql) {
                    if (mql.matches) {
                        console.log('Printing...');
                    }
                });
            }
        });
    </script>
</body>
</html>
