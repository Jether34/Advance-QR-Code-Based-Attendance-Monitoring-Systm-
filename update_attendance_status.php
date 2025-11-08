<?php
require_once __DIR__ . '/db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}
$pdo = get_db();
$student_id = $_POST['student_id'] ?? '';
$status = $_POST['status'] ?? '';
$today = date('Y-m-d');
if (!$student_id || !$status) {
    header('Location: teacher_dashboard.php?section=today&error=missing');
    exit;
}
$update = $pdo->prepare('UPDATE attendance_records SET status = :status WHERE student_id = :sid AND attendance_date = :date');
$update->execute([
    ':status' => $status,
    ':sid' => $student_id,
    ':date' => $today
]);
header('Location: teacher_dashboard.php?section=today');
exit;
