<?php
require_once 'db.php';
require_once 'logging.php';

$pdo = get_db();
require_once __DIR__ . '/bootstrap.php';

$request_start = microtime(true);

// Get logged-in teacher info
$teacher_id = $_SESSION['user_id'] ?? null;
$teacher_name = '';
if ($teacher_id) {
    $stmt = $pdo->prepare('SELECT full_name FROM teachers WHERE id = ?');
    $stmt->execute([$teacher_id]);
    $teacher_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $teacher_name = $teacher_row['full_name'] ?? 'Adviser';
}

// Get filters from query string or session
$strand = isset($_GET['strand']) ? trim($_GET['strand']) : ($_SESSION['strand'] ?? '');
$block = isset($_GET['block']) ? trim($_GET['block']) : ($_SESSION['section_block'] ?? '');
$grade_level = isset($_GET['grade_level']) ? trim($_GET['grade_level']) : ($_SESSION['grade_level'] ?? '');

// Get teacher name from parameter or session
$teacher_name = isset($_GET['teacher_name']) ? trim($_GET['teacher_name']) : $teacher_name;
if (!$teacher_name) {
    $teacher_name = $_SESSION['full_name'] ?? 'Adviser';
}

// Month defaults to current month if not provided
$month = $_GET['month'] ?? date('Y-m');

// DEBUG: Log the month being used
error_log("SF2 Export - Month parameter: " . $month);
error_log("SF2 Export - Current date: " . date('Y-m-d'));

// School info
$school_name = 'PALAWAN NATIONAL SCHOOL';
$school_id = '303101';
$district = 'Puerto Princesa I';
$division = 'PUERTO PRINCESA';
$region = 'IV-B';
$section = $block;
$course = $strand;

// Fetch students - ordered by gender then alphabetically, all caps
$query = "SELECT s.student_id,
                 UPPER(s.full_name) AS name,
                 s.strand,
                 s.section_block AS block,
                 s.grade_level,
                 s.gender
          FROM students s
          WHERE (? = '' OR s.grade_level = ?)
            AND (? = '' OR s.strand = ?)
            AND (? = '' OR s.section_block = ?)
          ORDER BY
            CASE WHEN LOWER(s.gender) = 'female' THEN 1 ELSE 0 END,
            s.full_name ASC";
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

// Get attendance - mark based on status: blank for present, X for absent, / for half-day
$attendance = [];
foreach ($students as $student) {
    $marks = [];
    foreach ($dates as $d) {
        $stmt2 = $pdo->prepare("SELECT status FROM attendance_records WHERE student_id = ? AND attendance_date = ?");
        $stmt2->execute([$student['student_id'], $d]);
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

        if (!$row2) {
            // No record = Absent
            $marks[] = 'X';
        } else {
            $status = $row2['status'] ?? '';
            switch ($status) {
                case 'present':
                case 'late':
                case 'excuse':
                    // Treat as present
                    $marks[] = '';
                    break;
                case 'morning_half_day':
                case 'afternoon_half_day':
                    // Half-day
                    $marks[] = '/';
                    break;
                case 'absent':
                default:
                    // Absent
                    $marks[] = 'X';
                    break;
            }
        }
    }
    $attendance[$student['student_id']] = $marks;
}

// Count totals (absences only)
$totals = [];
foreach ($attendance as $student_id => $marks) {
    $totals[$student_id] = array_sum(array_map(function($x){ return $x === 'X' ? 1 : 0; }, $marks));
}

// Calculate daily totals by gender - count absences only
$male_students = [];
$female_students = [];
foreach ($students as $s) {
    if (strtolower($s['gender']) === 'female') {
        $female_students[] = $s;
    } else {
        $male_students[] = $s;
    }
}

$male_daily = array_fill(0, count($dates), 0);
$female_daily = array_fill(0, count($dates), 0);

foreach ($male_students as $s) {
    foreach ($dates as $j => $d) {
        $male_daily[$j] += ($attendance[$s['student_id']][$j] === 'x' ? 1 : 0);
    }
}

foreach ($female_students as $s) {
    foreach ($dates as $j => $d) {
        $female_daily[$j] += ($attendance[$s['student_id']][$j] === 'x' ? 1 : 0);
    }
}

$combined_daily = array_map(function($m, $f) { return $m + $f; }, $male_daily, $female_daily);

// Use PhpSpreadsheet template fill
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Format: SF2_MONTH_STRAND_BLOCK.xlsx (e.g., SF2_DECEMBER_ICT_3.xlsx)
$month_name_file = strtoupper(date('F', strtotime($month . '-01')));
$output_filename = 'SF2_' . $month_name_file . '_' . str_replace(' ', '_', $strand) . '_' . str_replace(' ', '_', $block) . '.xlsx';

// Load template (converted XLSX)
$templatePath = 'Sf excel/SF2 Format.xls'; // Use original XLS to preserve formulas
$spreadsheet = IOFactory::load($templatePath);
$sheet = $spreadsheet->getActiveSheet();

// Clear only student NAME cells and attendance marks, NOT formulas
// Male section: rows 18-45
for ($r = 18; $r <= 45; $r++) {
    $sheet->getCell('G' . $r)->setValue('');
    $sheet->getCell('A' . $r)->setValue('');
    for ($c = 10; $c <= 53; $c++) { // J to BA
        $col = Coordinate::stringFromColumnIndex($c);
        $sheet->getCell($col . $r)->setValue('');
    }
}
// Female section: rows 47-55
for ($r = 47; $r <= 55; $r++) {
    $sheet->getCell('G' . $r)->setValue('');
    $sheet->getCell('A' . $r)->setValue('');
    for ($c = 10; $c <= 53; $c++) {
        $col = Coordinate::stringFromColumnIndex($c);
        $sheet->getCell($col . $r)->setValue('');
    }
}
// DON'T TOUCH rows 46, 56, 57 - they have COUNTIF formulas that auto-calculate daily absence totals
// Row 46: Male total per day (counts X's in rows 18-45)
// Row 56: Female total per day (counts X's in rows 47-52)
// Row 57: Combined total per day (sums row 46 + row 56)

// Set header info - following exact SF2 format structure
$sheet->setCellValue('B5', 'School Name');
$sheet->setCellValue('H5', $school_name);
$sheet->setCellValue('Q5', 'School ID');
$sheet->setCellValue('X5', $school_id);

// School Year
$sheet->setCellValue('M6', 'School Year');
$sheet->setCellValue('T8', '2024-2025');

// Semester
$sheet->setCellValue('D7', 'Semester');
$sheet->setCellValue('H7', 'Second Semester');

// Grade - Auto-set based on teacher's grade
$sheet->setCellValue('B6', 'Grade');
$sheet->setCellValue('H6', $grade_level);

// Track and Strand - Auto-set based on teacher's faculty and strand
$sheet->setCellValue('B8', 'Track and Strand');
// Get teacher's faculty/track from session or database
$faculty = $_SESSION['faculty'] ?? '';
if (!$faculty && $teacher_id) {
    $stmt_faculty = $pdo->prepare('SELECT faculty FROM teachers WHERE id = ?');
    $stmt_faculty->execute([$teacher_id]);
    $faculty_row = $stmt_faculty->fetch(PDO::FETCH_ASSOC);
    $faculty = $faculty_row['faculty'] ?? '';
}
$track_strand = ($faculty ? $faculty . ' - ' : '') . $strand;
$sheet->setCellValue('H8', $track_strand);

// Month of - Auto-set based on export month
$month_name = strtoupper(date('F', strtotime($month . '-01')));
error_log("SF2 Export - Month name: " . $month_name); // DEBUG
// Set month in BN11 (that's where template shows it)
$sheet->setCellValue('BN11', $month_name);
// Also set in BI58 format "Month : MONTHNAME"
$sheet->setCellValue('BI58', 'Month : ' . ucfirst(strtolower($month_name)));

// Section - Auto-set teacher's strand and block
$sheet->setCellValue('E12', 'Section');
$section_value = $strand . ' - ' . $block;
$sheet->setCellValue('H12', $section_value);

// Course (for TVL only) - Only show if strand contains TVL
$sheet->setCellValue('R10', 'Course (for TVL only)');
if (stripos($strand, 'TVL') !== false) {
    // Extract course from strand (e.g., "TVL - ICT" -> "ICT")
    $course_parts = explode('-', $strand);
    $course = isset($course_parts[1]) ? trim($course_parts[1]) : '';
    $sheet->setCellValue('W10', $course);
} else {
    $sheet->setCellValue('W10', '');
}

// Set adviser name and signature area - will be set later at the bottom

// First day column index
$startRow = 18;
$nameCol = 'G'; // Names in column G
$firstDayColIndex = Coordinate::columnIndexFromString('J'); // Dates start at J

// Separate students by gender
$maleStudents = [];
$femaleStudents = [];
foreach ($students as $student) {
    if (strtoupper($student['gender']) === 'MALE' || strtoupper($student['gender']) === 'M') {
        $maleStudents[] = $student;
    } else {
        $femaleStudents[] = $student;
    }
}

// Write MALE students to rows 18-45
$row = 18;
$studentNum = 1;
$lastMaleRow = 17; // Track the last row with a male student
foreach ($maleStudents as $student) {
    if ($row > 45) break; // Don't exceed male section

    $sheet->setCellValue('A' . $row, $studentNum);
    $sheet->setCellValue($nameCol . $row, $student['name']);
    $lastMaleRow = $row; // Update last male row

    // Write attendance marks (X for absent, / for half-day, blank for present)
    foreach ($dates as $j => $d) {
        $colIndex = $firstDayColIndex + $j;
        $col = Coordinate::stringFromColumnIndex($colIndex);
        $mark = $attendance[$student['student_id']][$j] ?? 'X';
        // Write the mark as-is (X, /, or blank)
        $sheet->setCellValue($col . $row, $mark);
    }

    $row++;
    $studentNum++;
}

// Write FEMALE students to rows 47-55
$row = 47;
$studentNum = 1;
$lastFemaleRow = 46; // Track the last row with a female student
foreach ($femaleStudents as $student) {
    if ($row > 55) break; // Don't exceed female section

    $sheet->setCellValue('A' . $row, $studentNum);
    $sheet->setCellValue($nameCol . $row, $student['name']);
    $lastFemaleRow = $row; // Update last female row

    // Write attendance marks (X for absent, / for half-day, blank for present)
    foreach ($dates as $j => $d) {
        $colIndex = $firstDayColIndex + $j;
        $col = Coordinate::stringFromColumnIndex($colIndex);
        $mark = $attendance[$student['student_id']][$j] ?? 'X';
        // Write the mark as-is (X, /, or blank)
        $sheet->setCellValue($col . $row, $mark);
    }

    $row++;
    $studentNum++;
}

// Update formulas in total rows to only count actual students
// Row 46: Male total (update formulas to count only rows with students)
for ($c = 10; $c <= 53; $c++) { // J to BA
    $col = Coordinate::stringFromColumnIndex($c);
    $sheet->setCellValue($col . '46', '=COUNTIF(' . $col . '18:' . $col . $lastMaleRow . ',"X")');
}

// Row 56: Female total (update formulas to count only rows with students)
for ($c = 10; $c <= 53; $c++) { // J to BA
    $col = Coordinate::stringFromColumnIndex($c);
    $sheet->setCellValue($col . '56', '=COUNTIF(' . $col . '47:' . $col . $lastFemaleRow . ',"X")');
}

// Row 57: Combined total (sum of male and female totals)
for ($c = 10; $c <= 53; $c++) { // J to BA
    $col = Coordinate::stringFromColumnIndex($c);
    $sheet->setCellValue($col . '57', '=' . $col . '46+' . $col . '56');
}

// Daily totals rows - formulas will auto-calculate, just clear old data
// Don't write daily totals manually - the template formulas (rows 46, 56, 57) will calculate them

// Summary counts - write to BU60, BW60 (formulas in other cells will calculate totals)
$male_count = count($maleStudents);
$female_count = count($femaleStudents);
$total_students = count($students);
$sheet->setCellValue('BU60', $male_count);   // Male enrollment
$sheet->setCellValue('BW60', $female_count); // Female enrollment
// BX60 has formula =SUM(BU60:BW62) so it will auto-calculate

$sheet->setCellValue('BU68', $male_count);   // Registered males
$sheet->setCellValue('BW68', $female_count); // Registered females
// BX68 has formula too

// Signature section - Signature of Adviser over Printed Name
// Template has this in BJ93-BK95 area
$sheet->setCellValue('BJ93', strtoupper($teacher_name)); // Replace JENNY C. COLO
// Note: "I certify..." text already in BI91 and (Signature...) in BK95 in template

// Log export before streaming so DB capture is not skipped
$latency_ms = (int)((microtime(true) - $request_start) * 1000);
$teacher_id = $_SESSION['user_id'] ?? null;
log_event($pdo, 'report_export', [
    'user_role' => 'teacher',
    'user_id' => $teacher_id,
    'object_type' => 'sf2_report',
    'latency_ms' => $latency_ms,
    'context_json' => [
        'report_type' => 'SF2',
        'month' => $month,
        'grade_level' => $grade_level,
        'strand' => $strand,
        'block' => $block,
        'teacher_name' => $teacher_name,
        'export_format' => 'excel',
        'student_count' => count($students),
        'school_days' => count($dates),
    ],
]);

// Stream output
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $output_filename . '"');
header('Cache-Control: max-age=0');

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
exit;
?>
