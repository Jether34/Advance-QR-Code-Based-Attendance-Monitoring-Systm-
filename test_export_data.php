<?php
echo "Testing database connection...\n";
require 'db.php';
$pdo = get_db();

$result = $pdo->query('SELECT COUNT(*) as count FROM students');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo "Total students: " . $row['count'] . "\n";

$result = $pdo->query('SELECT COUNT(*) as count FROM attendance_records');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo "Total attendance records: " . $row['count'] . "\n";

// Test a sample export query
$students = $pdo->prepare("SELECT s.student_id, s.full_name AS name, s.strand, s.section_block AS block, s.grade_level, s.gender FROM students s WHERE (? = '' OR s.grade_level = ?) AND (? = '' OR s.strand = ?) AND (? = '' OR s.section_block = ?) ORDER BY s.gender, s.full_name");
$students->execute(['Grade 11', 'Grade 11', 'STEM', 'STEM', 'A', 'A']);
$result = $students->fetchAll(PDO::FETCH_ASSOC);
echo "Sample query (Grade 11, STEM, A): " . count($result) . " students\n";
foreach ($result as $s) {
    echo "  - " . $s['name'] . "\n";
}
?>
