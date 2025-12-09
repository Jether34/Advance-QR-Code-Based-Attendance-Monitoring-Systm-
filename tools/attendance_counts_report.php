<?php
// Simple read-only report: attendance counts for the last 7 days
require_once __DIR__ . '/../db.php';

try {
    $pdo = get_db();
} catch (Exception $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . PHP_EOL);
    exit(2);
}

echo "Attendance counts (last 7 days)\n";
echo str_repeat('=', 40) . "\n";

// Total attendance records
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_records WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$stmt->execute();
$total = $stmt->fetchColumn();
echo "Total attendance records: $total\n";

// Records with correction_reason
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_records WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND correction_reason IS NOT NULL AND correction_reason != ''");
$stmt->execute();
$corrected = $stmt->fetchColumn();
echo "Corrected records: $corrected\n";

// Breakdown by grade/strand/block (top 50)
echo "\nBreakdown by grade/strand/block (top 50):\n";
$stmt = $pdo->prepare(
    "SELECT s.grade_level, s.strand, s.section_block, COUNT(a.id) AS cnt
     FROM attendance_records a
     JOIN students s ON a.student_id = s.student_id
     WHERE a.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
     GROUP BY s.grade_level, s.strand, s.section_block
     ORDER BY cnt DESC
     LIMIT 50"
);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) {
    echo "(no records found in breakdown)\n";
} else {
    foreach ($rows as $r) {
        printf("%s | %s | %s : %d\n", $r['grade_level'], $r['strand'], $r['section_block'], $r['cnt']);
    }
}

// Show sample recent records (limit 10)
echo "\nSample recent attendance rows (limit 10):\n";
$stmt = $pdo->prepare("SELECT a.id, a.student_id, s.full_name, a.attendance_date, a.status, a.correction_reason FROM attendance_records a LEFT JOIN students s ON a.student_id = s.student_id WHERE a.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY a.attendance_date DESC LIMIT 10");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) {
    echo "(no recent records)\n";
} else {
    foreach ($rows as $r) {
        printf("id:%s student:%s (%s) date:%s status:%s corrected:%s\n", $r['id'], $r['full_name'] ?: 'n/a', $r['student_id'], $r['attendance_date'], $r['status'], $r['correction_reason'] ?: '(none)');
    }
}

echo "\nDone.\n";

?>
