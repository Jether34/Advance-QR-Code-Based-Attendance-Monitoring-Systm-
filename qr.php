<?php
// qr.php - Short URL redirect for QR codes
// This provides a shorter, cleaner URL for QR codes
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

$student_id = $_GET['s'] ?? $_GET['id'] ?? '';

if (!$student_id) {
    // Show QR scanner page if no student ID provided
    header('Location: scan.php');
    exit;
}

// Redirect to full student information page
header('Location: student_info.php?id=' . urlencode($student_id));
exit;
?>