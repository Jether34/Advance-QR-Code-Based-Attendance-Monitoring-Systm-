<?php
// wall.php - community wall for posts and announcements
session_start();
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$pdo = get_db();
$uid = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle new post
// Community posting is disabled for both teachers and students

// Fetch all posts (latest first)
$posts = $pdo->query('
    SELECT p.*, t.full_name, "teacher" AS role
    FROM posts p
    JOIN teachers t ON p.user_id = t.id
    UNION ALL
    SELECT p.*, s.full_name, "student" AS role
    FROM posts p
    JOIN students s ON p.user_id = s.id
    ORDER BY created_at DESC
')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Community Wall</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="student_dashboard.php">Dashboard</a>
        <a href="wall.php">Community Wall</a>
        <a href="chatroom.php">Group Chatroom</a>
        <a href="edit_profile.php">Edit Profile</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
    <h1>Community Wall</h1>
    <!-- Community posting is disabled for both teachers and students -->
    <h2>All Posts & Announcements</h2>
    <p style="color:#b00;font-weight:bold">Community posting is currently disabled.</p>
    </div>
</body>
</html>
