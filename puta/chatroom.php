<?php
// chatroom.php - group chat for students/teachers with same grade, strand, block/section
session_start();
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$pdo = get_db();
$uid = $_SESSION['user_id'];
$user = $pdo->query("SELECT * FROM users WHERE id = $uid")->fetch(PDO::FETCH_ASSOC);
if (!$user) { echo 'User not found'; exit; }

// Group key: grade-strand-block
$group_key = $user['grade'] . '-' . $user['strand'] . '-' . $user['block_section'];

// Handle new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    if ($content) {
        $stmt = $pdo->prepare('INSERT INTO messages (group_key, user_id, content) VALUES (:gk, :uid, :content)');
        $stmt->execute([':gk'=>$group_key, ':uid'=>$uid, ':content'=>$content]);
        header('Location: chatroom.php');
        exit;
    }
}

// Fetch messages for this group
$msgs = $pdo->prepare('SELECT messages.*, users.full_name FROM messages JOIN users ON messages.user_id = users.id WHERE group_key = :gk ORDER BY messages.created_at ASC');
$msgs->execute([':gk'=>$group_key]);
$messages = $msgs->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Group Chatroom</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="student_dashboard.php">Dashboard</a>
        <a href="chatroom.php">Group Chatroom</a>
        <a href="edit_profile.php">Edit Profile</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
    <h1>Group Chatroom</h1>
    <p>Group: <b><?php echo htmlspecialchars($group_key); ?></b></p>
    <div style="border:1px solid #ccc;padding:10px;height:300px;overflow-y:scroll;background:#fafaff">
        <?php foreach($messages as $m): ?>
            <div style="margin-bottom:8px">
                <b><?php echo htmlspecialchars($m['full_name']); ?>:</b>
                <span><?php echo nl2br(htmlspecialchars($m['content'])); ?></span>
                <small style="color:#888"><?php echo htmlspecialchars($m['created_at']); ?></small>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="post" style="margin-top:10px">
        <input name="content" style="width:80%" placeholder="Type a message..." required />
        <button type="submit">Send</button>
    </form>
    </div>
</body>
</html>
