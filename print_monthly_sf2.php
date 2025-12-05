<?php
require_once 'db.php';
require_once 'logging.php';
$pdo = get_db();
session_start();

$request_start = microtime(true);

// Accept filters from query string with safe fallbacks to session (when present)
$strand = isset($_GET['strand']) ? trim($_GET['strand']) : ($_SESSION['strand'] ?? '');
$block = isset($_GET['block']) ? trim($_GET['block']) : ($_SESSION['block'] ?? '');
$grade_level = isset($_GET['grade_level']) ? trim($_GET['grade_level']) : ($_SESSION['grade_level'] ?? '');
$teacher_name = isset($_GET['teacher_name']) ? trim($_GET['teacher_name']) : ($_SESSION['teacher_name'] ?? 'Adviser');

// Get month from GET or default to current
$month = $_GET['month'] ?? date('Y-m');

// School info (customize as needed or fetch from DB)
$school_name = 'PALAWAN NATIONAL SCHOOL';
$school_id = '303101';
$district = 'Puerto Princesa I';
$division = 'PUERTO PRINCESA';
$region = 'IV-B';
$section = $block;
$course = $strand;

// Fetch only students matching the teacher's advisory class (grade, strand, section/block)
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
$stmt->execute([
    $grade_level,
    $grade_level,
    $strand,
    $strand,
    $block,
    $block,
]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all school days in the month (Monday-Friday)
$start = date('Y-m-01', strtotime($month));
$end = date('Y-m-t', strtotime($month));
$dates = [];
$period = new DatePeriod(new DateTime($start), new DateInterval('P1D'), (new DateTime($end))->modify('+1 day'));
foreach ($period as $dt) {
    if (in_array($dt->format('N'), [1,2,3,4,5])) $dates[] = $dt->format('Y-m-d');
}

// For each student, get attendance per day
$attendance = [];
foreach ($students as $student) {
    $marks = [];
    foreach ($dates as $d) {
        $stmt2 = $pdo->prepare("SELECT status FROM attendance_records WHERE student_id = ? AND attendance_date = ?");
        $stmt2->execute([$student['student_id'], $d]);
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        if (!$row2) {
            $marks[] = 'A'; // Absent
        } else {
            switch ($row2['status']) {
                case 'present':
                    $marks[] = 'P';
                    break;
                case 'late':
                    $marks[] = 'P'; // Treat late as present
                    break;
                case 'morning_half_day':
                case 'afternoon_half_day':
                    $marks[] = '/';
                    break;
                case 'excuse':
                    $marks[] = 'x';
                    break;
                case 'absent':
                    $marks[] = 'A';
                    break;
                default:
                    $marks[] = $row2['status'];
                    break;
            }
        }
    }
    $attendance[$student['student_id']] = $marks;
}

// Count total absences per student
$totals = [];
foreach ($attendance as $student_id => $marks) {
    $totals[$student_id] = array_sum(array_map(function($x){ return $x === 'A' ? 1 : 0; }, $marks));
}

// Calculate daily totals by gender
$female_daily = array_fill(0, count($dates), 0);
$combined_daily = array_fill(0, count($dates), 0);
foreach ($students as $idx => $student) {
    foreach ($dates as $j => $d) {
        if (isset($attendance[$student['student_id']][$j]) && ($attendance[$student['student_id']][$j] === 'P' || $attendance[$student['student_id']][$j] === '/' || $attendance[$student['student_id']][$j] === 'x')) {
            if (isset($student['gender']) && strtolower($student['gender']) === 'female') {
                $female_daily[$j]++;
            }
            $combined_daily[$j]++;
        }
    }
}

// Output SF2 HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>SF2 Monthly Attendance - <?php echo htmlspecialchars($month); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 5px; }
        .sf2-header-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0; }
        .sf2-logo { height: 55px; }
        .sf2-title-block { flex: 1; text-align: center; }
        .sf2-title-main { font-size: 1em; font-weight: bold; }
        .sf2-title-sub { font-size: 0.9em; }
        .sf2-header-table { width: 100%; border: none; margin-bottom: 0; font-size: 9px; }
        .sf2-header-table td { border: none; padding: 0 2px; }
        .sf2-header-label { font-weight: bold; }
        .sf2-thick-top { border-top: 3px solid #000 !important; }
        .sf2-thick-bottom { border-bottom: 3px solid #000 !important; }
        .sf2-bold { font-weight: bold; }
        .sf2-total-row { background: #f5f5f5; font-weight: bold; }
        .sf2-total-label { text-align: right; font-size: 8px; }
        .sf2-total-cell { background: #fff; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; font-size: 8px; }
        th, td { border: 1px solid #000; padding: 1px; text-align: center; }
        th { background: #e0e0e0; }
        .absent { background: #ffcdd2; color: #c62828; }
        .remarks-col { min-width: 70px; }
        .signature-line { border-bottom: 1.5px solid #222; width: 180px; margin: 0 auto 2px auto; height: 10px; }
        .generated-line { border-bottom: 1.5px solid #222; width: 180px; margin: 0 auto 2px auto; height: 10px; }
        @media print {
            body { margin: 0; }
            .sf2-header-row { margin-top: 0; }
            .sf2-title-main { font-size: 0.95em; }
            .sf2-title-sub { font-size: 0.85em; }
            table { font-size: 7px; }
            .sf2-header-table { font-size: 8px; }
        }
    </style>
</head>
<body>
    <div class="sf2-header-row" style="margin-bottom: 0;">
        <img src="src/KAGAWARAN NG EDUKASYON DEPED.png" alt="Kagawaran ng Edukasyon" class="sf2-logo" style="margin-right:18px;">
        <div class="sf2-header-center">
            <div class="sf2-title-main" style="font-size:11px; font-weight:bold; margin-bottom:8px; letter-spacing:0.5px;">School Form 2 Daily Attendance Report of Learners For Senior High School (SF2-SHS)</div>
            <div class="sf2-header-info-row">
                <span class="sf2-header-label">School Name:</span> <span class="sf2-header-value"><?php echo $school_name; ?></span>
                <span class="sf2-header-label">School ID:</span> <span class="sf2-header-value"><?php echo $school_id; ?></span>
                <span class="sf2-header-label">District:</span> <span class="sf2-header-value"><?php echo $district; ?></span>
                <span class="sf2-header-label">Division:</span> <span class="sf2-header-value"><?php echo $division; ?></span>
                <span class="sf2-header-label">Region:</span> <span class="sf2-header-value"><?php echo $region; ?></span>
            </div>
            <div class="sf2-header-info-row">
                <span class="sf2-header-label">Semester:</span> <span class="sf2-header-value">Second Semester</span>
                <span class="sf2-header-label">School Year:</span> <span class="sf2-header-value">2024-2025</span>
                <span class="sf2-header-label">Grade Level:</span> <span class="sf2-header-value"><?php echo $grade_level; ?></span>
                <span class="sf2-header-label">Track and Strand:</span> <span class="sf2-header-value"><?php echo $strand; ?></span>
            </div>
            <div class="sf2-header-info-row">
                <span class="sf2-header-label">Section:</span> <span class="sf2-header-value"><?php echo $section; ?></span>
                <span class="sf2-header-label">Course (for TVL only):</span> <span class="sf2-header-value"><?php echo $course; ?></span>
                <span class="sf2-header-label">Month of:</span> <span class="sf2-header-value" style="font-weight:bold;"><?php echo strtoupper(date('F', strtotime($month))); ?></span>
            </div>
        </div>
        <img src="src/n1142928.png" alt="DepEd" class="sf2-logo" style="margin-left:18px;">
    </div>
    <style>
        .sf2-header-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 0;
        }
        .sf2-header-center {
            flex: 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sf2-header-info-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 18px 12px;
            margin-bottom: 2px;
            font-size: 9px;
        }
        .sf2-header-label {
            font-weight: bold;
            margin-right: 2px;
            font-size: 9px;
        }
        .sf2-header-value {
            margin-right: 12px;
            font-weight: normal;
            min-width: 40px;
            display: inline-block;
            font-size: 9px;
        }
        @media print {
            .sf2-header-row { margin-top: 0; }
            .sf2-title-main { font-size: 1.05em; }
            .sf2-header-info-row { font-size: 0.92em; }
        }
    </style>

    <table>
        <thead>
            <tr class="sf2-thick-top">
                <th rowspan="2" style="width:18px;">No.</th>
                <th rowspan="2" style="min-width:160px;">NAME<br><span style="font-weight:normal;">(Last Name, First Name, Middle Name)</span></th>
<?php foreach ($dates as $d): ?>
                <th style="width:18px;"><?php echo date('j', strtotime($d)); ?></th>
<?php endforeach; ?>
                <th rowspan="2" style="width:32px;">Total for the Month<br>ABSENT</th>
                <th rowspan="2" style="width:32px;">TARDY</th>
                <th rowspan="2" class="remarks-col">REMARKS</th>
            </tr>
            <tr>
<?php foreach ($dates as $d): ?>
                <th style="width:18px;"><?php echo strtoupper(date('D', strtotime($d))); ?></th>
<?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
<?php foreach ($students as $i => $student): ?>
            <tr>
                <td><?php echo $i+1; ?></td>
                <td style="text-align:left;"><span style="font-size:10px;"><?php echo htmlspecialchars($student['name']); ?></span></td>
<?php foreach ($attendance[$student['student_id']] as $a): ?>
                <td class="<?php echo $a === 'X' ? 'absent' : ''; ?>"><?php echo $a; ?></td>
<?php endforeach; ?>
                <td><?php echo $totals[$student['student_id']]; ?></td>
                <td></td>
                <td></td>
            </tr>
<?php endforeach; ?>
            <!-- FEMALE | TOTAL Per Day row (dynamic) -->
            <tr class="sf2-total-row">
                <td colspan="2" class="sf2-total-label">&lt;=== FEMALE | TOTAL Per Day ===&gt;</td>
<?php foreach ($female_daily as $val): ?>
                <td class="sf2-total-cell"><?php echo $val; ?></td>
<?php endforeach; ?>
                <td class="sf2-total-cell"></td>
                <td class="sf2-total-cell"></td>
                <td class="sf2-total-cell"></td>
            </tr>
            <!-- Combined TOTAL Per Day row (dynamic) -->
            <tr class="sf2-total-row sf2-thick-bottom">
                <td colspan="2" class="sf2-total-label">Combined TOTAL Per Day</td>
<?php foreach ($combined_daily as $val): ?>
                <td class="sf2-total-cell"><?php echo $val; ?></td>
<?php endforeach; ?>
                <td class="sf2-total-cell"></td>
                <td class="sf2-total-cell"></td>
                <td class="sf2-total-cell"></td>
            </tr>
        </tbody>
    </table>
    <br>


            <br><br>
            <!-- SF2 Footer Section: 3 columns -->
            <table style="width:100%; margin-top:10px; font-size:11px; border:none;">
                <tr>
                    <!-- Guidelines Left -->
                    <td style="vertical-align:top; width:38%; border:none; padding-right:10px;">
                        <div style="margin-left:30px; text-align:left;">
                            <b>GUIDELINES:</b><br>
                            1. The attendance shall be accomplished daily. Refer to the codes for checking learners' attendance.<br>
                            2. Dates shall be written in the columns after Learner's Name.<br>
                            3. To compute the following:<br>
                            <div style="margin-left:20px;">
                                a. Percentage of Enrolment = <br>
                                <span style="margin-left:20px;">Registered Learners as of end of the month</span><br>
                                <span style="margin-left:20px;">Enrolment as of 1st Friday of the school year</span> x 100<br>
                                b. Average Daily Attendance = <br>
                                <span style="margin-left:20px;">Total Daily Attendance</span><br>
                                <span style="margin-left:20px;">Number of School Days in reporting month</span><br>
                                c. Percentage of Attendance for the month = <br>
                                <span style="margin-left:20px;">Average daily attendance</span><br>
                                <span style="margin-left:20px;">Registered Learners as of end of the month</span> x 100<br>
                            </div>
                            4. Every end of the month, the class adviser will submit this form to the office of the principal for recording of summary table into School Form 4. Once signed by the principal, this form should be returned to the adviser.<br>
                            5. The adviser will provide necessary interventions including but not limited to home visitation to learner/s who were absent for 5 consecutive days and/or those at risk of dropping out.<br>
                            6. Attendance performance of learners will be reflected in Form 137 and Form 138 every grading period.<br>
                        </div>
                    </td>
                    <!-- Codes/Reasons Center -->
                    <td style="vertical-align:top; width:32%; border:none; padding:0 10px;">
                        <div style="margin-left:30px; text-align:left;">
                            <b>1. CODES FOR CHECKING ATTENDANCE</b><br>
                            (blank) - Present; (X)- Absent; Tardy (half shaded= Upper for Late Commer, Lower for Cutting Classes)<br>
                            <br>
                            <b>2. REASONS/CAUSES FOR DROPPING OUT</b><br>
                            <b>a. Domestic-Related Factors</b><br>
                            a.1. Had to take care of siblings<br>
                            a.2. Early marriage/pregnancy<br>
                            a.3. Parents' attitude toward schooling<br>
                            a.4. Family problems<br>
                            <b>b. Individual-Related Factors</b><br>
                            b.1. Illness<br>
                            b.2. Overaged<br>
                            b.3. Death<br>
                            b.4. Drug Abuse<br>
                            b.5. Poor academic performance<br>
                            b.6. Lack of interest/Distractions<br>
                            b.7. Hunger/Malnutrition<br>
                            <b>c. School-Related Factors</b><br>
                            c.1. Teacher Factor<br>
                            c.2. Physical condition of classroom<br>
                            c.3. Peer influence<br>
                            <b>d. Geographic/Environmental</b><br>
                            d.1. Distance between home and school<br>
                            d.2. Armed conflict (incl. Tribal wars & clan feuds)<br>
                            d.3. Calamities/Disasters<br>
                            <b>e. Financial-Related</b><br>
                            e.1. Child labor, work<br>
                            <b>f. Others (Specify)</b>
                        </div>
                    </td>
                    <!-- Summary Table Right -->
                    <td style="vertical-align:top; width:30%; border:none;">
                        <table style="width:100%; font-size:11px; border:1px solid #333;">
                            <tr><th colspan="4">Month : <?php echo strtoupper(date('F', strtotime($month))); ?> <span style="float:right;">No. of Days of Classes: 21</span></th></tr>
                            <tr><th></th><th>M</th><th>F</th><th>TOTAL</th></tr>
                            <tr><td>* Enrolment as of<br>(1st Friday of JULY)</td><td>28</td><td>6</td><td>34</td></tr>
                            <tr><td>Late enrolment<br>during the month<br>(beyond cut-off)</td><td>0</td><td>0</td><td>0</td></tr>
                            <tr><td>Registered Learners as of<br>end of month</td><td>28</td><td>6</td><td>34</td></tr>
                            <tr><td>Percentage of Enrolment as of<br>end of month</td><td>100</td><td>100</td><td>100</td></tr>
                            <tr><td>Average Daily Attendance</td><td>0.1</td><td>0.1</td><td>0.2857</td></tr>
                            <tr><td>Percentage of Attendance for the month</td><td>0.5</td><td>2.4</td><td>1.4456</td></tr>
                            <tr><td>Number of students absent for 5 consecutive days</td><td>0</td><td>0</td><td>0</td></tr>
                            <tr><td>No Longer In School (NLS)</td><td>0</td><td>0</td><td>0</td></tr>
                            <tr><td>Transferred Out</td><td>0</td><td>0</td><td>0</td></tr>
                            <tr><td>Transferred In</td><td>0</td><td>0</td><td>0</td></tr>
                            <tr><td>Shifted Out</td><td>0</td><td>0</td><td>0</td></tr>
                            <tr><td>Shifted In</td><td>0</td><td>0</td><td>0</td></tr>
                        </table>
                        <div style="font-size:9px; margin-top:2px;">I certify that this is a true and correct report.</div>
                        <div style="margin-top:8px; text-align:left;">
                            <div class="signature-line"></div>
                            <b>JENNY C. COLO</b><br>
                            <span style="font-size:9px;">(Signature of Adviser over Printed Name)</span><br>
                            <b>Attested by:</b><br>
                            <div class="signature-line"></div>
                            <b>JABEL ANTHONY L. NUNALA, PhD</b><br>
                            <span style="font-size:9px;">(Signature of School Head over Printed Name)</span>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="generated-line"></div>
            <div style="text-align:center; margin-top:2px; font-size:9px;">Generated thru LIS</div>

            <button onclick="window.print()" style="padding:8px 16px;background:#1db954;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Print SF2</button>
</body>
</html>
<?php
// Log report export after render
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
        'export_format' => 'html',
        'student_count' => count($students),
        'school_days' => count($dates),
    ],
]);
?>
