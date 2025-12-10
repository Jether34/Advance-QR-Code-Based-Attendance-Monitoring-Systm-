<?php
require_once 'db.php';
require_once 'logging.php';

$pdo = get_db();
require_once __DIR__ . '/bootstrap.php';

$request_start = microtime(true);

// Get filters from query string
$strand = isset($_GET['strand']) ? trim($_GET['strand']) : '';
$block = isset($_GET['block']) ? trim($_GET['block']) : '';
$grade_level = isset($_GET['grade_level']) ? trim($_GET['grade_level']) : '';
$teacher_name = isset($_GET['teacher_name']) ? trim($_GET['teacher_name']) : 'Adviser';
$month = $_GET['month'] ?? date('Y-m');

// School info
$school_name = 'PALAWAN NATIONAL SCHOOL';
$school_id = '303101';
$district = 'Puerto Princesa I';
$division = 'PUERTO PRINCESA';
$region = 'IV-B';

// Fetch students
$query = "SELECT s.student_id,
                 s.full_name AS name,
                 s.strand,
                 s.section_block AS block,
                 s.grade_level,
                 s.gender
          FROM students s
          WHERE (? = '' OR s.grade_level = ?)
            AND (? = '' OR s.strand = ?)
            AND (? = '' OR s.section_block = ?)
          ORDER BY s.gender, s.full_name";
$stmt = $pdo->prepare($query);
$stmt->execute([$grade_level, $grade_level, $strand, $strand, $block, $block]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get school days (Mon-Fri)
$start = date('Y-m-01', strtotime($month));
$end = date('Y-m-t', strtotime($month));
$dates = [];
$period = new DatePeriod(new DateTime($start), new DateInterval('P1D'), (new DateTime($end))->modify('+1 day'));
foreach ($period as $dt) {
    if (in_array($dt->format('N'), [1,2,3,4,5])) $dates[] = $dt->format('Y-m-d');
}

// Get attendance (mark as 'x' for absent, leave empty for present)
$attendance = [];
foreach ($students as $student) {
    $marks = [];
    foreach ($dates as $d) {
        $stmt2 = $pdo->prepare("SELECT status FROM attendance_records WHERE student_id = ? AND attendance_date = ?");
        $stmt2->execute([$student['student_id'], $d]);
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        if (!$row2) {
            $marks[] = 'x'; // absent by default
        } else {
            switch ($row2['status']) {
                case 'present': $marks[] = ''; break;
                case 'late': $marks[] = ''; break;
                case 'morning_half_day':
                case 'afternoon_half_day': $marks[] = ''; break;
                case 'excuse': $marks[] = ''; break;
                case 'absent': $marks[] = 'x'; break;
                default: $marks[] = ''; break;
            }
        }
    }
    $attendance[$student['student_id']] = $marks;
}

// Count absences (x marks)
$totals = [];
foreach ($attendance as $student_id => $marks) {
    $totals[$student_id] = array_sum(array_map(function($x){ return $x === 'x' ? 1 : 0; }, $marks));
}

// Summary statistics
$male_count = 0;
$female_count = 0;
$male_students = [];
$female_students = [];
foreach ($students as $s) {
    if (strtolower($s['gender']) === 'female') {
        $female_count++;
        $female_students[] = $s;
    } else {
        $male_count++;
        $male_students[] = $s;
    }
}
$total_students = count($students);

// Use TCPDF for PDF generation
require 'vendor/autoload.php';

class MYPDF extends \TCPDF {
    public function Header() {}
    public function Footer() {}
}

$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(8, 8, 8);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->AddPage();

// Title (matching SF2 header)
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Cell(0, 5, 'School Form 2 Daily Attendance Report of Learners', 0, 1, 'C');
$pdf->SetFont('Helvetica', '', 9);
$pdf->Ln(2);

// School info row 1
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(10, 4, 'School Name:', 0, 0, 'L');
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(40, 4, $school_name, 0, 0, 'L');
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(20, 4, 'School ID:', 0, 0, 'L');
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(0, 4, $school_id, 0, 1, 'L');

// School info row 2
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(10, 4, 'District:', 0, 0, 'L');
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(40, 4, $district, 0, 0, 'L');
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(20, 4, 'Division:', 0, 0, 'L');
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(0, 4, $division, 0, 1, 'L');

$pdf->Ln(2);

// Month, grade, strand, section
$monthLabel = strtoupper(date('F', strtotime($month)));
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(25, 4, "Month: $monthLabel", 0, 0, 'L');
$pdf->Cell(30, 4, "Grade Level: $grade_level", 0, 0, 'L');
$pdf->Cell(30, 4, "Strand: $strand", 0, 0, 'L');
$pdf->Cell(0, 4, "Section: $block", 0, 1, 'L');

$schoolYear = date('Y') . '-' . (date('Y') + 1);
$pdf->Cell(25, 4, "S.Y.: $schoolYear", 0, 0, 'L');
$pdf->Cell(0, 4, "School Days: " . count($dates), 0, 1, 'L');

$pdf->Ln(3);

// Attendance header (date row)
$pdf->SetFont('Helvetica', 'B', 8);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(5, 5, 'No.', 1, 0, 'C', true);
$pdf->Cell(30, 5, 'Name', 1, 0, 'L', true);
foreach ($dates as $d) {
    $day = date('d', strtotime($d));
    $dow = date('D', strtotime($d))[0];
    $pdf->Cell(4, 5, $day, 1, 0, 'C', true);
}
$pdf->Cell(8, 5, 'Total', 1, 1, 'C', true);
$pdf->SetFillColor(255, 255, 255);

// Male students
$maleNum = 0;
foreach ($male_students as $student) {
    $maleNum++;
    $pdf->SetFont('Helvetica', '', 7);
    $pdf->Cell(5, 5, $maleNum, 1, 0, 'C');
    $pdf->Cell(30, 5, substr($student['name'], 0, 25), 1, 0, 'L');
    foreach ($dates as $j => $d) {
        $mark = $attendance[$student['student_id']][$j] ?? '';
        $pdf->Cell(4, 5, $mark, 1, 0, 'C');
    }
    $pdf->Cell(8, 5, $totals[$student['student_id']], 1, 1, 'C');
}

// Male daily total row
$pdf->SetFont('Helvetica', 'B', 7);
$pdf->Cell(5, 5, '', 1, 0, 'C');
$pdf->Cell(30, 5, 'MALE TOTAL', 1, 0, 'L');
$male_daily_totals = [];
for ($j = 0; $j < count($dates); $j++) {
    $count = 0;
    foreach ($male_students as $s) {
        $count += ($attendance[$s['student_id']][$j] === 'x' ? 1 : 0);
    }
    $male_daily_totals[] = $count;
    $pdf->Cell(4, 5, $count, 1, 0, 'C');
}
$male_total_absences = array_sum($male_daily_totals);
$pdf->Cell(8, 5, $male_total_absences, 1, 1, 'C');

$pdf->Ln(2);

// Female students
$femaleNum = 0;
foreach ($female_students as $student) {
    $femaleNum++;
    $pdf->SetFont('Helvetica', '', 7);
    $pdf->Cell(5, 5, $femaleNum, 1, 0, 'C');
    $pdf->Cell(30, 5, substr($student['name'], 0, 25), 1, 0, 'L');
    foreach ($dates as $j => $d) {
        $mark = $attendance[$student['student_id']][$j] ?? '';
        $pdf->Cell(4, 5, $mark, 1, 0, 'C');
    }
    $pdf->Cell(8, 5, $totals[$student['student_id']], 1, 1, 'C');
}

// Female daily total row
$pdf->SetFont('Helvetica', 'B', 7);
$pdf->Cell(5, 5, '', 1, 0, 'C');
$pdf->Cell(30, 5, 'FEMALE TOTAL', 1, 0, 'L');
$female_daily_totals = [];
for ($j = 0; $j < count($dates); $j++) {
    $count = 0;
    foreach ($female_students as $s) {
        $count += ($attendance[$s['student_id']][$j] === 'x' ? 1 : 0);
    }
    $female_daily_totals[] = $count;
    $pdf->Cell(4, 5, $count, 1, 0, 'C');
}
$female_total_absences = array_sum($female_daily_totals);
$pdf->Cell(8, 5, $female_total_absences, 1, 1, 'C');

// Combined daily total row
$pdf->SetFont('Helvetica', 'B', 7);
$pdf->Cell(5, 5, '', 1, 0, 'C');
$pdf->Cell(30, 5, 'COMBINED TOTAL', 1, 0, 'L');
$combined_daily_totals = [];
for ($j = 0; $j < count($dates); $j++) {
    $count = $male_daily_totals[$j] + $female_daily_totals[$j];
    $combined_daily_totals[] = $count;
    $pdf->Cell(4, 5, $count, 1, 0, 'C');
}
$combined_total_absences = array_sum($combined_daily_totals);
$pdf->Cell(8, 5, $combined_total_absences, 1, 1, 'C');

$pdf->Ln(5);

// Summary table (matching SF2 Format)
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(0, 6, 'SUMMARY', 0, 1, 'C');

$pdf->SetFont('Helvetica', 'B', 8);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(50, 5, 'Metric', 1, 0, 'L', true);
$pdf->Cell(15, 5, 'M', 1, 0, 'C', true);
$pdf->Cell(15, 5, 'F', 1, 0, 'C', true);
$pdf->Cell(0, 5, 'Total', 1, 1, 'C', true);
$pdf->SetFillColor(255, 255, 255);

$pdf->SetFont('Helvetica', '', 8);

// Summary rows
$summaryData = [
    "* Enrolment as of (1st Friday of JULY)" => [$male_count, $female_count, $total_students],
    "Late enrolment during the month" => [0, 0, 0],
    "Registered Learners as of end of month" => [$male_count, $female_count, $total_students],
    "Percentage of Enrolment as of end of month" => [100, 100, 100],
    "Average Daily Attendance" => [
        round($male_count > 0 ? ($male_count * count($dates) - $male_total_absences) / ($male_count * count($dates)) : 0, 2),
        round($female_count > 0 ? ($female_count * count($dates) - $female_total_absences) / ($female_count * count($dates)) : 0, 2),
        round($total_students > 0 ? ($total_students * count($dates) - $combined_total_absences) / ($total_students * count($dates)) : 0, 2)
    ],
    "Percentage of Attendance for the month" => [
        round($male_count > 0 ? (($male_count * count($dates) - $male_total_absences) / ($male_count * count($dates))) * 100 : 0, 2),
        round($female_count > 0 ? (($female_count * count($dates) - $female_total_absences) / ($female_count * count($dates))) * 100 : 0, 2),
        round($total_students > 0 ? (($total_students * count($dates) - $combined_total_absences) / ($total_students * count($dates))) * 100 : 0, 2)
    ],
    "Number of students absent for 5 consecutive days" => [0, 0, 0],
];

foreach ($summaryData as $metric => $values) {
    $pdf->MultiCell(50, 5, $metric, 1, 'L');
    $y = $pdf->GetY() - 5;
    $pdf->SetXY($pdf->GetX() + 50, $y);
    $pdf->Cell(15, 5, $values[0], 1, 0, 'C');
    $pdf->Cell(15, 5, $values[1], 1, 0, 'C');
    $pdf->Cell(0, 5, $values[2], 1, 1, 'C');
}

// Log export before streaming
$latency_ms = (int)((microtime(true) - $request_start) * 1000);
$teacher_id = $_SESSION['user_id'] ?? null;
log_event($pdo, 'report_export', [
    'user_role' => 'teacher',
    'user_id' => $teacher_id,
    'object_type' => 'sf2_report',
    'latency_ms' => $latency_ms,
    'context_json' => [
        'report_type' => 'SF2_PDF',
        'month' => $month,
        'grade_level' => $grade_level,
        'strand' => $strand,
        'block' => $block,
        'teacher_name' => $teacher_name,
        'export_format' => 'pdf',
        'student_count' => count($students),
        'school_days' => count($dates),
    ],
]);

// Output PDF
$filename = 'SF2_' . date('Y-m', strtotime($month)) . '_' . str_replace(' ', '_', $strand) . '_' . str_replace(' ', '_', $block) . '.pdf';
$pdf->Output($filename, 'D');
exit;
?>
