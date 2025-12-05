<?php
// recent_conversation.php - Show student's recent reviewer conversations
session_start();
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}
$pdo = get_db();
$uid = (int)$_SESSION['user_id'];

// Handle continue conversation
if (isset($_GET['continue']) && is_numeric($_GET['continue'])) {
    $convId = (int)$_GET['continue'];
    $stmt = $pdo->prepare('SELECT question, response FROM reviewer_conversations WHERE id = ? AND user_id = ?');
    $stmt->execute([$convId, $uid]);
    $convo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($convo) {
        // Redirect to review center with conversation data
        header('Location: review_center.php?convo_id=' . $convId);
        exit;
    }
}

$stmt = $pdo->prepare('SELECT id, question, response, created_at, updated_at FROM reviewer_conversations WHERE user_id = ? ORDER BY updated_at DESC LIMIT 20');
$stmt->execute([$uid]);
$convos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Recent Conversations - PNS Review Center</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body{font-family:'Segoe UI',Arial,sans-serif;background:#f8fffe;color:#2c3e50;margin:0}
    .container{max-width:900px;margin:32px auto;padding:0 24px}
    .panel{background:#fff;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:24px 28px;margin-bottom:20px;border:1px solid #d8f3dc}
    h1{color:#1e5128;margin-bottom:18px}
    .convo{margin-bottom:24px;padding:16px;border:2px solid #d8f3dc;border-radius:12px;background:#f8fffe}
    .question{font-weight:700;color:#218c21;margin-bottom:8px}
    .response{margin-top:8px;color:#2d6a4f;line-height:1.6}
    .timestamp{font-size:12px;color:#5a6c7d;margin-top:8px}
    .continue-btn{background:#218c21;color:#fff;border:none;padding:8px 16px;border-radius:8px;cursor:pointer;font-weight:700;text-decoration:none;display:inline-block;margin-top:8px}
    .continue-btn:hover{background:#1e5128}
    .export-btn{background:#2d6a4f;color:#fff;border:none;padding:8px 16px;border-radius:8px;cursor:pointer;font-weight:700;text-decoration:none;display:inline-block;margin-top:8px;margin-left:8px}
    .export-btn:hover{background:#1e5128}
    .empty{color:#d32f2f;font-style:italic}
    .back-btn{background:#2d6a4f;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:700;text-decoration:none;display:inline-block;margin-bottom:16px}
  </style>
</head>
<body>
  <div class="container">
    <a href="review_center.php" class="back-btn">← Back to Review Center</a>
    <div class="panel">
      <h1>Recent Conversations with Jether</h1>
      <?php if (empty($convos)): ?>
        <div class="empty">No recent conversations found.</div>
      <?php else: ?>
        <?php foreach ($convos as $c): ?>
          <div class="convo">
            <div class="question">🗨️ <?php echo nl2br(htmlspecialchars($c['question'])); ?></div>
            <div class="response"><?php echo nl2br(htmlspecialchars($c['response'])); ?></div>
            <div class="timestamp">Created: <?php echo date('M d, Y H:i', strtotime($c['created_at'])); ?> | Updated: <?php echo date('M d, Y H:i', strtotime($c['updated_at'])); ?></div>
            <a href="review_center.php?convo_id=<?php echo $c['id']; ?>" class="continue-btn">Continue Conversation</a>
            <a href="export_conversation_pdf.php?id=<?php echo $c['id']; ?>" class="export-btn" target="_blank">🖨️ Print/Export PDF</a>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
