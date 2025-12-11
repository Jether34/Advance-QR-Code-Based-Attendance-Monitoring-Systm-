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
                case 'present':
                case 'late':
                case 'morning_half_day':
                case 'afternoon_half_day':
                case 'excuse':
                    $marks[] = '';
                    break;
                case 'absent':
                    $marks[] = 'x';
                    break;
                default:
                    $marks[] = '';
                    break;
            }
        }
    }
    $attendance[$student['student_id']] = $marks;
}

// Count totals (absences)
$totals = [];
foreach ($attendance as $student_id => $marks) {
    $totals[$student_id] = array_sum(array_map(function($x){ return $x === 'x' ? 1 : 0; }, $marks));
}

// Separate by gender
$male_students = [];
$female_students = [];
foreach ($students as $s) {
    if (strtolower($s['gender']) === 'female') {
        $female_students[] = $s;
    } else {
        $male_students[] = $s;
    }
}
$male_count = count($male_students);
$female_count = count($female_students);
$total_students = count($students);

// Calculate daily totals
$male_daily_totals = array_fill(0, count($dates), 0);
$female_daily_totals = array_fill(0, count($dates), 0);
foreach ($male_students as $s) {
    foreach ($dates as $j => $d) {
        $male_daily_totals[$j] += ($attendance[$s['student_id']][$j] === 'x' ? 1 : 0);
    }
}
foreach ($female_students as $s) {
    foreach ($dates as $j => $d) {
        $female_daily_totals[$j] += ($attendance[$s['student_id']][$j] === 'x' ? 1 : 0);
    }
}
$combined_daily_totals = array_map(function($m, $f) { return $m + $f; }, $male_daily_totals, $female_daily_totals);

// Use TCPDF
require 'vendor/autoload.php';

class MYPDF extends \TCPDF {
    public function Header() {}
    public function Footer() {}
}

$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false); // Landscape
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(TRUE, 5);
$pdf->AddPage();

$pdf->SetFont('Courier', 'B', 9);

// Title
$pdf->Cell(0, 6, 'School Form 2 Daily Attendance Report of Learners For Senior High School (SF2-SHS)', 0, 1, 'C');

// Header info row 1
$pdf->SetFont('Courier', '', 8);
$pdf->Cell(25, 4, 'School Name:', 0, 0, 'L');
$pdf->Cell(45, 4, $school_name, 0, 0, 'L');
$pdf->Cell(20, 4, 'School ID:', 0, 0, 'L');
$pdf->Cell(25, 4, $school_id, 0, 0, 'L');
$pdf->Cell(20, 4, 'District:', 0, 0, 'L');
$pdf->Cell(0, 4, $district, 0, 1, 'L');

// Header info row 2
$pdf->Cell(25, 4, 'School Year:', 0, 0, 'L');
$pdf->Cell(45, 4, '2024-2025', 0, 0, 'L');
$pdf->Cell(20, 4, 'Semester:', 0, 0, 'L');
$pdf->Cell(25, 4, 'Second', 0, 0, 'L');
$pdf->Cell(20, 4, 'Division:', 0, 0, 'L');
$pdf->Cell(0, 4, $division, 0, 1, 'L');

// Header info row 3
$pdf->Cell(25, 4, 'Grade Level:', 0, 0, 'L');
$pdf->Cell(45, 4, $grade_level, 0, 0, 'L');
$pdf->Cell(20, 4, 'Strand:', 0, 0, 'L');
$pdf->Cell(25, 4, $strand, 0, 0, 'L');
$pdf->Cell(20, 4, 'Section:', 0, 0, 'L');
$pdf->Cell(0, 4, $block, 0, 1, 'L');

$pdf->Ln(2);

// Table header
$pdf->SetFont('Courier', 'B', 7);
$pdf->SetFillColor(200, 200, 200);

// Row: No. | Name | Dates... | Total | Tardy | Remarks
$col_width = 3.5; // for each date column
$pdf->Cell(4, 4, 'No.', 1, 0, 'C', true);
$pdf->Cell(25, 4, 'Name', 1, 0, 'L', true);

// Date columns - show date numbers only
foreach ($dates as $d) {
    $day = date('d', strtotime($d));
    $pdf->Cell($col_width, 4, $day, 1, 0, 'C', true);
}

$pdf->Cell(8, 4, 'Absent', 1, 0, 'C', true);
$pdf->Cell(15, 4, 'Tardy', 1, 0, 'C', true);
$pdf->Cell(0, 4, 'Remarks', 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('Courier', '', 7);

// Male students
$male_num = 0;
foreach ($male_students as $student) {
    $male_num++;
    $pdf->Cell(4, 3.5, $male_num, 1, 0, 'C');
    $pdf->Cell(25, 3.5, substr($student['name'], 0, 20), 1, 0, 'L');
    foreach ($dates as $j => $d) {
        $mark = $attendance[$student['student_id']][$j] ?? '';
        $pdf->Cell($col_width, 3.5, $mark, 1, 0, 'C');
    }
    $pdf->Cell(8, 3.5, $totals[$student['student_id']], 1, 0, 'C');
    $pdf->Cell(15, 3.5, '', 1, 0, 'C');
    $pdf->Cell(0, 3.5, '', 1, 1, 'C');
}

// Male daily total
$pdf->SetFont('Courier', 'B', 7);
$pdf->Cell(4, 3.5, '', 1, 0, 'C');
$pdf->Cell(25, 3.5, '<== MALE | TOTAL Per Day ==>', 1, 0, 'L');
foreach ($male_daily_totals as $total) {
    $pdf->Cell($col_width, 3.5, $total, 1, 0, 'C');
}
$male_total_abs = array_sum($male_daily_totals);
$pdf->Cell(8, 3.5, $male_total_abs, 1, 0, 'C');
$pdf->Cell(15, 3.5, '', 1, 0, 'C');
$pdf->Cell(0, 3.5, '', 1, 1, 'C');

$pdf->Ln(2);

// Female students
$pdf->SetFont('Courier', '', 7);
$female_num = 0;
foreach ($female_students as $student) {
    $female_num++;
    $pdf->Cell(4, 3.5, $female_num, 1, 0, 'C');
    $pdf->Cell(25, 3.5, substr($student['name'], 0, 20), 1, 0, 'L');
    foreach ($dates as $j => $d) {
        $mark = $attendance[$student['student_id']][$j] ?? '';
        $pdf->Cell($col_width, 3.5, $mark, 1, 0, 'C');
    }
    $pdf->Cell(8, 3.5, $totals[$student['student_id']], 1, 0, 'C');
    $pdf->Cell(15, 3.5, '', 1, 0, 'C');
    $pdf->Cell(0, 3.5, '', 1, 1, 'C');
}

// Female daily total
$pdf->SetFont('Courier', 'B', 7);
$pdf->Cell(4, 3.5, '', 1, 0, 'C');
$pdf->Cell(25, 3.5, '<== FEMALE | TOTAL Per Day ==>', 1, 0, 'L');
foreach ($female_daily_totals as $total) {
    $pdf->Cell($col_width, 3.5, $total, 1, 0, 'C');
}
$female_total_abs = array_sum($female_daily_totals);
$pdf->Cell(8, 3.5, $female_total_abs, 1, 0, 'C');
$pdf->Cell(15, 3.5, '', 1, 0, 'C');
$pdf->Cell(0, 3.5, '', 1, 1, 'C');

// Combined daily total
$pdf->SetFont('Courier', 'B', 7);
$pdf->Cell(4, 3.5, '', 1, 0, 'C');
$pdf->Cell(25, 3.5, 'Combined TOTAL Per Day', 1, 0, 'L');
foreach ($combined_daily_totals as $total) {
    $pdf->Cell($col_width, 3.5, $total, 1, 0, 'C');
}
$combined_total_abs = array_sum($combined_daily_totals);
$pdf->Cell(8, 3.5, $combined_total_abs, 1, 0, 'C');
$pdf->Cell(15, 3.5, '', 1, 0, 'C');
$pdf->Cell(0, 3.5, '', 1, 1, 'C');

// Summary section
$pdf->Ln(5);
$pdf->SetFont('Courier', 'B', 9);
$pdf->Cell(0, 5, 'SUMMARY', 0, 1, 'C');

$pdf->SetFont('Courier', 'B', 7);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(50, 4, 'Metric', 1, 0, 'L', true);
$pdf->Cell(25, 4, 'M', 1, 0, 'C', true);
$pdf->Cell(25, 4, 'F', 1, 0, 'C', true);
$pdf->Cell(0, 4, 'Total', 1, 1, 'C', true);
$pdf->SetFillColor(255, 255, 255);

$pdf->SetFont('Courier', '', 7);

// Summary data
$summaryRows = [
    ['* Enrolment as of (1st Friday of JULY)', $male_count, $female_count, $total_students],
    ['Late enrolment during the month', 0, 0, 0],
    ['Registered Learners as of end of month', $male_count, $female_count, $total_students],
    ['Percentage of Enrolment as of end of month', 100, 100, 100],
    ['Average Daily Attendance',
        round($male_count > 0 ? 1 - ($male_total_abs / ($male_count * count($dates))) : 0, 2),
        round($female_count > 0 ? 1 - ($female_total_abs / ($female_count * count($dates))) : 0, 2),
        round($total_students > 0 ? 1 - (($male_total_abs + $female_total_abs) / ($total_students * count($dates))) : 0, 2)
    ],
    ['Percentage of Attendance for the month',
        round($male_count > 0 ? (1 - ($male_total_abs / ($male_count * count($dates)))) * 100 : 0, 2),
        round($female_count > 0 ? (1 - ($female_total_abs / ($female_count * count($dates)))) * 100 : 0, 2),
        round($total_students > 0 ? (1 - (($male_total_abs + $female_total_abs) / ($total_students * count($dates)))) * 100 : 0, 2)
    ],
    ['Number of students absent for 5 consecutive days', 0, 0, 0],
];

foreach ($summaryRows as $row) {
    $pdf->Cell(50, 4, substr($row[0], 0, 45), 1, 0, 'L');
    $pdf->Cell(25, 4, $row[1], 1, 0, 'C');
    $pdf->Cell(25, 4, $row[2], 1, 0, 'C');
    $pdf->Cell(0, 4, $row[3], 1, 1, 'C');
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
