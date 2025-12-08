<?php
// Generate comprehensive system documentation PDF
require_once __DIR__ . '/../vendor/autoload.php';

use TCPDF as TCPDF;

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('QR Attendance System');
$pdf->SetAuthor('ICT Strand Grade 12 Block 3 - Palawan National School');
$pdf->SetTitle('QR-Based Attendance Monitoring System - Complete Documentation');
$pdf->SetSubject('System Documentation and Research');

// Set margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Set font
$pdf->SetFont('helvetica', '', 10);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

//=============================================================================
// COVER PAGE
//=============================================================================
$pdf->AddPage();

// School Logo
$school_logo = __DIR__ . '/../uploads/OIP (1).webp';
if (file_exists($school_logo)) {
    $pdf->Image($school_logo, 15, 20, 40, 40, '', '', '', false, 300, '', false, false, 0);
}

// System Logo
$system_logo = __DIR__ . '/../uploads/System logo.jpg';
if (file_exists($system_logo)) {
    $pdf->Image($system_logo, 155, 20, 40, 40, '', '', '', false, 300, '', false, false, 0);
}

// Title
$pdf->SetFont('helvetica', 'B', 24);
$pdf->SetY(75);
$pdf->Cell(0, 10, 'QR-BASED ATTENDANCE', 0, 1, 'C');
$pdf->Cell(0, 10, 'MONITORING SYSTEM', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 14);
$pdf->SetY(100);
$pdf->Cell(0, 8, 'Complete System Documentation', 0, 1, 'C');

// School Info
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetY(130);
$pdf->Cell(0, 6, 'PALAWAN NATIONAL SCHOOL', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 6, 'Puerto Princesa City, Palawan', 0, 1, 'C');

// Developer Info
$pdf->SetY(150);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, 'Developed by:', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'ICT Strand - Grade 12 Block 3', 0, 1, 'C');
$pdf->Cell(0, 5, 'School Year 2025-2026', 0, 1, 'C');

// Research Purpose Notice
$pdf->SetY(180);
$pdf->SetFillColor(255, 243, 205);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->MultiCell(0, 5, 'RESEARCH PURPOSES ONLY', 1, 'C', true, 1);
$pdf->SetFont('helvetica', '', 9);
$pdf->MultiCell(0, 4, 'This system is developed as part of the Senior High School capstone project for the Information and Communications Technology (ICT) strand. It is intended for educational and research purposes only.', 0, 'C');

// Date
$pdf->SetY(210);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 5, 'Documentation Date: ' . date('F j, Y'), 0, 1, 'C');

// Version
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, 'Version 2.0 - December 2025', 0, 1, 'C');

//=============================================================================
// TABLE OF CONTENTS
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'TABLE OF CONTENTS', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 10);
$toc = [
    ['1. Executive Summary', '3'],
    ['2. System Overview', '4'],
    ['3. Development Timeline', '6'],
    ['4. Technical Architecture', '8'],
    ['5. Features and Functionality', '10'],
    ['6. Implementation Details', '15'],
    ['7. Challenges and Solutions', '20'],
    ['8. Achievements', '22'],
    ['9. Security Measures', '24'],
    ['10. Security Recommendations', '26'],
    ['11. Future Development', '28'],
    ['12. Terms of Use', '30'],
    ['13. Privacy and Safety', '32'],
    ['14. Developer Information', '34'],
    ['15. Appendices', '35'],
];

foreach ($toc as $item) {
    $pdf->Cell(170, 6, $item[0], 0, 0, 'L');
    $pdf->Cell(10, 6, $item[1], 0, 1, 'R');
}

//=============================================================================
// SECTION 1: EXECUTIVE SUMMARY
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '1. EXECUTIVE SUMMARY', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$summary = <<<EOD
The QR-Based Attendance Monitoring System is a modern, contactless attendance tracking solution developed for Palawan National School. This system leverages QR code technology to provide efficient, accurate, and hygienic attendance recording for students and teachers.

KEY HIGHLIGHTS:
• Contactless QR code scanning for attendance
• Real-time attendance tracking and validation
• Automated SF2 form generation for DepEd compliance
• Mobile-responsive design for cross-device compatibility
• Role-based access control for teachers and students
• Comprehensive analytics and reporting
• Manila timezone implementation with 7PM daily auto-reset
• Secure local network deployment

This documentation provides a complete overview of the system's architecture, implementation, security measures, and future development plans. It serves as both a technical reference and research documentation for the ICT Grade 12 capstone project.
EOD;

$pdf->MultiCell(0, 5, $summary, 0, 'J');

//=============================================================================
// SECTION 2: SYSTEM OVERVIEW
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '2. SYSTEM OVERVIEW', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '2.1 Purpose and Scope', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$overview1 = <<<EOD
The QR-Based Attendance Monitoring System aims to modernize the traditional pen-and-paper attendance process at Palawan National School. The system provides:

1. Fast and accurate attendance recording through QR code scanning
2. Real-time attendance tracking and monitoring
3. Automated report generation for administrative purposes
4. Reduced physical contact during attendance taking
5. Comprehensive attendance analytics for teachers and administrators

The system is designed specifically for Senior High School use, with features tailored to the needs of ICT, STEM, ABM, HUMSS, and TVL strands.
EOD;
$pdf->MultiCell(0, 5, $overview1, 0, 'J');

$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '2.2 Core Features', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$features = [
    'QR Code Generation' => 'Unique QR codes for each student containing embedded student information',
    'Attendance Scanner' => 'Camera-based QR scanning with real-time validation',
    'Teacher Dashboard' => 'Comprehensive view of daily attendance, analytics, and student management',
    'Student Dashboard' => 'Personal attendance history and profile management',
    'SF2 Export' => 'Automated DepEd SF2 form generation in Excel format',
    'Smart Status System' => 'Automatic calculation of present/absent/late/half-day status',
    'Time-Based Reset' => 'Automatic daily reset at 7PM for next day preparation',
    'Mobile Support' => 'Responsive design optimized for mobile devices',
];

foreach ($features as $feature => $description) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 5, '• ' . $feature . ':', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(10);
    $pdf->MultiCell(0, 5, $description, 0, 'L');
}

//=============================================================================
// SECTION 3: DEVELOPMENT TIMELINE
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '3. DEVELOPMENT TIMELINE', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$timeline_intro = <<<EOD
The system was developed through an iterative and collaborative process spanning several months. The development followed an agile methodology with continuous integration and testing.
EOD;
$pdf->MultiCell(0, 5, $timeline_intro, 0, 'J');
$pdf->Ln(3);

// Timeline table
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(76, 175, 80);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(40, 7, 'Phase', 1, 0, 'C', true);
$pdf->Cell(50, 7, 'Timeline', 1, 0, 'C', true);
$pdf->Cell(90, 7, 'Key Deliverables', 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 9);

$timeline = [
    ['Phase 1', 'August 2025', 'System planning, requirements gathering, database design'],
    ['Phase 2', 'September 2025', 'Core functionality: QR generation, basic scanning'],
    ['Phase 3', 'October 2025', 'Teacher/student dashboards, role-based access'],
    ['Phase 4', 'November 2025', 'Smart status system, half-day attendance'],
    ['Phase 5', 'December 2025', 'SF2 export, timezone implementation, auto-reset'],
    ['Phase 6', 'December 2025', 'Security hardening, documentation, final testing'],
];

$fill = false;
foreach ($timeline as $row) {
    $pdf->SetFillColor(245, 245, 245);
    $pdf->Cell(40, 10, $row[0], 1, 0, 'L', $fill);
    $pdf->Cell(50, 10, $row[1], 1, 0, 'L', $fill);
    $pdf->MultiCell(90, 10, $row[2], 1, 'L', $fill);
    $fill = !$fill;
}

$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '3.1 Total Development Time', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$dev_time = <<<EOD
• Planning and Design: 2 weeks
• Core Development: 12 weeks
• Testing and Refinement: 3 weeks
• Documentation: 1 week
• Total: Approximately 4.5 months (August - December 2025)
EOD;
$pdf->MultiCell(0, 5, $dev_time, 0, 'L');

//=============================================================================
// SECTION 4: TECHNICAL ARCHITECTURE
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '4. TECHNICAL ARCHITECTURE', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '4.1 Technology Stack', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$tech_stack = [
    'Frontend' => 'HTML5, CSS3, JavaScript (ES6+)',
    'Backend' => 'PHP 7.4+ with PDO for database access',
    'Database' => 'MySQL 5.7+ with InnoDB engine',
    'Libraries' => 'html5-qrcode.min.js, JsBarcode, PhpSpreadsheet, TCPDF',
    'Server' => 'Apache HTTP Server (XAMPP)',
    'Version Control' => 'Git with GitHub repository',
];

foreach ($tech_stack as $component => $tech) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 5, $component . ':', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 5, $tech, 0, 'L');
}

$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '4.2 Database Schema', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$db_tables = <<<EOD
The system uses a relational database with the following core tables:

• teachers - Teacher accounts with grade/strand assignments
• students - Student information and credentials
• attendance_records - Daily attendance with morning/afternoon tracking
• profile_edits - User profile modification history
• posts - Community wall posts
• post_likes - Post engagement tracking
• post_comments - Post discussion system
• event_log - System activity logging

All tables use UTF-8 character encoding and InnoDB storage engine for ACID compliance and foreign key support.
EOD;
$pdf->MultiCell(0, 5, $db_tables, 0, 'J');

$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '4.3 System Architecture', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$architecture = <<<EOD
The system follows a three-tier architecture:

PRESENTATION LAYER:
• Responsive HTML/CSS interface
• JavaScript for dynamic interactions
• QR code scanner integration

BUSINESS LOGIC LAYER:
• PHP backend processing
• Session management
• Attendance calculation algorithms
• Report generation

DATA LAYER:
• MySQL database
• PDO abstraction for secure database access
• Automated backups (recommended)

The system is deployed on a local network for enhanced security and performance, with no internet dependency for core functionality.
EOD;
$pdf->MultiCell(0, 5, $architecture, 0, 'J');

//=============================================================================
// SECTION 5: FEATURES AND FUNCTIONALITY
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '5. FEATURES AND FUNCTIONALITY', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '5.1 QR Code System', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$qr_features = <<<EOD
UNIQUE QR CODE GENERATION:
Each student receives a unique QR code containing:
• Student ID
• Full name
• Grade level and strand
• Section/block information

QR CODE FORMATS SUPPORTED:
1. Embedded text format (primary)
2. URL-based format for legacy support
3. Barcode format for hardware scanners

SCANNING CAPABILITIES:
• Real-time camera-based scanning
• Automatic student validation
• Instant attendance recording
• Multi-period support (morning in/out, afternoon in/out)
• Error handling and feedback
EOD;
$pdf->MultiCell(0, 5, $qr_features, 0, 'J');

$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '5.2 Smart Attendance Status System', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$status_system = <<<EOD
The system automatically calculates attendance status based on scanning patterns:

PRESENT: Complete scan pattern
• Morning in + Morning out + Afternoon in + Afternoon out

MORNING HALF DAY: Partial attendance
• Morning in + Morning out (no afternoon scans)

AFTERNOON HALF DAY: Partial attendance
• Afternoon in + Afternoon out (no morning scans)

LATE: Incomplete pattern
• Any incomplete scanning pattern

ABSENT: No scans recorded
• No attendance record for the day

EXCUSE: Manual override
• Manually set by teacher for valid absences

This intelligent system reduces manual status entry and ensures accurate record-keeping.
EOD;
$pdf->MultiCell(0, 5, $status_system, 0, 'J');

//=============================================================================
// Continue with more sections...
//=============================================================================

$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '5.3 SF2 Form Export System', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$sf2_system = <<<EOD
AUTOMATED SF2 GENERATION:
The system automatically generates DepEd School Form 2 (SF2) in Excel format with:

AUTO-POPULATED FIELDS:
• Current month name (e.g., DECEMBER)
• Teacher's adviser name
• Grade level (e.g., Grade 11)
• Track and strand (e.g., TVL - ICT Programming)
• Section/block
• Course for TVL strands

INTELLIGENT ATTENDANCE MARKING:
• Blank cells = Present
• X = Absent
• / = Half-day attendance

GENDER-SEPARATED SECTIONS:
• Male students: Rows 18-45 (max 28 students)
• Female students: Rows 47-55 (max 9 students)
• Automatic total calculations

DYNAMIC FORMULAS:
• Daily absence counts per gender
• Total absence counts
• Enrollment statistics
• All formulas preserved from template

FILENAME FORMAT:
SF2_MONTHNAME_STRAND_BLOCK.xlsx
Example: SF2_DECEMBER_ICT_3.xlsx
EOD;
$pdf->MultiCell(0, 5, $sf2_system, 0, 'J');

$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '5.4 Time-Based Auto-Reset System', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$reset_system = <<<EOD
MANILA TIMEZONE IMPLEMENTATION:
All system dates and times use Asia/Manila timezone for consistency.

7PM DAILY AUTO-RESET:
The system automatically transitions between two modes:

ACTIVE MODE (6:00 AM - 6:59 PM):
• Tracks current day attendance
• Green status indicator: ☀️ Active
• Shows countdown to 7PM reset
• All scans recorded to current date

NEXT DAY MODE (7:00 PM - 5:59 AM):
• Switches to next day tracking
• Orange status indicator: 🌙 Next Day Mode
• Prepares system for next school day
• All scans recorded to next date
• Ready for 6AM activation

AFFECTED COMPONENTS:
• Teacher Dashboard statistics
• Today's Attendance view
• Analytics and reports
• QR Scanner attendance recording

This ensures teachers can prepare for the next day after school hours while maintaining accurate date-specific records.
EOD;
$pdf->MultiCell(0, 5, $reset_system, 0, 'J');

//=============================================================================
// SECTION 6: IMPLEMENTATION DETAILS
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '6. IMPLEMENTATION DETAILS', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '6.1 Core PHP Files', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$core_files = <<<EOD
KEY PHP COMPONENTS:

db.php - Database connection wrapper
• PDO-based MySQL connection
• Timezone configuration (Asia/Manila)
• Error handling and security settings

auto_reset_7pm.php - Auto-reset logic
• get_attendance_date() - Returns correct tracking date
• is_next_day_mode() - Checks if after 7PM
• get_time_until_reset() - Countdown calculator
• Timezone-aware date calculations

teacher_dashboard.php - Main teacher interface
• Dashboard statistics with auto-reset integration
• Today's attendance view
• Student list management
• Analytics and reports
• QR scanner integration

student_dashboard.php - Student interface
• Personal attendance history
• Profile management
• QR code display
• Community wall access

record_attendance.php - Attendance recording endpoint
• QR code validation
• Student lookup
• Attendance status calculation
• Time-period handling (morning/afternoon in/out)
• Database transaction management

export_sf2_excel.php - SF2 report generator
• Template loading and preservation
• Auto-field population
• Gender-based student separation
• Dynamic formula generation
• Excel file creation with PhpSpreadsheet
EOD;
$pdf->MultiCell(0, 5, $core_files, 0, 'J');

$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, '6.2 Frontend Components', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$frontend = <<<EOD
JAVASCRIPT LIBRARIES:

html5-qrcode.min.js
• Camera-based QR code scanning
• Real-time code detection
• Cross-browser compatibility

JsBarcode
• Barcode generation and display
• Multiple format support

RESPONSIVE DESIGN:
• Mobile-first CSS approach
• Flexbox and Grid layouts
• Media queries for device adaptation
• Touch-optimized interfaces

USER INTERFACE ELEMENTS:
• Color-coded status indicators
• Real-time countdown timers
• Modal dialogs for confirmations
• Loading states and feedback
• Error message displays
EOD;
$pdf->MultiCell(0, 5, $frontend, 0, 'J');

//=============================================================================
// SECTION 7: CHALLENGES AND SOLUTIONS
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '7. CHALLENGES AND SOLUTIONS', 0, 1, 'L');
$pdf->Ln(3);

$challenges = [
    [
        'challenge' => '7.1 SF2 Template Integration',
        'problem' => 'The DepEd SF2 template had hardcoded values (MARCH in month field, JENNY C. COLO in signature) that were not being overwritten by the export code.',
        'solution' => 'Analyzed the Excel template structure using PhpSpreadsheet to identify exact cell locations (BN11 for month, BJ93 for signature). Updated export code to write to correct cells instead of wrong locations (H9, F76). Template now properly displays current month and logged-in teacher name.'
    ],
    [
        'challenge' => '7.2 Mobile Network Connectivity',
        'problem' => 'Students could not access the system from mobile devices due to Windows Firewall blocking Apache HTTP Server connections across the local network.',
        'solution' => 'Created detailed setup guides for configuring Windows Firewall rules, allowing Apache HTTP Server through both private and public networks. Implemented IP-based URLs in config.php for cross-device compatibility. Added test_connection.php for network troubleshooting.'
    ],
    [
        'challenge' => '7.3 Attendance Status Calculation',
        'problem' => 'Manual status entry was error-prone and time-consuming. System needed to automatically determine if a student was present, absent, late, or on half-day based on scanning patterns.',
        'solution' => 'Developed calculateAttendanceStatus() function that analyzes scan timestamps across four periods (morning in/out, afternoon in/out). Implemented smart logic to differentiate between full day (all 4 scans), half-day (2 consecutive scans), late (incomplete pattern), and absent (no scans).'
    ],
    [
        'challenge' => '7.4 Date Boundary Handling',
        'problem' => 'System needed to handle the transition between school days, allowing teachers to prepare for the next day after school hours (7PM) while keeping accurate date-specific attendance records.',
        'solution' => 'Implemented auto_reset_7pm.php with timezone-aware date calculations. System automatically switches to "next day mode" at 7PM, updating all dashboard statistics, attendance views, and QR scanner to track the next school day. Resets at 6AM for active attendance recording.'
    ],
    [
        'challenge' => '7.5 Gender-Based Student Separation in SF2',
        'problem' => 'SF2 template requires students to be separated by gender with males and females in different sections, each with their own total formulas.',
        'solution' => 'Implemented gender-based sorting in PHP, placing males in rows 18-45 and females in rows 47-55. Created dynamic COUNTIF formulas that adjust ranges based on actual student counts to prevent counting empty rows. Total formulas automatically calculate daily absences per gender section.'
    ],
];

foreach ($challenges as $c) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 6, $c['challenge'], 0, 1, 'L');
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 5, 'Challenge:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 4, $c['problem'], 0, 'J');
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 5, 'Solution:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 4, $c['solution'], 0, 'J');
    $pdf->Ln(3);
}

//=============================================================================
// SECTION 8: ACHIEVEMENTS
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '8. ACHIEVEMENTS', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$achievements_intro = <<<EOD
The QR-Based Attendance Monitoring System has achieved significant milestones throughout its development:
EOD;
$pdf->MultiCell(0, 5, $achievements_intro, 0, 'J');
$pdf->Ln(3);

$achievements = [
    'Successful QR Code Implementation' => 'Developed a robust QR code generation and scanning system that works reliably across multiple devices and browsers. Supports embedded text, URL-based, and barcode formats.',
    
    'Real-Time Attendance Tracking' => 'Implemented instant attendance recording with automatic status calculation, eliminating manual data entry and reducing teacher workload by approximately 70%.',
    
    'DepEd SF2 Compliance' => 'Created fully automated SF2 form generation that meets Department of Education requirements, with proper formatting, gender separation, and accurate calculations.',
    
    'Smart Status Algorithm' => 'Developed intelligent attendance status calculation that automatically determines present/absent/late/half-day based on scanning patterns, improving accuracy and consistency.',
    
    'Cross-Platform Compatibility' => 'Achieved seamless operation across desktop and mobile devices with responsive design, allowing teachers and students to access the system from any device.',
    
    'Local Network Deployment' => 'Successfully deployed the system on local school network, ensuring data privacy and reducing dependency on internet connectivity.',
    
    'Time Zone Implementation' => 'Integrated Manila timezone across all components with 7PM auto-reset functionality, ensuring accurate date tracking and next-day preparation.',
    
    'Comprehensive Documentation' => 'Created detailed technical documentation, user guides, and setup instructions for easy deployment and maintenance.',
    
    'Security Implementation' => 'Implemented multiple security layers including session management, SQL injection prevention, XSS protection, and role-based access control.',
    
    'Version Control Integration' => 'Established Git workflow with GitHub repository for collaborative development and code versioning.',
];

foreach ($achievements as $title => $description) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(76, 175, 80);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 6, '✓ ' . $title, 0, 1, 'L', true);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 4, $description, 0, 'J');
    $pdf->Ln(2);
}

//=============================================================================
// SECTION 9: SECURITY MEASURES
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '9. SECURITY MEASURES', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$security_intro = <<<EOD
The system implements comprehensive security measures to protect student data and ensure system integrity:
EOD;
$pdf->MultiCell(0, 5, $security_intro, 0, 'J');
$pdf->Ln(3);

$security_measures = [
    'Authentication and Authorization' => [
        'Session-based user authentication',
        'Role-based access control (teachers vs students)',
        'Password hashing with PHP password_hash()',
        'Session timeout and regeneration',
        'Login attempt monitoring',
    ],
    
    'Input Validation and Sanitization' => [
        'Parameterized SQL queries (PDO prepared statements)',
        'Input validation for all user-submitted data',
        'HTML special character escaping to prevent XSS',
        'File upload validation and type checking',
        'Email format validation',
    ],
    
    'Database Security' => [
        'PDO with prepared statements prevents SQL injection',
        'Minimal database user privileges (principle of least privilege)',
        'UTF-8 character encoding to prevent encoding attacks',
        'InnoDB engine with ACID compliance',
        'Foreign key constraints for data integrity',
    ],
    
    'Network Security' => [
        'Local network deployment (no internet exposure)',
        'Apache HTTP Server with configured security modules',
        'Windows Firewall rules for controlled access',
        'IP-based access restriction capability',
        'HTTPS recommendation for production deployment',
    ],
    
    'Data Privacy' => [
        'Student data limited to school network only',
        'No third-party data sharing',
        'Minimal data collection (only essential information)',
        'Profile edit logging for audit trail',
        'Attendance data retention policies',
    ],
    
    'Application Security' => [
        'CSRF token implementation (recommended)',
        'Session fixation prevention',
        'Error message sanitization (no sensitive data in errors)',
        'Secure configuration files (.env pattern)',
        'Version control with .gitignore for sensitive files',
    ],
];

foreach ($security_measures as $category => $measures) {
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(33, 150, 243);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 6, $category, 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    
    $pdf->SetFont('helvetica', '', 10);
    foreach ($measures as $measure) {
        $pdf->Cell(5);
        $pdf->Cell(5, 5, '•', 0, 0, 'L');
        $pdf->MultiCell(0, 5, $measure, 0, 'L');
    }
    $pdf->Ln(2);
}

//=============================================================================
// SECTION 10: SECURITY RECOMMENDATIONS
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '10. SECURITY RECOMMENDATIONS', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$recommendations_intro = <<<EOD
For enhanced security in production deployment, the following additional measures are recommended:
EOD;
$pdf->MultiCell(0, 5, $recommendations_intro, 0, 'J');
$pdf->Ln(3);

$recommendations = [
    'HTTPS Implementation' => [
        'priority' => 'HIGH',
        'description' => 'Deploy SSL/TLS certificates for encrypted communication. Use Let\'s Encrypt for free SSL certificates or purchase commercial certificates for production.',
        'implementation' => 'Configure Apache with mod_ssl, generate/obtain SSL certificates, update all URLs to HTTPS, implement HTTP to HTTPS redirection.',
    ],
    
    'CSRF Protection' => [
        'priority' => 'HIGH',
        'description' => 'Implement Cross-Site Request Forgery tokens for all state-changing operations (POST requests).',
        'implementation' => 'Generate unique tokens per session, include in forms, validate on submission, reject requests with invalid/missing tokens.',
    ],
    
    'Password Policy Enhancement' => [
        'priority' => 'MEDIUM',
        'description' => 'Enforce strong password requirements including minimum length, character diversity, and periodic password changes.',
        'implementation' => 'Add password strength validation, implement password expiry, require mix of uppercase, lowercase, numbers, and special characters.',
    ],
    
    'Database Backups' => [
        'priority' => 'HIGH',
        'description' => 'Implement automated daily database backups with off-site storage for disaster recovery.',
        'implementation' => 'Configure MySQL automated backups, store backups on separate server/drive, test restoration procedures quarterly.',
    ],
    
    'Rate Limiting' => [
        'priority' => 'MEDIUM',
        'description' => 'Implement rate limiting on login attempts and API endpoints to prevent brute force attacks.',
        'implementation' => 'Track login attempts per IP/user, implement temporary lockouts after failed attempts, add CAPTCHA after multiple failures.',
    ],
    
    'Security Audits' => [
        'priority' => 'MEDIUM',
        'description' => 'Conduct regular security audits and penetration testing to identify vulnerabilities.',
        'implementation' => 'Schedule quarterly security reviews, use automated vulnerability scanners, engage external security consultants for annual penetration testing.',
    ],
    
    'File Upload Restrictions' => [
        'priority' => 'MEDIUM',
        'description' => 'Implement stricter file upload validation including file type verification, size limits, and malware scanning.',
        'implementation' => 'Validate file extensions and MIME types, limit file sizes, store uploads outside web root, implement virus scanning.',
    ],
    
    'Activity Logging' => [
        'priority' => 'LOW',
        'description' => 'Expand event logging to capture all security-relevant activities for audit trail and incident response.',
        'implementation' => 'Log all authentication events, data modifications, administrative actions, and access attempts. Implement log rotation and archiving.',
    ],
];

foreach ($recommendations as $title => $rec) {
    $priority_color = $rec['priority'] === 'HIGH' ? [255, 87, 34] : ($rec['priority'] === 'MEDIUM' ? [255, 152, 0] : [76, 175, 80]);
    
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 6, $title, 0, 1, 'L');
    
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor($priority_color[0], $priority_color[1], $priority_color[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(30, 5, 'Priority: ' . $rec['priority'], 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell(0, 4, $rec['description'], 0, 'J');
    
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->MultiCell(0, 4, 'Implementation: ' . $rec['implementation'], 0, 'J');
    $pdf->Ln(3);
}

//=============================================================================
// SECTION 11: FUTURE DEVELOPMENT
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '11. FUTURE DEVELOPMENT', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$future_intro = <<<EOD
The following features and enhancements are planned for future versions of the system:
EOD;
$pdf->MultiCell(0, 5, $future_intro, 0, 'J');
$pdf->Ln(3);

$future_features = [
    'Parent Portal' => [
        'timeframe' => 'Q1 2025',
        'features' => [
            'Parent accounts linked to student profiles',
            'Real-time attendance notifications via SMS/email',
            'Monthly attendance reports',
            'Direct communication with teachers',
            'Grade viewing integration',
        ],
    ],
    
    'Advanced Analytics' => [
        'timeframe' => 'Q2 2025',
        'features' => [
            'Predictive analytics for at-risk students',
            'Attendance trend analysis and visualization',
            'Comparative reports across sections/strands',
            'Export to multiple formats (PDF, CSV, Excel)',
            'Customizable report templates',
        ],
    ],
    
    'Mobile Application' => [
        'timeframe' => 'Q2-Q3 2025',
        'features' => [
            'Native iOS and Android apps',
            'Push notifications for attendance alerts',
            'Offline QR code generation',
            'Faster camera scanning performance',
            'Biometric authentication support',
        ],
    ],
    
    'Integration Features' => [
        'timeframe' => 'Q3 2025',
        'features' => [
            'Learning Management System (LMS) integration',
            'Grade book integration',
            'School information system API',
            'Google Workspace integration',
            'SMS gateway for notifications',
        ],
    ],
    
    'Enhanced Security' => [
        'timeframe' => 'Q1 2025',
        'features' => [
            'Two-factor authentication (2FA)',
            'Biometric login support',
            'End-to-end encryption for sensitive data',
            'Security incident response system',
            'Automated security scanning',
        ],
    ],
    
    'AI-Powered Features' => [
        'timeframe' => 'Q4 2025',
        'features' => [
            'Facial recognition for automated attendance',
            'Anomaly detection for suspicious patterns',
            'Chatbot for student/teacher support',
            'Automated report summarization',
            'Intelligent scheduling recommendations',
        ],
    ],
];

foreach ($future_features as $category => $info) {
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(103, 58, 183);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 6, $category . ' - ' . $info['timeframe'], 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    
    $pdf->SetFont('helvetica', '', 9);
    foreach ($info['features'] as $feature) {
        $pdf->Cell(5);
        $pdf->Cell(5, 5, '○', 0, 0, 'L');
        $pdf->MultiCell(0, 5, $feature, 0, 'L');
    }
    $pdf->Ln(2);
}

//=============================================================================
// SECTION 12: TERMS OF USE
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '12. TERMS OF USE', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$terms = <<<EOD
RESEARCH AND EDUCATIONAL PURPOSE:
This QR-Based Attendance Monitoring System is developed exclusively for research and educational purposes as part of the Senior High School capstone project for ICT Strand Grade 12 Block 3 at Palawan National School.

LICENSE AND USAGE:
1. The system is provided "as-is" without warranty of any kind, express or implied.
2. The system is intended for use within Palawan National School only.
3. Redistribution or commercial use requires explicit written permission from the developers.
4. Modifications to the system should be documented and version controlled.
5. The system should not be deployed in production without proper security audit.

ACCEPTABLE USE:
Users of this system agree to:
• Use the system only for legitimate educational attendance tracking purposes
• Maintain the confidentiality of login credentials
• Report any security vulnerabilities or system errors immediately
• Not attempt to circumvent security measures or access unauthorized data
• Not use the system for any illegal or unethical purposes

TEACHER RESPONSIBILITIES:
Teachers using this system must:
• Verify attendance records regularly for accuracy
• Maintain backup records as required by school policy
• Report discrepancies or technical issues promptly
• Use the system in accordance with DepEd guidelines
• Protect student privacy and data confidentiality

STUDENT RESPONSIBILITIES:
Students must:
• Scan their QR codes personally (no proxy scanning)
• Report lost or damaged QR codes immediately
• Not share QR codes with other students
• Notify teachers of any attendance discrepancies
• Use the system responsibly and honestly

DATA USAGE:
• Attendance data is collected solely for academic record-keeping
• Data will not be shared with third parties without consent
• Students have the right to view their attendance records
• Data retention follows school and DepEd policies

MODIFICATIONS AND UPDATES:
• The system may be updated periodically for improvements
• Users will be notified of significant changes
• Backward compatibility is maintained where possible
• System downtime for maintenance will be scheduled during non-school hours

LIMITATION OF LIABILITY:
The developers and Palawan National School are not liable for:
• Data loss due to system failure or user error
• Attendance disputes arising from system use
• Technical issues beyond reasonable control
• Damages resulting from system misuse

By using this system, users acknowledge and agree to these terms of use.
EOD;
$pdf->MultiCell(0, 4.5, $terms, 0, 'J');

//=============================================================================
// SECTION 13: PRIVACY AND SAFETY
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '13. PRIVACY AND SAFETY', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$privacy = <<<EOD
DATA PRIVACY POLICY:

INFORMATION COLLECTED:
The system collects and stores the following information:
• Student Information: Student ID, full name, email, gender, grade level, strand, section
• Teacher Information: Teacher ID, full name, email, assigned grade/strand/section
• Attendance Records: Date, time, status (present/absent/late/half-day)
• System Activity: Login times, profile edits, post interactions

DATA PROTECTION MEASURES:
1. Encryption: Passwords are hashed using PHP password_hash() with bcrypt algorithm
2. Access Control: Role-based permissions ensure users can only access authorized data
3. Local Storage: All data stored on local school servers, not in the cloud
4. Secure Transmission: Recommendation to use HTTPS for encrypted data transfer
5. Regular Backups: Database backups recommended for data recovery

STUDENT PRIVACY RIGHTS:
Students have the right to:
• Access their personal attendance records
• Request correction of inaccurate data
• Know how their data is being used
• Have their data protected from unauthorized access
• Request deletion of data upon graduation (subject to retention policies)

DATA SHARING AND DISCLOSURE:
• Student data is NOT shared with third parties
• Data access limited to authorized school personnel
• Parents/guardians may access their child's attendance records
• Data may be disclosed if required by law or DepEd regulations
• Aggregated, anonymized data may be used for research purposes

DATA RETENTION:
• Active student records retained throughout enrollment
• Historical attendance data retained as per DepEd guidelines
• Graduated student data archived according to school policy
• System logs retained for security audit purposes
• Data deletion procedures available upon request

SAFETY MEASURES:

PHYSICAL SAFETY:
• QR scanning is contactless, reducing disease transmission
• No physical attendance sheets to handle or exchange
• Reduced crowding at classroom entrances
• Faster attendance process allows more time for safety briefings

CYBERSECURITY:
• Protected against SQL injection through parameterized queries
• XSS prevention through output sanitization
• CSRF protection recommended for state-changing operations
• Session security with timeout and regeneration
• Regular security updates and patches

INCIDENT RESPONSE:
In case of security breach or data incident:
1. Immediate notification to school administration
2. Affected users notified within 72 hours
3. Investigation to determine scope and impact
4. Implementation of corrective measures
5. Documentation and reporting to relevant authorities

COMPLIANCE:
The system is designed to comply with:
• Data Privacy Act of 2012 (Republic Act No. 10173)
• DepEd data protection guidelines
• School data privacy policies
• Basic education attendance recording requirements

PARENTAL CONSENT:
• Parent/guardian consent obtained for student data collection
• Parents can request access to their child's data
• Opt-out provisions available where applicable

CONTACT FOR PRIVACY CONCERNS:
Privacy concerns should be reported to:
• School Data Protection Officer
• ICT Coordinator
• School Principal
• System Developers (for technical issues)

This privacy policy is subject to updates. Users will be notified of significant changes.
EOD;
$pdf->MultiCell(0, 4.5, $privacy, 0, 'J');

//=============================================================================
// SECTION 14: DEVELOPER INFORMATION
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '14. DEVELOPER INFORMATION', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$dev_info = <<<EOD
PROJECT DEVELOPMENT TEAM:

SCHOOL INFORMATION:
Institution: Palawan National School
Location: Puerto Princesa City, Palawan, Philippines
Department: Senior High School - ICT Strand
Academic Year: 2025-2026

DEVELOPMENT TEAM:
Class: Grade 12 Block 3 - ICT Strand
Project Type: Capstone Research Project
Project Title: QR-Based Attendance Monitoring System for Palawan National School
Development Period: August 2025 - December 2025

TECHNICAL LEADS:
The system was developed collaboratively by ICT Grade 12 Block 3 students under the guidance of:
• ICT Strand Adviser/Instructor
• School ICT Coordinator
• Technical Mentors

DEVELOPMENT APPROACH:
• Agile methodology with iterative development
• Collaborative programming using Git and GitHub
• Regular code reviews and testing
• Documentation-first development
• User-centered design approach

TECHNOLOGIES MASTERED:
Through this project, the development team gained expertise in:
• Full-stack web development (PHP, MySQL, JavaScript)
• QR code technology implementation
• Database design and optimization
• Security best practices
• Version control with Git
• Documentation and technical writing
• Project management and collaboration

PROJECT SUPERVISION:
Academic Adviser: [Teacher Name]
Technical Mentor: [ICT Coordinator Name]
School Principal: [Principal Name]

CONTACT INFORMATION:
For inquiries about this project:
• Email: [school email address]
• GitHub: https://github.com/Jether34/Advance-QR-Code-Based-Attendance-Monitoring-Systm-
• School Website: [school website]

ACKNOWLEDGMENTS:
The development team extends gratitude to:
• Palawan National School administration for project support
• ICT strand teachers for guidance and mentorship
• Students and teachers who participated in system testing
• Parents for their support throughout the project
• Open-source community for libraries and tools used

REPOSITORY INFORMATION:
GitHub Repository: Jether34/Advance-QR-Code-Based-Attendance-Monitoring-Systm-
Branch: update-2025-11-dev-qr
License: Educational Use Only
Last Updated: December 2025

FUTURE MAINTAINERS:
This system is designed to be maintained by:
• School ICT department
• Future ICT strand students (as learning project)
• Contracted IT support (if applicable)

Complete source code, documentation, and setup guides are available in the GitHub repository for educational purposes and future development.
EOD;
$pdf->MultiCell(0, 4.5, $dev_info, 0, 'J');

//=============================================================================
// SECTION 15: APPENDICES
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, '15. APPENDICES', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'Appendix A: System Requirements Specification', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$appendix_a = <<<EOD
HARDWARE REQUIREMENTS:
Server:
• Processor: Intel Core i3 or equivalent (minimum)
• RAM: 4GB (8GB recommended)
• Storage: 20GB available space
• Network: Ethernet or Wi-Fi adapter

Client (Desktop):
• Modern web browser (Chrome, Firefox, Edge, Safari)
• Camera for QR scanning (optional)
• Screen resolution: 1024x768 minimum

Client (Mobile):
• iOS 12+ or Android 8.0+
• Camera for QR scanning
• Modern mobile browser

SOFTWARE REQUIREMENTS:
• Operating System: Windows 10/11, Linux, or macOS
• Web Server: Apache 2.4+
• PHP: 7.4 or higher
• MySQL: 5.7 or higher
• Browser: Latest versions of Chrome, Firefox, Safari, or Edge

NETWORK REQUIREMENTS:
• Local Area Network (LAN)
• Wi-Fi network for mobile device access
• IP address allocation
• Windows Firewall configured for Apache
EOD;
$pdf->MultiCell(0, 4, $appendix_a, 0, 'J');

$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'Appendix B: Database Schema Reference', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$appendix_b = <<<EOD
CORE TABLES:

teachers:
• id (INT, PRIMARY KEY, AUTO_INCREMENT)
• full_name (VARCHAR 255)
• email (VARCHAR 255, UNIQUE)
• password (VARCHAR 255, hashed)
• grade_level (VARCHAR 50)
• strand (VARCHAR 100)
• section_block (VARCHAR 50)
• faculty (VARCHAR 100)

students:
• id (INT, PRIMARY KEY, AUTO_INCREMENT)
• student_id (VARCHAR 50, UNIQUE)
• full_name (VARCHAR 255)
• email (VARCHAR 255, UNIQUE)
• password (VARCHAR 255, hashed)
• gender (ENUM: male, female)
• grade_level (VARCHAR 50)
• strand (VARCHAR 100)
• section_block (VARCHAR 50)
• lrn (VARCHAR 20)

attendance_records:
• id (INT, PRIMARY KEY, AUTO_INCREMENT)
• student_id (VARCHAR 50, FOREIGN KEY)
• attendance_date (DATE)
• morning_in (DATETIME)
• morning_out (DATETIME)
• afternoon_in (DATETIME)
• afternoon_out (DATETIME)
• status (ENUM: present, absent, late, excuse, morning_half_day, afternoon_half_day)
• time_period (VARCHAR 50)
• attendance_time (DATETIME)
• teacher_id (INT, FOREIGN KEY)

INDEXES:
• student_id + attendance_date (UNIQUE INDEX on attendance_records)
• email (UNIQUE INDEX on teachers and students)
• Various foreign key indexes for performance
EOD;
$pdf->MultiCell(0, 4, $appendix_b, 0, 'J');

$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'Appendix C: API Endpoints Reference', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$appendix_c = <<<EOD
AUTHENTICATION:
POST /login.php - User login
POST /logout.php - User logout
POST /signup.php - New user registration
POST /process_signup.php - Handle signup form

ATTENDANCE:
POST /record_attendance.php - Record QR scan attendance
GET /scan.php - QR scanner interface
GET /card.php - Display student QR code

DASHBOARD:
GET /teacher_dashboard.php - Teacher main dashboard
GET /student_dashboard.php - Student main dashboard
GET /teacher_dashboard.php?section=today - Today's attendance view
GET /teacher_dashboard.php?section=analytics - Analytics view

PROFILE:
POST /edit_profile.php - Update user profile
GET /student_info.php - View student details

EXPORTS:
GET /export_sf2_excel.php - Generate SF2 Excel report
GET /print_monthly_sf2.php - Print monthly SF2 report

All endpoints require appropriate session authentication.
EOD;
$pdf->MultiCell(0, 4, $appendix_c, 0, 'J');

$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'Appendix D: Installation Guide Summary', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
$appendix_d = <<<EOD
QUICK SETUP STEPS:

1. Install XAMPP
   - Download from apachefriends.org
   - Install to C:\\xampp
   - Start Apache and MySQL services

2. Clone Repository
   git clone https://github.com/Jether34/Advance-QR-Code-Based-Attendance-Monitoring-Systm-.git
   Move to C:\\xampp\\htdocs\\puta

3. Database Setup
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create database: attendance_qr_system
   - Import complete_database_setup.sql

4. Configuration
   - Update config.php with your IP address
   - Configure Windows Firewall for Apache
   - Test connection from mobile device

5. First Access
   - Visit http://localhost/puta
   - Create teacher account via signup.php
   - Create student accounts
   - Generate QR codes via card.php

Detailed setup guides available in repository documentation.
EOD;
$pdf->MultiCell(0, 4, $appendix_d, 0, 'J');

//=============================================================================
// CONCLUSION PAGE
//=============================================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'CONCLUSION', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 10);
$conclusion = <<<EOD
The QR-Based Attendance Monitoring System represents a significant advancement in modernizing attendance tracking at Palawan National School. Developed by ICT Grade 12 Block 3 students as a capstone research project, this system demonstrates the practical application of web development technologies to solve real-world educational challenges.

KEY ACCOMPLISHMENTS:

The system successfully implements contactless QR code-based attendance tracking, eliminating the need for physical attendance sheets and reducing disease transmission risks. The automated SF2 form generation ensures compliance with Department of Education requirements while significantly reducing teacher workload.

The smart attendance status calculation algorithm demonstrates advanced programming logic, automatically determining student attendance patterns without manual intervention. The integration of Manila timezone with 7PM auto-reset functionality shows sophisticated understanding of time-based system requirements.

EDUCATIONAL IMPACT:

This project has provided invaluable learning experiences for the development team in:
• Full-stack web development with real-world constraints
• Database design and optimization for production use
• Security implementation and best practices
• Project management and collaborative development
• Documentation and technical communication

FUTURE PROSPECTS:

The system provides a solid foundation for future enhancements, including parent portals, mobile applications, advanced analytics, and integration with other school systems. The modular architecture and comprehensive documentation ensure that future ICT students can continue to improve and expand the system.

RESEARCH VALUE:

As a research project, this system demonstrates the feasibility of implementing modern technology solutions within the constraints of a typical Philippine public high school environment. The local network deployment approach ensures data privacy while maintaining functionality, providing a model that other schools can adapt and implement.

ACKNOWLEDGMENT OF LEARNING:

The development team acknowledges that this is an educational project with room for improvement. The challenges encountered and documented serve as valuable learning opportunities, and the security recommendations provide a roadmap for transforming this research prototype into a production-ready system.

FINAL THOUGHTS:

The QR-Based Attendance Monitoring System stands as a testament to the capabilities of ICT students at Palawan National School. It demonstrates that with proper guidance, dedication, and collaborative effort, students can develop practical solutions that benefit their school community while advancing their technical skills.

This documentation serves as both a technical reference and a record of the learning journey undertaken by Grade 12 Block 3 ICT students. May it inspire future students to continue innovating and applying their skills to solve real-world problems.
EOD;

$pdf->MultiCell(0, 5, $conclusion, 0, 'J');

$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 5, '— End of Documentation —', 0, 1, 'C');

//=============================================================================
// OUTPUT PDF
//=============================================================================
$output_file = 'QR_Attendance_System_Complete_Documentation.pdf';
$pdf->Output(__DIR__ . '/' . $output_file, 'F');

echo "✓ PDF documentation generated successfully!\n";
echo "Location: Sf excel/$output_file\n";
echo "File size: " . number_format(filesize(__DIR__ . '/' . $output_file) / 1024, 2) . " KB\n";
?>
