<?php
// teacher_dashboard.php - main dashboard for teachers with sidebar
require_once __DIR__ . '/bootstrap.php';
date_default_timezone_set('Asia/Manila'); // Set Manila timezone
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auto_reset_7pm.php'; // Auto-reset system
require_once __DIR__ . '/page_security.php';

require_once __DIR__ . '/security_utils.php';

$csrf_token = generate_csrf_token();

// Initialize page security
init_page_security();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}

$pdo = get_db();
$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM teachers WHERE id = :id');
$stmt->execute([':id' => $uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { echo 'Teacher not found'; exit; }

// Get attendance date (auto-adjusts after 7PM)
$attendance_date = get_attendance_date();
$is_next_day = is_next_day_mode();

$stmt = $pdo->prepare('SELECT * FROM teachers WHERE id = :id');
$stmt->execute([':id' => $uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { echo 'Teacher not found'; exit; }

// Mobile detection for scanner
$isMobile = preg_match('/(Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini)/i', $_SERVER['HTTP_USER_AGENT']);
$section = $_GET['section'] ?? 'dashboard';

// If mobile user clicks scanner, redirect to mobile scanner
if ($isMobile && $section === 'scanner') {
    header('Location: scanner_mobile.php');
    exit;
}

$errors = [];
$success = '';

// Handle student update
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='update_student'){
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) { $errors[] = 'Invalid CSRF token'; }
    else {
    $sid = (int)($_POST['student_id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $lrn = trim($_POST['lrn'] ?? '');

    // Verify student belongs to teacher's class
    $checkStmt = $pdo->prepare('SELECT id FROM students WHERE id = :id AND grade_level = :grade AND strand = :strand AND section_block = :block');
    $checkStmt->execute([':id'=>$sid, ':grade'=>$user['grade_level'], ':strand'=>$user['strand'], ':block'=>$user['section_block']]);
    if(!$checkStmt->fetch()){
        $errors[] = 'You can only edit students in your class';
    } else {
        foreach(['full_name','email'] as $req){ if(empty($$req)) $errors[] = "$req is required"; }
        if($email && !filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Invalid email format';
        if(!$errors){
            $updates = [];$params=[':id'=>$sid];
            $updates[]='full_name = :full_name'; $params[':full_name']=$full_name;
            $updates[]='email = :email'; $params[':email']=$email;
            if($password!==''){ $updates[]='password = :password'; $params[':password']=password_hash($password,PASSWORD_DEFAULT); }
            if($gender!==''){ $updates[]='gender = :gender'; $params[':gender']=$gender; }
            if($lrn!==''){ $updates[]='lrn = :lrn'; $params[':lrn']=$lrn; }
            $sql = 'UPDATE students SET '.implode(', ',$updates).' WHERE id = :id';
                try { $stmt=$pdo->prepare($sql); $stmt->execute($params); $success='Student updated successfully'; }
                catch(Exception $e){ $errors[]='Update failed: '.$e->getMessage(); }
            }
        }
    }
}

// here are the css styles for sidebar and main content

function active($s, $section) { return $s === $section ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Prevent browser caching and back button exploitation -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Teacher Dashboard - PNS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Manrope', 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background:
                radial-gradient(circle at 18% 18%, rgba(34, 211, 238, 0.12), transparent 40%),
                radial-gradient(circle at 78% -8%, rgba(34, 197, 94, 0.1), transparent 44%),
                linear-gradient(140deg, #0c1426 0%, #102035 50%, #0c2841 100%);
            min-height: 100vh;
            display: flex;
            color: #0f172a;
        }
                .sidebar {
                    width: 280px;
                    background: linear-gradient(180deg, #0ea5e9 0%, #0b243d 100%);
                    color: #fff;
                    min-height: 100vh;
                    padding: 0 0 0 0;
                    box-shadow: 10px 0 30px rgba(8, 47, 73, 0.22);
                    position: fixed;
                    left: 0;
                }
                .sidebar-header {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        padding: 16px 18px 10px 18px;
                        min-height: 56px;
                }
                .sidebar-logo {
                        width: 38px;
                        height: 38px;
                }
                .sidebar-title {
                        font-size: 1.15em;
                        font-weight: 700;
                        margin-left: 6px;
                }
                .sidebar-icons {
                        display: flex;
                        gap: 18px;
                        padding: 8px 18px 4px 18px;
                        margin-bottom: 0;
                }
                .sidebar-icons img, .sidebar-icons i {
                        font-size: 1.25em;
                        width: 28px;
                        height: 28px;
                }
                .sidebar-logout {
                        padding: 8px 18px 8px 18px;
                        margin-top: 0;
                }
                @media (max-width: 768px) {
                    .sidebar {
                        width: 100vw;
                        min-height: unset;
                        padding: 0;
                    }
                    .sidebar-header {
                        padding: 10px 12px 6px 12px;
                        min-height: 44px;
                    }
                    .sidebar-logo {
                        width: 32px;
                        height: 32px;
                    }
                    .sidebar-title {
                        font-size: 1em;
                        margin-left: 4px;
                    }
                    .sidebar-icons {
                        gap: 12px;
                        padding: 6px 12px 2px 12px;
                    }
                    .sidebar-icons img, .sidebar-icons i {
                        font-size: 1em;
                        width: 22px;
                        height: 22px;
                    }
            .sidebar-logout {
                padding: 6px 12px 6px 12px;
            }
        }
        .sidebar {
            top: 0;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.18);
            background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.02) 100%);
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px 0;
            border-bottom: none;
        }
        .sidebar-logo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255,255,255,0.15);
            padding: 6px;
            object-fit: contain;
        }
        .sidebar-text h2 {
            font-size: 1.1em;
            font-weight: 800;
            margin: 0;
            line-height: 1.2;
            color: #fff;
        }
        .school-name {
            font-size: 0.7em;
            color: #bae6fd;
            margin: 2px 0 0;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        .teacher-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            background: rgba(255,255,255,0.1);
            margin: 12px 12px 0 12px;
            border-radius: 12px;
        }
        .teacher-avatar {
            font-size: 2em;
            flex-shrink: 0;
        }
        .teacher-info {
            flex: 1;
            min-width: 0;
        }
        .teacher-name {
            font-size: 0.92em;
            font-weight: 800;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #fff;
        }
        .teacher-role {
            font-size: 0.75em;
            color: #bae6fd;
            margin: 2px 0 0;
            font-weight: 700;
            text-transform: capitalize;
        }
        .sidebar-menu {
            flex: 1;
            padding: 12px 0;
            overflow-y: auto;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            padding: 14px 20px;
            text-decoration: none;
            font-weight: 600;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            font-size: 0.95em;
            position: relative;
        }
        .menu-icon {
            font-size: 1.2em;
            flex-shrink: 0;
        }
        .menu-text {
            flex: 1;
        }
        .menu-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            color: #e0f2fe;
            font-size: 0.7em;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.12);
            border-left-color: #e0f2fe;
            padding-left: 24px;
        }
        .sidebar a.active {
            background: linear-gradient(90deg, rgba(224,242,254,0.25) 0%, rgba(14,165,233,0.16) 100%);
            border-left-color: #e0f2fe;
            box-shadow: inset 2px 0 10px rgba(0,0,0,0.2);
        }
        .sidebar a.active .menu-badge {
            background: #e0f2fe;
            color: #0b243d;
        }
        .sidebar a.logout {
            margin-top: auto;
            border-top: 2px solid rgba(255,255,255,0.14);
            padding-top: 16px;
            margin-bottom: 12px;
            color: #ffe2e2;
            border-left: 4px solid transparent;
        }
        .sidebar a.logout:hover {
            background: rgba(239,68,68,0.16);
            border-left-color: #ef4444;
            color: #fff;
        }
        .main {
            margin-left: 280px;
            flex: 1;
            padding: 0;
            width: calc(100% - 280px);
            background: #f6f8fb;
        }
        .main-header {
            background: #ffffff;
            padding: 20px 32px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 22px rgba(15,23,42,0.08);
        }
        .main-header h1 {
            font-size: 1.5em;
            color: #0f172a;
            margin: 0 0 4px 0;
            font-weight: 800;
        }
        .main-header p {
            margin: 0;
            font-size: 0.9em;
            color: #475569;
        }
        .main-header-stats {
            font-size: 0.85em;
            color: #475569;
        }
        .main-content {
            padding: 24px 32px;
        }
        @media (max-width: 768px) {
            .main {
                margin-left: 0;
                width: 100%;
            }
            .main-header {
                padding: 16px 20px;
            }
            .main-header h1 {
                font-size: 1.2em;
                margin-bottom: 2px;
            }
            .main-content {
                padding: 16px 20px;
            }
        }
        .content-wrapper {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 18px 46px rgba(8,47,73,0.14);
            padding: 32px;
            border: 1px solid #e2e8f0;
        }
        .page-header {
            display: none;
        }
        .page-header h1 {
            color: #0f172a;
            font-size: 1.8em;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .page-header p {
            color: #475569;
            font-size: 0.95em;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
            border-left: 4px solid;
            transition: all 0.3s ease;
            position: relative;
            cursor: help;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        /* Tooltip on hover */
        .stat-card:hover::after {
            content: attr(title);
            position: absolute;
            bottom: -45px;
            left: 50%;
            transform: translateX(-50%);
            background: #2c3e50;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.8em;
            white-space: nowrap;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            pointer-events: none;
        }
        .stat-card:hover::before {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-bottom: 6px solid #2c3e50;
            z-index: 100;
        }
        .stat-card.present { border-left-color: #22c55e; }
        .stat-card.late { border-left-color: #f59e0b; }
        .stat-card.absent { border-left-color: #ef4444; }
        .stat-card.excuse { border-left-color: #0ea5e9; }
        .stat-value {
            font-size: 2em;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .stat-card.present .stat-value { color: #22c55e; }
        .stat-card.late .stat-value { color: #f59e0b; }
        .stat-card.absent .stat-value { color: #ef4444; }
        .stat-card.excuse .stat-value { color: #0ea5e9; }
        .stat-label {
            color: #475569;
            font-size: 0.85em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.55px;
        }
        .content-section {
            background: #fff;
            border-radius: 12px;
            padding: 28px 32px;
            margin-bottom: 24px;
            box-shadow: 0 14px 40px rgba(8,47,73,0.12);
            border: 1px solid #e2e8f0;
        }
        .content-section h2 {
            color: #0f172a;
            font-size: 1.25em;
            font-weight: 800;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e0f2fe;
        }
        .search-bar {
            margin-bottom: 20px;
        }
        .search-bar input {
            width: 100%;
            max-width: 500px;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.95em;
            background: #f8f8f8;
            transition: all 0.3s;
        }
        .search-bar input:focus {
            outline: none;
            border-color: #0ea5e9;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(14,165,233,0.14);
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
        }
        table th {
            background: #e0f2fe;
            padding: 14px 12px;
            text-align: left;
            font-weight: 800;
            color: #0f172a;
            font-size: 0.86em;
            text-transform: uppercase;
            letter-spacing: 0.55px;
            border-bottom: 2px solid #d7e0eb;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #e8e8e8;
            font-size: 0.9em;
            background: #fff;
        }
        table tr:hover td {
            background: #f9f9f9;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9em;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #2d6a4f 0%, #1e5128 100%);
            color: #fff;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45,106,79,0.4);
        }
        iframe {
            width: 100%;
            height: 500px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        .dashboard-header {
            margin-bottom: 28px;
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
        }
        .greeting {
            font-size: 1.6em;
            font-weight: 700;
            color: #333;
            margin-bottom: 4px;
        }
        .greeting-time {
            font-size: 0.95em;
            color: #666;
            font-weight: 500;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .stat-label {
            font-size: 0.85em;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .stat-icon {
            font-size: 1.4em;
        }
        .stat-number {
            font-size: 2.2em;
            font-weight: 700;
            color: #1e5128;
            margin-bottom: 8px;
        }
        .stat-progress {
            width: 100%;
            height: 6px;
            background: #e8e8e8;
            border-radius: 3px;
            overflow: hidden;
        }
        .stat-progress-bar {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        .stat-card.present .stat-progress-bar {
            background: #27ae60;
        }
        .stat-card.absent .stat-progress-bar {
            background: #e74c3c;
        }
        .stat-card.late .stat-progress-bar {
            background: #f39c12;
        }
        .stat-card.excuse .stat-progress-bar {
            background: #3498db;
        }
        .quick-actions {
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 32px;
            border: 1px solid #e8e8e8;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .quick-actions h3 {
            font-size: 1.1em;
            font-weight: 700;
            color: #333;
            margin: 0 0 16px;
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 16px 12px;
            background: #f8f8f8;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 0.9em;
        }
        .action-btn:hover {
            border-color: #2d6a4f;
            background: #f0f0f0;
            color: #2d6a4f;
            transform: translateY(-2px);
        }
        .action-icon {
            font-size: 1.8em;
        }
        /* Responsive Design - Mobile First */
        @media (max-width: 768px) {
            body {
                display: block;
                padding-top: 0;
            }

            .sidebar {
                width: 100%;
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1000;
                min-height: auto;
                padding: 0;
                box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            }

            .sidebar-header {
                padding: 14px 20px;
                margin-bottom: 0;
                border-bottom: 1px solid rgba(255,255,255,0.15);
            }

            .sidebar-brand {
                gap: 10px;
                padding: 8px 16px 12px;
            }

            .sidebar-logo {
                width: 40px;
                height: 40px;
            }

            .teacher-profile {
                display: none;
            }

            .sidebar-menu {
                display: flex;
                flex-direction: row;
                padding: 0;
                overflow-x: auto;
                overflow-y: hidden;
            }

            .sidebar a {
                padding: 12px 20px;
                font-size: 0.9em;
                gap: 10px;
                border-left: none;
                border-bottom: 1px solid rgba(255,255,255,0.08);
                white-space: nowrap;
            }

            .menu-text {
                display: none;
            }

            .menu-badge {
                display: none;
            }

            .sidebar a:hover {
                padding-left: 20px;
            }

            .sidebar a.logout {
                margin-top: 0;
                padding-top: 12px;
            }

            .main {
                margin-left: 0;
                margin-top: 280px;
                width: 100%;
                padding: 16px;
            }

            .dashboard-header {
                margin-bottom: 16px;
            }

            .greeting {
                font-size: 1.4em;
                margin-bottom: 4px;
            }

            .page-header {
                padding: 20px;
                margin-bottom: 16px;
                border-radius: 12px;
            }

            .page-header h1 {
                font-size: 1.4em;
                margin-bottom: 8px;
            }

            .page-header p {
                font-size: 0.85em;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                margin-bottom: 16px;
            }

            .stat-card {
                padding: 12px;
            }

            .stat-number {
                font-size: 1.8em;
                margin-bottom: 6px;
            }

            .stat-progress {
                height: 4px;
            }

            .stat-label {
                font-size: 0.75em;
            }

            .quick-actions {
                margin-bottom: 20px;
                padding: 16px;
            }

            .quick-actions h3 {
                font-size: 0.95em;
                margin-bottom: 12px;
            }

            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .action-btn {
                padding: 12px 8px;
                font-size: 0.8em;
            }

            .action-icon {
                font-size: 1.5em;
            }

            /* Analytics responsive layout */
            .main-content { padding: 16px; }
            .analytics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; align-items: start; }
            .analytics-card { padding: 16px; }
            .analytics-metric { font-size: 1.4em; }
            .analytics-card[style*="grid-column"] { grid-column: auto; }
            canvas { max-width: 100%; height: auto !important; }

            /* Tablet */
            @media (max-width: 1024px) {
                .analytics-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            }

            /* Mobile */
            @media (max-width: 600px) {
                .analytics-grid { grid-template-columns: 1fr; gap: 12px; }
                .analytics-metric { font-size: 1.2em; }
            }

            .content-section {
                padding: 20px 16px;
                margin-bottom: 16px;
                border-radius: 12px;
            }

            .content-section h2 {
                font-size: 1.2em;
                margin-bottom: 16px;
                padding-bottom: 10px;
            }

            .search-bar input {
                max-width: 100%;
                font-size: 16px; /* Prevent iOS zoom */
                padding: 14px 16px;
            }

            table {
                font-size: 0.85em;
                border-radius: 8px;
            }

            table th {
                font-size: 0.75em;
                padding: 10px 8px;
            }

            table td {
                padding: 10px 8px;
                font-size: 0.85em;
            }

            .btn {
                font-size: 0.85em;
                padding: 10px 16px;
            }

            iframe {
                height: 400px;
                border-radius: 8px;
            }

            input, select, textarea {
                font-size: 16px !important; /* Prevent iOS zoom */
            }
        }

        @media (max-width: 480px) {
            .sidebar-brand {
                gap: 8px;
                padding: 6px 12px 10px;
            }

            .sidebar-text h2 {
                font-size: 0.95em;
            }

            .school-name {
                display: none;
            }

            .sidebar-logo {
                width: 36px;
                height: 36px;
            }

            .sidebar a {
                font-size: 0.8em;
                padding: 10px 12px;
            }

            .menu-icon {
                font-size: 1em;
            }

            .main {
                padding: 12px;
                margin-top: 240px;
            }

            .dashboard-header {
                margin-bottom: 12px;
            }

            .greeting {
                font-size: 1.2em;
                margin-bottom: 2px;
            }

            .greeting-time {
                font-size: 0.85em;
            }

            .page-header {
                padding: 12px;
            }

            .page-header h1 {
                font-size: 1.1em;
                margin-bottom: 4px;
            }

            .page-header p {
                font-size: 0.8em;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 8px;
                margin-bottom: 12px;
            }

            .stat-card {
                padding: 10px;
            }

            .stat-number {
                font-size: 1.5em;
                margin-bottom: 4px;
            }

            .stat-header {
                margin-bottom: 8px;
            }

            .stat-label {
                font-size: 0.7em;
            }

            .quick-actions {
                margin-bottom: 12px;
                padding: 12px;
            }

            .quick-actions h3 {
                font-size: 0.9em;
                margin-bottom: 10px;
            }

            .actions-grid {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .action-btn {
                padding: 10px;
                font-size: 0.75em;
            }

            .action-icon {
                font-size: 1.3em;
            }

            .content-section {
                padding: 12px;
                margin-bottom: 12px;
            }

            .content-section h2 {
                font-size: 1em;
                margin-bottom: 12px;
            }

            /* Extra small devices */
            .main-content { padding: 12px; }
            .analytics-card { padding: 12px 14px; }
            .analytics-metric { font-size: 1.2em; }
            .analytics-card h3 { font-size: 0.95em; }
            .insights-list li { font-size: 0.9em; padding: 8px 10px; }
            .mini-table th, .mini-table td { font-size: 0.85em; padding: 8px 6px; }
            canvas { max-width: 100% !important; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <picture>
                    <source type="image/webp" srcset="uploads/OIP (1)-72.webp 72w, uploads/OIP (1)-150.webp 150w, uploads/OIP (1)-300.webp 300w, uploads/OIP (1)-474.webp 474w">
                    <img src="<?php echo file_exists(__DIR__.'/uploads/OIP (1)-150.webp') ? 'uploads/OIP (1)-150.webp' : (file_exists(__DIR__.'/uploads/OIP (1).webp') ? 'uploads/OIP (1).webp' : '#'); ?>" srcset="uploads/OIP (1)-72.webp 72w, uploads/OIP (1)-150.webp 150w, uploads/OIP (1)-300.webp 300w, uploads/OIP (1)-474.webp 474w" sizes="120px" alt="PNS Logo" class="sidebar-logo">
                </picture>
                <div class="sidebar-text">
                    <h2>Teacher Portal</h2>
                    <p class="school-name">Palawan National School</p>
                </div>
            </div>
        </div>
        <div class="teacher-profile">
            <div class="teacher-avatar">👨‍🏫</div>
            <div class="teacher-info">
                <p class="teacher-name"><?php echo htmlspecialchars($user['full_name']); ?></p>
                <p class="teacher-role"><?php echo htmlspecialchars($user['faculty'] ?? 'Faculty'); ?></p>
            </div>
        </div>

        <div class="sidebar-menu">
            <a href="teacher_dashboard.php?section=scanner" class="<?php echo active('scanner', $section); ?>">
                <span class="menu-icon">📷</span>
                <span class="menu-text">Scanner</span>
            </a>
            <a href="teacher_dashboard.php?section=dashboard" class="<?php echo active('dashboard', $section); ?>">
                <span class="menu-icon">📊</span>
                <span class="menu-text">Dashboard</span>
                <span class="menu-badge">Overview</span>
            </a>
            <a href="teacher_dashboard.php?section=today" class="<?php echo active('today', $section); ?>">
                <span class="menu-icon">📅</span>
                <span class="menu-text">Today's Attendance</span>
                <span class="menu-badge">Live</span>
            </a>
            <a href="teacher_dashboard.php?section=student_list" class="<?php echo active('student_list', $section); ?>">
                <span class="menu-icon">👥</span>
                <span class="menu-text">Student List</span>
                <span class="menu-badge" id="student-count">0</span>
            </a>
            <a href="teacher_dashboard.php?section=analytics" class="<?php echo active('analytics', $section); ?>">
                <span class="menu-icon">📈</span>
                <span class="menu-text">Analytics</span>
                <span class="menu-badge">New</span>
            </a>
            <a href="teacher_dashboard.php?section=corrections" class="<?php echo active('corrections', $section); ?>">
                <span class="menu-icon">✏️</span>
                <span class="menu-text">Corrections</span>
                <span class="menu-badge">Edit</span>
            </a>
        </div>

        <a href="logout.php" class="logout">🚪 Logout</a>
    </div>
    <div class="main">
        <div class="main-header">
            <div>
                <h1><?php echo $section === 'scanner' ? '📷 Attendance Scanner' : ($section === 'today' ? '📅 Today\'s Attendance' : ($section === 'student_list' ? '👥 Student List' : ($section === 'analytics' ? '📈 Analytics' : ($section === 'corrections' ? '✏️ Attendance Corrections' : '📊 Dashboard')))); ?></h1>
                <p class="main-header-stats">
                    📅 <?php echo get_dashboard_display_date(); ?>
                    <?php
                    $reset_info = get_time_until_reset();
                    if ($reset_info['is_next_day_mode']): ?>
                        <span style="background: #ff9800; color: white; padding: 4px 12px; border-radius: 12px; margin-left: 10px; font-size: 0.85em;">
                            🌙 Next Day Mode
                        </span>
                    <?php else: ?>
                        <span style="background: #4caf50; color: white; padding: 4px 12px; border-radius: 12px; margin-left: 10px; font-size: 0.85em;">
                            ☀️ Active
                        </span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <div class="main-content">
        <?php if($section === 'scanner'): ?>
            <div class="content-section">
                <!-- Scanner Mode Selection Dialog -->
                <div id="scannerModeDialog" style="display: flex; align-items: center; justify-content: center; min-height: 400px;">
                    <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 500px; text-align: center;">
                        <h2 style="margin: 0 0 20px; color: #218c21; font-size: 2em;">📷 Scanner Mode</h2>
                        <p style="margin-bottom: 30px; color: #666; font-size: 1.1em;">Choose how you want to scan attendance:</p>
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <button onclick="enableFullscreenCamera()" style="padding: 20px; font-size: 1.1em; font-weight: 600; background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; border: none; border-radius: 12px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(39,174,96,0.3);">
                                🎥 Fullscreen Camera Mode
                            </button>
                            <button onclick="loadIframeScanner()" style="padding: 20px; font-size: 1.1em; font-weight: 600; background: linear-gradient(135deg, #3498db, #2980b9); color: white; border: none; border-radius: 12px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(52,152,219,0.3);">
                                📱 Standard Scanner
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Fullscreen Camera Container (hidden initially) -->
                <div id="fullscreenCameraContainer" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: #000; z-index: 9999;">
                    <div id="cameraView" style="width: 100%; height: 100%; position: relative;">
                        <video id="cameraPreview" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>

                        <!-- Sync Status Indicator -->
                        <div style="position: absolute; top: 20px; left: 20px; background: rgba(0,0,0,0.7); color: white; padding: 10px 16px; border-radius: 8px; font-size: 0.9em; display: flex; align-items: center; gap: 8px;">
                            <span id="syncStatus" style="display: inline-block; width: 8px; height: 8px; background: #27ae60; border-radius: 50%; animation: pulse 2s infinite;"></span>
                            <span id="syncText">Online</span>
                        </div>
                        <style>
                            @keyframes pulse {
                                0%, 100% { opacity: 1; }
                                50% { opacity: 0.5; }
                            }
                        </style>

                        <!-- Exit Button -->
                        <button onclick="exitFullscreenCamera()" style="position: absolute; top: 20px; right: 20px; padding: 12px 24px; background: rgba(231,76,60,0.9); color: white; border: none; border-radius: 8px; font-size: 1.1em; font-weight: 600; cursor: pointer; z-index: 10000; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                            ✕ Exit
                        </button>

                        <!-- Manual Input Box -->
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.8); padding: 30px; backdrop-filter: blur(10px);">
                            <div style="max-width: 600px; margin: 0 auto;">
                                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                    <div style="flex: 1;">
                                        <label style="display: block; color: white; margin-bottom: 8px; font-size: 0.95em; font-weight: 600;">Period:</label>
                                        <select id="attendancePeriod" style="width: 100%; padding: 10px; border: 2px solid #27ae60; border-radius: 6px; font-size: 1em; background: white; color: #2c3e50;">
                                            <option value="morning">🌅 Morning (6:00-12:00)</option>
                                            <option value="afternoon">🌆 Afternoon (12:00-17:00)</option>
                                            <option value="evening">🌙 Evening (17:00-22:00)</option>
                                        </select>
                                    </div>
                                </div>
                                <label style="display: block; color: white; margin-bottom: 10px; font-size: 1.1em; font-weight: 600;">Student ID:</label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" id="manualStudentId" placeholder="Enter Student ID or scan QR code..." style="flex: 1; padding: 15px; font-size: 1.1em; border: 2px solid #27ae60; border-radius: 8px; outline: none;" onkeypress="if(event.key==='Enter') recordManualAttendance()">
                                    <button onclick="recordManualAttendance()" style="padding: 15px 30px; background: #27ae60; color: white; border: none; border-radius: 8px; font-size: 1.1em; font-weight: 600; cursor: pointer; white-space: nowrap;">
                                        ✓ Record
                                    </button>
                                </div>
                                <div id="scanStatus" style="margin-top: 15px; color: white; text-align: center; font-size: 0.95em;"></div>
                            </div>
                        </div>

                        <!-- Audio for success notification -->
                        <audio id="successSound" preload="auto">
                            <source src="data:audio/wav;base64,UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAAB9AAACABAAZGF0YQIAAAAAAA==" type="audio/wav">
                        </audio>

                        <!-- Success Popup -->
                        <div id="successPopup" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: linear-gradient(135deg, #27ae60, #2ecc71); padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); text-align: center; min-width: 400px; z-index: 10001;">
                            <h2 id="popupTitle" style="margin: 0 0 15px; color: white; font-size: 1.8em;">✅ Success!</h2>
                            <p id="popupMessage" style="margin: 0; color: white; font-size: 1.2em; line-height: 1.6;"></p>
                        </div>
                    </div>
                </div>

                <!-- Standard iframe scanner (hidden initially) -->
                <iframe id="scannerFrame" src="" style="display: none; width: 100%; height: 600px; border: none; border-radius: 12px;"></iframe>

                <script src="html5-qrcode.min.js"></script>
                <script>
                let html5QrCode = null;
                let cameraStream = null;

                function loadIframeScanner() {
                    document.getElementById('scannerModeDialog').style.display = 'none';
                    document.getElementById('scannerFrame').style.display = 'block';
                    document.getElementById('scannerFrame').src = 'scan.php';
                }

                async function enableFullscreenCamera() {
                    document.getElementById('scannerModeDialog').style.display = 'none';
                    document.getElementById('fullscreenCameraContainer').style.display = 'block';

                    try {
                        // Get camera stream
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'environment', width: 1920, height: 1080 }
                        });
                        cameraStream = stream;
                        document.getElementById('cameraPreview').srcObject = stream;

                        // Initialize QR scanner
                        html5QrCode = new Html5Qrcode("cameraPreview");

                        Html5Qrcode.getCameras().then(cameras => {
                            if (cameras && cameras.length > 0) {
                                const cameraId = cameras[cameras.length - 1].id; // Prefer back camera

                                html5QrCode.start(
                                    cameraId,
                                    { fps: 10, qrbox: { width: 300, height: 300 } },
                                    (decodedText) => {
                                        // QR code scanned
                                        document.getElementById('manualStudentId').value = extractStudentId(decodedText);
                                        recordManualAttendance();
                                    },
                                    (errorMessage) => {
                                        // Scan error (ignore)
                                    }
                                );
                            }
                        });

                        document.getElementById('manualStudentId').focus();
                    } catch (err) {
                        alert('Camera access denied: ' + err.message);
                        exitFullscreenCamera();
                    }
                }

                // Sync status tracking
                let lastSyncTime = Date.now();
                let offlineQueueCount = 0;

                function updateSyncStatus() {
                    const now = Date.now();
                    const timeSinceSync = Math.floor((now - lastSyncTime) / 1000);
                    const syncStatusEl = document.getElementById('syncStatus');
                    const syncTextEl = document.getElementById('syncText');

                    if (timeSinceSync < 60) {
                        syncStatusEl.style.background = '#27ae60';
                        syncTextEl.textContent = 'Online';
                    } else if (timeSinceSync < 300) {
                        syncStatusEl.style.background = '#f39c12';
                        const mins = Math.floor(timeSinceSync / 60);
                        syncTextEl.textContent = `Last synced: ${mins}m ago`;
                    } else {
                        syncStatusEl.style.background = '#e74c3c';
                        syncTextEl.textContent = 'Offline - Queuing scans';
                    }

                    if (offlineQueueCount > 0) {
                        syncTextEl.textContent += ` (${offlineQueueCount} pending)`;
                    }
                }

                // Update sync status every 5 seconds
                setInterval(updateSyncStatus, 5000);

                function extractStudentId(qrData) {
                    try {
                        const parsed = JSON.parse(qrData);
                        return parsed.student_id || parsed.id || qrData.trim();
                    } catch (e) {
                        return qrData.trim();
                    }
                }

                function exitFullscreenCamera() {
                    if (html5QrCode) {
                        html5QrCode.stop().catch(err => console.error(err));
                    }
                    if (cameraStream) {
                        cameraStream.getTracks().forEach(track => track.stop());
                    }
                    document.getElementById('fullscreenCameraContainer').style.display = 'none';
                    document.getElementById('scannerModeDialog').style.display = 'flex';
                }

                function recordManualAttendance() {
                    const studentId = document.getElementById('manualStudentId').value.trim();
                    if (!studentId) {
                        document.getElementById('scanStatus').textContent = '⚠️ Please enter a student ID';
                        return;
                    }

                    // Duplicate scan prevention
                    const recentScans = JSON.parse(sessionStorage.getItem('recentScans') || '{}');
                    const now = Date.now();
                    const thirtySecondsAgo = now - 30000;

                    if (recentScans[studentId] && recentScans[studentId] > thirtySecondsAgo) {
                        const timeSince = Math.round((now - recentScans[studentId]) / 1000);
                        document.getElementById('scanStatus').textContent = `⚠️ Student already scanned ${timeSince}s ago`;
                        setTimeout(() => {
                            document.getElementById('manualStudentId').value = '';
                            document.getElementById('scanStatus').textContent = '';
                            document.getElementById('manualStudentId').focus();
                        }, 2000);
                        return;
                    }

                    document.getElementById('scanStatus').textContent = '⏳ Recording...';

                    const selectedPeriod = document.getElementById('attendancePeriod') ? document.getElementById('attendancePeriod').value : 'morning';

                    fetch('record_attendance.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            code: studentId,
                            type: 'manual',
                            period: selectedPeriod
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        // Update sync status on successful response
                        lastSyncTime = Date.now();
                        offlineQueueCount = Math.max(0, offlineQueueCount - 1);
                        updateSyncStatus();

                        if (data.success) {
                            const student = data.student_info;

                            // Record successful scan
                            recentScans[studentId] = now;
                            // Clean up old scans (older than 30 seconds)
                            Object.keys(recentScans).forEach(id => {
                                if (recentScans[id] < thirtySecondsAgo) delete recentScans[id];
                            });
                            sessionStorage.setItem('recentScans', JSON.stringify(recentScans));

                            showSuccessPopup(
                                `Attendance for ${student.full_name}`,
                                `Grade ${student.grade_level} - ${student.strand} - ${student.section_block}<br>Status: ${data.attendance_info.status}`
                            );

                            document.getElementById('scanStatus').textContent = '';

                            // Clear textbox after 2 seconds (when popup closes)
                            setTimeout(() => {
                                document.getElementById('manualStudentId').value = '';
                                document.getElementById('manualStudentId').focus();
                            }, 2000);
                        } else {
                            document.getElementById('scanStatus').textContent = '❌ ' + (data.error || 'Student not found');
                            // Clear textbox after 1 second on error
                            setTimeout(() => {
                                document.getElementById('manualStudentId').value = '';
                                document.getElementById('scanStatus').textContent = '';
                                document.getElementById('manualStudentId').focus();
                            }, 1000);
                        }
                    })
                    .catch(err => {
                        // Network error - add to offline queue
                        offlineQueueCount++;
                        updateSyncStatus();

                        document.getElementById('scanStatus').textContent = '❌ Network error - saved for later';
                        // Clear textbox after 1 second on error
                        setTimeout(() => {
                            document.getElementById('manualStudentId').value = '';
                            document.getElementById('scanStatus').textContent = '';
                            document.getElementById('manualStudentId').focus();
                        }, 1000);
                    });
                }

                function playSuccessSound() {
                    const audio = document.getElementById('successSound');
                    if (audio) {
                        audio.currentTime = 0;
                        audio.play().catch(err => console.log('Audio play failed:', err));
                    }
                }

                function showSuccessPopup(title, message) {
                    document.getElementById('popupTitle').textContent = '✅ ' + title;
                    document.getElementById('popupMessage').innerHTML = message;
                    document.getElementById('successPopup').style.display = 'block';

                    // Play success sound
                    playSuccessSound();

                    // Auto-hide after 2 seconds
                    setTimeout(() => {
                        document.getElementById('successPopup').style.display = 'none';
                    }, 2000);
                }

                window.addEventListener('message', function(e) {
                    if(e.data === 'refreshAttendance') {
                        console.log('Attendance recorded successfully');
                    }
                });
                </script>
            </div>
        <?php elseif($section === 'dashboard'): ?>
            <?php
            // Get teacher's advisory group
            $grade = $user['grade_level'];
            $strand = $user['strand'];
            $block = $user['section_block'];

            // Time-based greeting
            $hour = date('H');
            if ($hour < 12) {
                $greeting = "Good Morning";
            } elseif ($hour < 18) {
                $greeting = "Good Afternoon";
            } else {
                $greeting = "Good Evening";
            }

            // Get reset info for display
            $reset_info = get_time_until_reset();
            $status_msg = get_attendance_status_message();
            ?>

            <?php if ($is_next_day): ?>
            <div style="background: linear-gradient(135deg, #ff9800, #f57c00); color: white; padding: 16px 24px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(255,152,0,0.3); display: flex; align-items: center; gap: 15px;">
                <span style="font-size: 2em;">🌙</span>
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 1.1em; margin-bottom: 4px;">Next Day Preparation Mode Active</div>
                    <div style="opacity: 0.95; font-size: 0.95em;">System is now tracking attendance for <strong><?php echo date('F j, Y', strtotime($attendance_date)); ?></strong>. Attendance will be active at 6:00 AM.</div>
                </div>
            </div>
            <?php else: ?>
            <div style="background: linear-gradient(135deg, #4caf50, #45a049); color: white; padding: 16px 24px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(76,175,80,0.3); display: flex; align-items: center; gap: 15px;">
                <span style="font-size: 2em;">☀️</span>
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 1.1em; margin-bottom: 4px;">Attendance Tracking Active</div>
                    <div style="opacity: 0.95; font-size: 0.95em;">Currently tracking for <strong><?php echo date('F j, Y', strtotime($attendance_date)); ?></strong>. System resets at 7:00 PM (<?php echo $reset_info['hours']; ?>h <?php echo $reset_info['minutes']; ?>m remaining).</div>
                </div>
            </div>
            <?php endif; ?>

            <div class="dashboard-header">
                <div class="greeting"><?php echo $greeting; ?>, <?php echo htmlspecialchars($user['full_name']); ?>! 👋</div>
                <div class="greeting-time">Grade <?php echo htmlspecialchars($grade); ?> - <?php echo htmlspecialchars($strand); ?> - Section <?php echo htmlspecialchars($block); ?></div>
            </div>
            <?php
            // Get all students in this group
            $students = $pdo->prepare('SELECT * FROM students WHERE grade_level = :grade AND strand = :strand AND section_block = :block');
            $students->execute([':grade'=>$grade, ':strand'=>$strand, ':block'=>$block]);
            $studentList = $students->fetchAll(PDO::FETCH_ASSOC);
            $studentIds = array_column($studentList, 'student_id');
            $totalStudents = count($studentList);
            // Get attendance records for current tracking date (auto-adjusts after 7PM)
            $attendance = $pdo->prepare('SELECT * FROM attendance_records WHERE attendance_date = :date AND student_id IN ("' . implode('","', $studentIds) . '")');
            $attendance->execute([':date'=>$attendance_date]);
            $present = $absent = $late = $excuse = $morning_half = $afternoon_half = 0;
            foreach($attendance->fetchAll(PDO::FETCH_ASSOC) as $row) {
                switch($row['status']) {
                    case 'present': $present++; break;
                    case 'absent': $absent++; break;
                    case 'late': $late++; break;
                    case 'excuse': $excuse++; break;
                    case 'morning_half_day': $morning_half++; break;
                    case 'afternoon_half_day': $afternoon_half++; break;
                }
            }

            // Calculate percentages
            $marked = $present + $absent + $late + $excuse + $morning_half + $afternoon_half;
            $presentPct = $marked > 0 ? round(($present / $marked) * 100) : 0;
            $latePct = $marked > 0 ? round(($late / $marked) * 100) : 0;
            $absentPct = $marked > 0 ? round(($absent / $marked) * 100) : 0;
            $excusePct = $marked > 0 ? round(($excuse / $marked) * 100) : 0;

            // Get yesterday's data for trend comparison
            $yesterday = date('Y-m-d', strtotime($attendance_date . ' -1 day'));
            $yesterdayAttendance = $pdo->prepare('SELECT * FROM attendance_records WHERE attendance_date = :date AND student_id IN ("' . implode('","', $studentIds) . '")');
            $yesterdayAttendance->execute([':date'=>$yesterday]);
            $yesterdayPresent = $yesterdayAbsent = $yesterdayLate = 0;
            foreach($yesterdayAttendance->fetchAll(PDO::FETCH_ASSOC) as $row) {
                switch($row['status']) {
                    case 'present': $yesterdayPresent++; break;
                    case 'absent': $yesterdayAbsent++; break;
                    case 'late': $yesterdayLate++; break;
                }
            }

            // Calculate trend deltas
            $presentDelta = $present - $yesterdayPresent;
            $absentDelta = $absent - $yesterdayAbsent;
            $lateDelta = $late - $yesterdayLate;
            ?>
            <div class="stats-grid">
                <div class="stat-card present" title="<?php echo $presentDelta > 0 ? '+' . $presentDelta . ' more than yesterday' : ($presentDelta < 0 ? $presentDelta . ' fewer than yesterday' : 'Same as yesterday'); ?>">
                    <div class="stat-header">
                        <div class="stat-label">Present</div>
                        <div class="stat-icon">✓</div>
                    </div>
                    <div class="stat-number"><?php echo $present; ?></div>
                    <div style="font-size: 0.8em; color: #27ae60; margin: 4px 0; font-weight: 600;">
                        <?php echo $presentDelta > 0 ? '▲ +' . $presentDelta : ($presentDelta < 0 ? '▼ ' . $presentDelta : '→ 0'); ?>
                    </div>
                    <div class="stat-progress">
                        <div class="stat-progress-bar" style="width: <?php echo $presentPct; ?>%"></div>
                    </div>
                    <div class="stat-label" style="margin-top: 8px; color: #27ae60;"><?php echo $presentPct; ?>%</div>
                </div>
                <div class="stat-card late" title="<?php echo $lateDelta > 0 ? '+' . $lateDelta . ' more than yesterday' : ($lateDelta < 0 ? $lateDelta . ' fewer than yesterday' : 'Same as yesterday'); ?>">
                    <div class="stat-header">
                        <div class="stat-label">Late</div>
                        <div class="stat-icon">⏱️</div>
                    </div>
                    <div class="stat-number"><?php echo $late; ?></div>
                    <div style="font-size: 0.8em; color: #f39c12; margin: 4px 0; font-weight: 600;">
                        <?php echo $lateDelta > 0 ? '▲ +' . $lateDelta : ($lateDelta < 0 ? '▼ ' . $lateDelta : '→ 0'); ?>
                    </div>
                    <div class="stat-progress">
                        <div class="stat-progress-bar" style="width: <?php echo $latePct; ?>%"></div>
                    </div>
                    <div class="stat-label" style="margin-top: 8px; color: #f39c12;"><?php echo $latePct; ?>%</div>
                </div>
                <div class="stat-card absent" title="<?php echo $absentDelta > 0 ? '+' . $absentDelta . ' more than yesterday' : ($absentDelta < 0 ? $absentDelta . ' fewer than yesterday' : 'Same as yesterday'); ?>">
                    <div class="stat-header">
                        <div class="stat-label">Absent</div>
                        <div class="stat-icon">✕</div>
                    </div>
                    <div class="stat-number"><?php echo $absent; ?></div>
                    <div style="font-size: 0.8em; color: #e74c3c; margin: 4px 0; font-weight: 600;">
                        <?php echo $absentDelta > 0 ? '▲ +' . $absentDelta : ($absentDelta < 0 ? '▼ ' . $absentDelta : '→ 0'); ?>
                    </div>
                    <div class="stat-progress">
                        <div class="stat-progress-bar" style="width: <?php echo $absentPct; ?>%"></div>
                    </div>
                    <div class="stat-label" style="margin-top: 8px; color: #e74c3c;"><?php echo $absentPct; ?>%</div>
                </div>
                <div class="stat-card excuse">
                    <div class="stat-header">
                        <div class="stat-label">Excused</div>
                        <div class="stat-icon">📝</div>
                    </div>
                    <div class="stat-number"><?php echo $excuse; ?></div>
                    <div class="stat-progress">
                        <div class="stat-progress-bar" style="width: <?php echo $excusePct; ?>%"></div>
                    </div>
                    <div class="stat-label" style="margin-top: 8px; color: #3498db;"><?php echo $excusePct; ?>%</div>
                </div>
            </div>


            <div class="content-section">
                <h2>Student Search</h2>
                <form method="get" class="search-bar">
                    <input type="hidden" name="section" value="dashboard">
                    <input type="text" name="search" placeholder="🔍 Search student by name or ID..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </form>
            </div>
            <div class="content-section">
                <h2>📋 Student Directory</h2>
                <?php
                $search = trim($_GET['search'] ?? '');
                $filtered = $studentList;
                if ($search) {
                    $filtered = array_filter($studentList, function($s) use ($search) {
                        return stripos($s['full_name'], $search) !== false || stripos($s['student_id'], $search) !== false;
                    });
                }
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Gender</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($filtered) === 0): ?>
                        <tr><td colspan="3" style="padding:20px;text-align:center;color:#e74c3c">No students found.</td></tr>
                        <?php else: foreach($filtered as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($s['gender'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif($section === 'today'): ?>
            <div class="content-section" style="text-align:right;padding:16px 32px;margin-bottom:16px;">
                <button id="printSF2Btn" class="btn btn-primary">🖨️ Print SF2</button>

                <!-- Export Format Modal -->
                <div id="sf2ExportModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9998; align-items: center; justify-content: center;">
                    <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-width: 500px; text-align: center;">
                        <h2 style="margin: 0 0 20px; color: #218c21; font-size: 2em;">📊 Export SF2</h2>
                        <p style="margin-bottom: 30px; color: #666; font-size: 1.1em;">Choose export format:</p>
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <button onclick="exportSF2Excel()" style="padding: 20px; font-size: 1.1em; font-weight: 600; background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; border: none; border-radius: 12px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(39,174,96,0.3);">
                                📄 Excel Format (XLS)
                            </button>
                            <button onclick="exportSF2PDF()" style="padding: 20px; font-size: 1.1em; font-weight: 600; background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border: none; border-radius: 12px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(231,76,60,0.3);">
                                📃 PDF Format
                            </button>
                        </div>
                        <button onclick="closeSF2Modal()" style="margin-top: 20px; padding: 12px 24px; background: #95a5a6; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                            ✕ Cancel
                        </button>
                    </div>
                </div>

                <script>
                const sf2Params = {
                    month: new Date().toISOString().slice(0,7),
                    strand: '<?php echo addslashes($user['strand']); ?>',
                    block: '<?php echo addslashes($user['section_block']); ?>',
                    grade_level: '<?php echo addslashes($user['grade_level']); ?>',
                    teacher_name: '<?php echo addslashes($user['full_name']); ?>'
                };

                document.getElementById('printSF2Btn').onclick = function() {
                    document.getElementById('sf2ExportModal').style.display = 'flex';
                };

                function closeSF2Modal() {
                    document.getElementById('sf2ExportModal').style.display = 'none';
                }

                function exportSF2Excel() {
                    var params = new URLSearchParams(sf2Params);
                    window.location.href = 'export_sf2_excel.php?' + params.toString();
                    closeSF2Modal();
                }

                function exportSF2PDF() {
                    var params = new URLSearchParams(sf2Params);
                    window.open('print_monthly_sf2.php?' + params.toString(), '_blank');
                    closeSF2Modal();
                }

                // Close modal when clicking outside
                document.getElementById('sf2ExportModal').onclick = function(e) {
                    if (e.target === this) closeSF2Modal();
                };
                </script>
            </div>
            <?php
            // Get teacher's advisory group
            $grade = $user['grade_level'];
            $strand = $user['strand'];
            $block = $user['section_block'];
            // Get all students in this group
            $students = $pdo->prepare('SELECT * FROM students WHERE grade_level = :grade AND strand = :strand AND section_block = :block');
            $students->execute([':grade'=>$grade, ':strand'=>$strand, ':block'=>$block]);
            $studentList = $students->fetchAll(PDO::FETCH_ASSOC);
            // Group by gender and sort alphabetically
            $males = array_filter($studentList, function($s){ return strtolower($s['gender']) === 'male'; });
            $females = array_filter($studentList, function($s){ return strtolower($s['gender']) === 'female'; });
            usort($males, function($a,$b){ return strcmp($a['full_name'], $b['full_name']); });
            usort($females, function($a,$b){ return strcmp($a['full_name'], $b['full_name']); });
            // Get attendance records for current tracking date (auto-adjusts after 7PM)
            $attendance = $pdo->prepare('SELECT * FROM attendance_records WHERE attendance_date = :date');
            $attendance->execute([':date'=>$attendance_date]);
            $attendanceData = [];
            foreach($attendance->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $attendanceData[$row['student_id']][] = $row;
            }
            function getPeriodCol($record, $col) {
                if (!empty($record[$col])) {
                    // Extract only time part (HH:MM:SS)
                    $dt = date_create($record[$col]);
                    return $dt ? date_format($dt, 'H:i:s') : $record[$col];
                }
                return '';
            }
            function getScanTime($records) {
                foreach($records as $r) {
                    if($r['time_period'] === 'scan') return $r['attendance_time'];
                }
                return '';
            }
            function getStatus($records) {
                return $records[0]['status'] ?? 'absent';
            }
            function getStatusDisplay($status) {
                // Return styled badge markup
                switch($status) {
                    case 'present':
                        return '<span class="status-badge status-present">Present</span>';
                    case 'morning_half_day':
                        return '<span class="status-badge status-morning_half_day">Morning Half Day</span>';
                    case 'afternoon_half_day':
                        return '<span class="status-badge status-afternoon_half_day">Afternoon Half Day</span>';
                    case 'late':
                        return '<span class="status-badge status-late">Late</span>';
                    case 'excuse':
                        return '<span class="status-badge status-excuse">Excused</span>';
                    case 'absent':
                    default:
                        return '<span class="status-badge status-absent">Absent</span>';
                }
            }
            ?>
            <style>
            /* Attendance table redesign */
            .attendance-table {width:100%;border-collapse:collapse;background:#fff;box-shadow:0 4px 16px rgba(0,0,0,0.08);border-radius:12px;overflow:hidden;}
            .attendance-table thead th {background:#1e5128;color:#fff;padding:12px 10px;font-size:0.8em;letter-spacing:.5px;text-transform:uppercase;border:1px solid #1e5128;font-weight:700;}
            .attendance-table tbody tr {background:#ffffff;}
            .attendance-table tbody td {padding:12px 10px;border:1px solid #e8f5e9;font-size:0.9em;}
            .attendance-table tr.gender-header td {background:linear-gradient(90deg,#2d6a4f,#52b788);color:#fff;font-weight:700;letter-spacing:.5px;font-size:0.95em;padding:10px 12px;}
            .status-badge {display:inline-block;padding:5px 12px;border-radius:20px;font-size:0.8em;font-weight:700;letter-spacing:.3px;}
            .status-present {background:#d8f3dc;color:#1b5e20;}
            .status-late {background:#fff4d5;color:#b26a00;}
            .status-absent {background:#ffe3e3;color:#b00020;}
            .status-excuse {background:#e0f7fa;color:#006064;}
            .status-morning_half_day {background:#e8f0fe;color:#1e40af;}
            .status-afternoon_half_day {background:#ede7f6;color:#4a148c;}
            .attendance-actions form {display:flex;flex-wrap:wrap;gap:6px;align-items:center;}
            .attendance-actions select {padding:8px 10px;border:2px solid #d8f3dc;border-radius:8px;background:#f6fff7;font-size:0.85em;transition:all 0.3s;}
            .attendance-actions select:focus {border-color:#2d6a4f;outline:none;box-shadow:0 0 0 3px rgba(45,106,79,0.1);}
            .attendance-actions button {background:linear-gradient(135deg,#2d6a4f 0%,#1e5128 100%);color:#fff;border:none;padding:8px 16px;border-radius:8px;cursor:pointer;font-weight:700;font-size:0.85em;transition:all 0.3s;}
            .attendance-actions button:hover {transform:translateY(-2px);box-shadow:0 4px 12px rgba(45,106,79,0.4);}

            @media (max-width: 1024px){
                .attendance-table {font-size:0.9em;}
                .attendance-table thead th {padding:10px 8px;font-size:0.75em;}
                .attendance-table tbody td {padding:10px 8px;}
            }

            @media (max-width: 768px){
                .attendance-table {display:block;overflow-x:auto;-webkit-overflow-scrolling:touch;font-size:0.85em;}
                .attendance-table thead th {font-size:0.7em;padding:8px 6px;}
                .attendance-table tbody td {padding:8px 6px;white-space:nowrap;}
                .attendance-actions select {font-size:0.8em;padding:6px 8px;}
                .attendance-actions button {font-size:0.8em;padding:6px 12px;}
            }

            @media (max-width: 600px){
                .attendance-table thead {display:none;}
                .attendance-table tbody {display:block;}
                .attendance-table tbody tr {
                    display:grid;
                    grid-template-columns:1fr 1fr;
                    gap:10px;
                    padding:16px;
                    margin-bottom:12px;
                    background:#fff;
                    border:2px solid #d8f3dc;
                    border-radius:12px;
                    box-shadow:0 2px 8px rgba(0,0,0,0.06);
                }
                .attendance-table tbody tr.gender-header {
                    display:block;
                    grid-template-columns:1fr;
                    padding:10px 16px;
                    border-radius:8px;
                    margin-bottom:8px;
                }
                .attendance-table tbody td {
                    position:relative;
                    padding:8px 0;
                    border:none;
                    display:flex;
                    flex-direction:column;
                    gap:4px;
                }
                .attendance-table tbody td[data-label]:before {
                    content:attr(data-label);
                    display:block;
                    font-size:0.7em;
                    font-weight:700;
                    color:#2d6a4f;
                    text-transform:uppercase;
                    letter-spacing:.5px;
                    margin-bottom:4px;
                }
                .attendance-table tbody td:nth-child(1),
                .attendance-table tbody td:nth-child(2) {
                    grid-column:1 / -1;
                }
                .attendance-actions {
                    grid-column:1 / -1;
                }
                .attendance-actions form {
                    display:flex;
                    gap:8px;
                    width:100%;
                }
                .attendance-actions select {
                    flex:1;
                    font-size:14px;
                    padding:10px;
                }
                .attendance-actions button {
                    padding:10px 20px;
                    font-size:0.9em;
                }
            }
            </style>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Gender</th>
                        <th>Morning In</th>
                        <th>Morning Out</th>
                        <th>Afternoon In</th>
                        <th>Afternoon Out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <tr class="gender-header"><td colspan="9">Male</td></tr>
                <?php foreach($males as $s):
                    $records = $attendanceData[$s['student_id']] ?? [];
                ?>
                <tr>
                    <td data-label="Student ID"><?php echo htmlspecialchars($s['student_id']); ?></td>
                    <td data-label="Full Name"><?php echo htmlspecialchars($s['full_name']); ?></td>
                    <td data-label="Gender">Male</td>
                    <td data-label="Morning In"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_in')); ?></td>
                    <td data-label="Morning Out"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_out')); ?></td>
                    <td data-label="Afternoon In"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_in')); ?></td>
                    <td data-label="Afternoon Out"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_out')); ?></td>
                    <td data-label="Status"><?php echo getStatusDisplay(getStatus($records)); ?></td>
                    <td data-label="Actions" class="attendance-actions">
                        <form method="post" action="update_attendance_status.php">
                            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($s['student_id']); ?>">
                            <select name="status">
                                <option value="present" <?php if(getStatus($records)==='present') echo 'selected'; ?>>Present</option>
                                <option value="morning_half_day" <?php if(getStatus($records)==='morning_half_day') echo 'selected'; ?>>Morning Half Day</option>
                                <option value="afternoon_half_day" <?php if(getStatus($records)==='afternoon_half_day') echo 'selected'; ?>>Afternoon Half Day</option>
                                <option value="absent" <?php if(getStatus($records)==='absent') echo 'selected'; ?>>Absent</option>
                                <option value="late" <?php if(getStatus($records)==='late') echo 'selected'; ?>>Late</option>
                                <option value="excuse" <?php if(getStatus($records)==='excuse') echo 'selected'; ?>>Excuse</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr class="gender-header"><td colspan="9">Female</td></tr>
                <?php foreach($females as $s):
                    $records = $attendanceData[$s['student_id']] ?? [];
                ?>
                <tr>
                    <td data-label="Student ID"><?php echo htmlspecialchars($s['student_id']); ?></td>
                    <td data-label="Full Name"><?php echo htmlspecialchars($s['full_name']); ?></td>
                    <td data-label="Gender">Female</td>
                    <td data-label="Morning In"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_in')); ?></td>
                    <td data-label="Morning Out"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'morning_out')); ?></td>
                    <td data-label="Afternoon In"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_in')); ?></td>
                    <td data-label="Afternoon Out"><?php echo htmlspecialchars(getPeriodCol($records[0] ?? [], 'afternoon_out')); ?></td>
                    <td data-label="Status"><?php echo getStatusDisplay(getStatus($records)); ?></td>
                    <td data-label="Actions" class="attendance-actions">
                        <form method="post" action="update_attendance_status.php">
                            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($s['student_id']); ?>">
                            <select name="status">
                                <option value="present" <?php if(getStatus($records)==='present') echo 'selected'; ?>>Present</option>
                                <option value="morning_half_day" <?php if(getStatus($records)==='morning_half_day') echo 'selected'; ?>>Morning Half Day</option>
                                <option value="afternoon_half_day" <?php if(getStatus($records)==='afternoon_half_day') echo 'selected'; ?>>Afternoon Half Day</option>
                                <option value="late" <?php if(getStatus($records)==='late') echo 'selected'; ?>>Late</option>
                                <option value="excuse" <?php if(getStatus($records)==='excuse') echo 'selected'; ?>>Excuse</option>
                                <option value="absent" <?php if(getStatus($records)==='absent') echo 'selected'; ?>>Absent</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif($section === 'student_list'): ?>
            <div class="page-header">
                <h1>👥 Student List</h1>
                <p>Manage students in your class</p>
                <a href="import_students.php" style="margin-left: auto; background: #27ae60; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;">📥 Batch Import CSV</a>
            </div>

            <!-- Filter & Search Section -->
            <div class="content-section" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); margin-bottom: 20px; padding: 20px 24px;">
                <h3 style="margin-top: 0; color: #2c3e50; font-size: 1.1em;">🔍 Filter Students</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <!-- Search Box with Autocomplete -->
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #2c3e50; font-size: 0.9em;">Search Name or ID:</label>
                        <input type="text" id="studentSearch" placeholder="Type to search..." style="width: 100%; padding: 10px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 0.95em;">
                        <div id="searchSuggestions" style="position: absolute; background: white; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; width: calc(100% - 4px); display: none; z-index: 100;"></div>
                    </div>

                    <!-- Gender Filter -->
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #2c3e50; font-size: 0.9em;">Gender:</label>
                        <select id="genderFilter" style="width: 100%; padding: 10px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 0.95em;">
                            <option value="">All Genders</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #2c3e50; font-size: 0.9em;">Today's Status:</label>
                        <select id="statusFilter" style="width: 100%; padding: 10px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 0.95em;">
                            <option value="">All Students</option>
                            <option value="present">✅ Present</option>
                            <option value="absent">❌ Absent</option>
                            <option value="late">⏰ Late</option>
                            <option value="unmarked">📋 Not Marked</option>
                        </select>
                    </div>

                    <!-- Reset Filters Button -->
                    <div style="display: flex; align-items: flex-end;">
                        <button onclick="resetFilters()" style="width: 100%; padding: 10px 12px; background: #95a5a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.95em;">
                            🔄 Reset Filters
                        </button>
                    </div>
                </div>
            </div>

            <?php if(!empty($errors)): ?>
                <div style="padding:14px 20px;background:linear-gradient(135deg,#f8d7da 0%,#f5c6cb 100%);border-left:4px solid #dc3545;margin-bottom:18px;border-radius:12px">
                    <?php foreach($errors as $err): ?><p style="margin:3px 0;color:#721c24;font-weight:600"><?= htmlspecialchars($err) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if($success): ?>
                <div style="padding:14px 20px;background:linear-gradient(135deg,#d4edda 0%,#c3e6cb 100%);border-left:4px solid #28a745;margin-bottom:18px;border-radius:12px;color:#155724;font-weight:600">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php
            $grade = $user['grade_level'];
            $strand = $user['strand'];
            $block = $user['section_block'];
            $students = $pdo->prepare('SELECT * FROM students WHERE grade_level = :grade AND strand = :strand AND section_block = :block ORDER BY full_name ASC');
            $students->execute([':grade'=>$grade, ':strand'=>$strand, ':block'=>$block]);
            $studentList = $students->fetchAll(PDO::FETCH_ASSOC);

            // Get today's attendance for status highlighting
            $attendanceCheck = $pdo->prepare('SELECT student_id, status FROM attendance_records WHERE attendance_date = :date');
            $attendanceCheck->execute([':date'=>$attendance_date]);
            $attendanceMap = [];
            foreach($attendanceCheck->fetchAll(PDO::FETCH_ASSOC) as $att) {
                $attendanceMap[$att['student_id']] = $att['status'];
            }
            ?>
            <div class="content-section">
                                <h2>📋 Student Records</h2>
                                <div class="student-table-container">
                                    <table class="student-records">
                                            <thead>
                                                    <tr>
                                                            <th>LRN</th>
                                                            <th>Student ID</th>
                                                            <th>Full Name</th>
                                                            <th>Email</th>
                                                            <th>Gender</th>
                                                            <th>Actions</th>
                                                    </tr>
                                            </thead>
                                            <tbody>
                                                    <?php foreach($studentList as $s):
                                                        $status = $attendanceMap[$s['student_id']] ?? 'unmarked';
                                                        $statusColor = match($status) {
                                                            'present' => '#d4edda',
                                                            'absent' => '#f8d7da',
                                                            'late' => '#fff3cd',
                                                            default => '#f0f0f0'
                                                        };
                                                        $statusText = match($status) {
                                                            'present' => '✅ Present',
                                                            'absent' => '❌ Absent',
                                                            'late' => '⏰ Late',
                                                            default => '📋 Not Marked'
                                                        };
                                                    ?>
                                                    <tr data-student-id="<?php echo htmlspecialchars($s['student_id']); ?>"
                                                        data-student-name="<?php echo htmlspecialchars($s['full_name']); ?>"
                                                        data-gender="<?php echo htmlspecialchars($s['gender'] ?? ''); ?>"
                                                        data-status="<?php echo $status; ?>"
                                                        style="background-color: <?php echo $statusColor; ?>; transition: all 0.2s;">
                                                            <td title="<?= htmlspecialchars($s['lrn'] ?? '') ?>">
                                                                <?= htmlspecialchars($s['lrn'] ?? '') ?>
                                                            </td>
                                                            <td title="<?= htmlspecialchars($s['student_id'] ?? '') ?>">
                                                                <?= htmlspecialchars($s['student_id'] ?? '') ?>
                                                            </td>
                                                            <td title="<?= htmlspecialchars($s['full_name']) ?>">
                                                                <strong><?= htmlspecialchars($s['full_name']) ?></strong>
                                                            </td>
                                                            <td title="<?= htmlspecialchars($s['email']) ?>">
                                                                <?= htmlspecialchars($s['email']) ?>
                                                            </td>
                                                            <td title="<?= htmlspecialchars($s['gender'] ?? '') ?>">
                                                                <?= htmlspecialchars($s['gender'] ?? '') ?>
                                                            </td>
                                                            <td>
                                                                <span style="display: inline-block; padding: 4px 8px; background: <?php echo $statusColor; ?>; border-radius: 4px; font-size: 0.85em; font-weight: 600; margin-right: 8px;">
                                                                    <?php echo $statusText; ?>
                                                                </span>
                                                                <button type="button" onclick='openEditModal(<?= json_encode($s) ?>)' class="action-btn" style="padding:10px 14px;font-size:1.1em;border-radius:8px;min-width:40px;min-height:40px;display:flex;align-items:center;justify-content:center" title="Edit">
                                                                    <span style="font-size:1.2em">✏️</span>
                                                                </button>
                                                            </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                            </tbody>
                                    </table>
                                </div>
            <style>
            @media (max-width: 768px) {
                .student-table-container {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                }
                table.student-records {
                    min-width: 600px;
                    font-size: 0.95em;
                }
                table.student-records th, table.student-records td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    max-width: 120px;
                    padding: 8px 6px;
                }
                table.student-records th {
                    position: sticky;
                    top: 0;
                    background: #fff;
                    z-index: 2;
                }
                .student-records .action-btn {
                    padding: 10px 14px;
                    font-size: 1.1em;
                    border-radius: 8px;
                    min-width: 40px;
                    min-height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
            }
            </style>
            </div>

            <!-- Edit Student Modal -->
            <div id="editModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.65);z-index:10000;justify-content:center;align-items:center">
                <div style="background:linear-gradient(135deg,#ffffff 0%,#f8fffe 100%);max-width:550px;width:90%;margin:50px auto;padding:32px 40px;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
                    <h3 style="margin:0 0 24px;color:#1e5128;font-size:1.5em;font-weight:700;padding-bottom:14px;border-bottom:3px solid #d8f3dc">✏️ Edit Student</h3>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="update_student">
                        <input type="hidden" name="student_id" id="edit_student_id">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                        <label style="display:block;margin-bottom:6px;color:#1e5128;font-weight:700;font-size:0.8em;text-transform:uppercase;letter-spacing:0.5px">Full Name:</label>
                        <input type="text" name="full_name" id="edit_full_name" required style="width:100%;padding:12px 14px;margin-bottom:14px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:0.9em;transition:all 0.3s">

                        <label style="display:block;margin-bottom:6px;color:#1e5128;font-weight:700;font-size:0.8em;text-transform:uppercase;letter-spacing:0.5px">LRN:</label>
                        <input type="text" name="lrn" id="edit_lrn" style="width:100%;padding:12px 14px;margin-bottom:14px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:0.9em;transition:all 0.3s">

                        <label style="display:block;margin-bottom:6px;color:#1e5128;font-weight:700;font-size:0.8em;text-transform:uppercase;letter-spacing:0.5px">Email:</label>
                        <input type="email" name="email" id="edit_email" required style="width:100%;padding:12px 14px;margin-bottom:14px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:0.9em;transition:all 0.3s">

                        <label style="display:block;margin-bottom:6px;color:#1e5128;font-weight:700;font-size:0.8em;text-transform:uppercase;letter-spacing:0.5px">Gender:</label>
                        <select name="gender" id="edit_gender" style="width:100%;padding:12px 14px;margin-bottom:14px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:0.9em;transition:all 0.3s">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>

                        <label style="display:block;margin-bottom:6px;color:#1e5128;font-weight:700;font-size:0.8em;text-transform:uppercase;letter-spacing:0.5px">Change Password (leave blank to keep current):</label>
                        <input type="password" name="password" id="edit_password" placeholder="New password" style="width:100%;padding:12px 14px;margin-bottom:18px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:0.9em;transition:all 0.3s">

                        <div style="display:flex;gap:12px">
                            <button type="submit" class="btn btn-primary" style="flex:1;padding:13px">Update Student</button>
                            <button type="button" onclick="closeEditModal()" style="flex:1;padding:13px;background:linear-gradient(135deg,#95a5a6 0%,#7f8c8d 100%);color:#fff;border:none;border-radius:11px;font-weight:600;cursor:pointer;transition:all 0.3s">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            function openEditModal(student) {
                document.getElementById('edit_student_id').value = student.id;
                document.getElementById('edit_full_name').value = student.full_name || '';
                document.getElementById('edit_lrn').value = student.lrn || '';
                document.getElementById('edit_email').value = student.email || '';
                document.getElementById('edit_gender').value = student.gender || '';
                document.getElementById('edit_password').value = '';
                document.getElementById('editModal').style.display = 'flex';
            }
            function closeEditModal() {
                document.getElementById('editModal').style.display = 'none';
            }

            // Student List Filtering & Search
            const studentSearch = document.getElementById('studentSearch');
            const genderFilter = document.getElementById('genderFilter');
            const statusFilter = document.getElementById('statusFilter');
            const studentTable = document.querySelector('table tbody');
            const allRows = studentTable ? Array.from(studentTable.querySelectorAll('tr')) : [];

            function applyFilters() {
                const searchTerm = (studentSearch?.value || '').toLowerCase();
                const selectedGender = genderFilter?.value || '';
                const selectedStatus = statusFilter?.value || '';

                allRows.forEach(row => {
                    const name = (row.dataset.studentName || '').toLowerCase();
                    const id = (row.dataset.studentId || '').toLowerCase();
                    const gender = row.dataset.gender || '';
                    const status = row.dataset.status || '';

                    let matches = true;

                    // Search filter
                    if (searchTerm && !name.includes(searchTerm) && !id.includes(searchTerm)) {
                        matches = false;
                    }

                    // Gender filter
                    if (selectedGender && gender !== selectedGender) {
                        matches = false;
                    }

                    // Status filter
                    if (selectedStatus && status !== selectedStatus) {
                        matches = false;
                    }

                    row.style.display = matches ? '' : 'none';
                });

                updateVisibleCount();
            }

            function updateVisibleCount() {
                const visibleCount = allRows.filter(r => r.style.display !== 'none').length;
                const totalCount = allRows.length;
                const badge = document.getElementById('student-count');
                if (badge) {
                    badge.textContent = visibleCount;
                }
            }

            function resetFilters() {
                if (studentSearch) studentSearch.value = '';
                if (genderFilter) genderFilter.value = '';
                if (statusFilter) statusFilter.value = '';
                allRows.forEach(row => row.style.display = '');
                updateVisibleCount();
            }

            // Add event listeners for filtering
            if (studentSearch) {
                studentSearch.addEventListener('input', applyFilters);
                // Autocomplete suggestions
                studentSearch.addEventListener('input', function() {
                    const value = this.value.toLowerCase();
                    if (value.length === 0) {
                        document.getElementById('searchSuggestions').style.display = 'none';
                        return;
                    }

                    const matches = allRows
                        .filter(row => {
                            const name = (row.dataset.studentName || '').toLowerCase();
                            const id = (row.dataset.studentId || '').toLowerCase();
                            return name.includes(value) || id.includes(value);
                        })
                        .slice(0, 5)
                        .map(row => row.dataset.studentName || '');

                    if (matches.length > 0) {
                        const suggestionsDiv = document.getElementById('searchSuggestions');
                        suggestionsDiv.innerHTML = matches.map(m => `<div style="padding:8px 12px; cursor:pointer; border-bottom:1px solid #eee;" onclick="document.getElementById('studentSearch').value='${m}'; applyFilters(); this.parentElement.style.display='none';">${m}</div>`).join('');
                        suggestionsDiv.style.display = 'block';
                    }
                });
            }

            if (genderFilter) genderFilter.addEventListener('change', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);

            // Update student count on load
            document.addEventListener('DOMContentLoaded', function() {
                updateVisibleCount();
                const studentBadge = document.getElementById('student-count');
                if (studentBadge) {
                    const studentRows = document.querySelectorAll('table tbody tr').length;
                    if (studentRows > 0) {
                        studentBadge.textContent = studentRows;
                    }
                }
            });
            </script>
        <?php elseif($section === 'analytics'): ?>
            <?php
            // Build daily analytics for this teacher's class
            $grade = $user['grade_level'];
            $strand = $user['strand'];
            $block = $user['section_block'];
            $studentsStmt = $pdo->prepare('SELECT student_id, full_name FROM students WHERE grade_level = :grade AND strand = :strand AND section_block = :block');
            $studentsStmt->execute([':grade'=>$grade, ':strand'=>$strand, ':block'=>$block]);
            $studentList = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
            $studentIds = array_column($studentList, 'student_id');
            $studentNames = [];
            foreach ($studentList as $s) { $studentNames[$s['student_id']] = $s['full_name']; }
            $totalStudents = count($studentIds);

            // Format date display variable
            $today = date('M d, Y', strtotime($attendance_date));

            // Use attendance_date for analytics (auto-adjusts after 7PM)
            $yesterday = date('Y-m-d', strtotime($attendance_date . ' -1 day'));
            $weekStart = date('Y-m-d', strtotime($attendance_date . ' -6 days'));

            $fetchStatusCounts = function($date, $ids, $pdo) {
                if (empty($ids)) return [];
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("SELECT status, COUNT(*) as c FROM attendance_records WHERE attendance_date = ? AND student_id IN ($placeholders) GROUP BY status");
                $stmt->execute(array_merge([$date], $ids));
                return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            };

            $todayStats = $fetchStatusCounts($attendance_date, $studentIds, $pdo);
            $yesterdayStats = $fetchStatusCounts($yesterday, $studentIds, $pdo);

            $presentToday = (int)($todayStats['present'] ?? 0);
            $lateToday = (int)($todayStats['late'] ?? 0);
            $absentToday = (int)($todayStats['absent'] ?? 0);
            $excuseToday = (int)($todayStats['excuse'] ?? 0);
            $halfToday = (int)($todayStats['morning_half_day'] ?? 0) + (int)($todayStats['afternoon_half_day'] ?? 0);

            $presentY = (int)($yesterdayStats['present'] ?? 0);
            $lateY = (int)($yesterdayStats['late'] ?? 0);
            $absentY = (int)($yesterdayStats['absent'] ?? 0);
            $halfY = (int)($yesterdayStats['morning_half_day'] ?? 0) + (int)($yesterdayStats['afternoon_half_day'] ?? 0);

            $attendanceRate = $totalStudents ? round((($presentToday + $lateToday + ($halfToday * 0.5)) / $totalStudents) * 100) : 0;
            $attendanceRateY = $totalStudents ? round((($presentY + $lateY + ($halfY * 0.5)) / $totalStudents) * 100) : 0;
            $rateDelta = $attendanceRate - $attendanceRateY;

            // Build last 7 days series for visualization
            $dailyLabels = [];
            $dailyRates = [];
            $dailyPresent = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime('-' . $i . ' days'));
                $dailyLabels[] = $d;
                $stats = $fetchStatusCounts($d, $studentIds, $pdo);
                $p = (int)($stats['present'] ?? 0);
                $l = (int)($stats['late'] ?? 0);
                $h = (int)($stats['morning_half_day'] ?? 0) + (int)($stats['afternoon_half_day'] ?? 0);
                $rate = $totalStudents ? round((($p + $l + ($h * 0.5)) / $totalStudents) * 100, 1) : 0;
                $dailyRates[] = $rate;
                $dailyPresent[] = $p;
            }

            // Top late students (last 7 days)
            $topLate = [];
            if (!empty($studentIds)) {
                $ph = implode(',', array_fill(0, count($studentIds), '?'));
                $stmt = $pdo->prepare("SELECT student_id, COUNT(*) as c FROM attendance_records WHERE status = 'late' AND attendance_date >= ? AND student_id IN ($ph) GROUP BY student_id ORDER BY c DESC LIMIT 3");
                $stmt->execute(array_merge([$weekStart], $studentIds));
                $topLate = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // AI-style heuristic insights
            $insights = [];
            if ($attendanceRate >= 95) {
                $insights[] = 'Excellent attendance today—keep reinforcing on-time habits.';
            } elseif ($attendanceRate >= 85) {
                $insights[] = 'Attendance is solid; target the few absences and late arrivals.';
            } else {
                $insights[] = 'Attendance needs attention—consider reminders and parent notices for absentees.';
            }

            if ($lateToday > 0) {
                $insights[] = $lateToday . ' student(s) arrived late; set a quick homeroom reminder to improve punctuality tomorrow.';
            } else {
                $insights[] = 'No late arrivals logged today—great punctuality.';
            }

            if ($absentToday > 0) {
                $insights[] = $absentToday . ' absent; follow up with parents/guardians and log reasons to track patterns.';
            }

            if ($excuseToday > 0) {
                $insights[] = $excuseToday . ' excused; ensure documentation is filed and reasons are tracked.';
            }

            if ($rateDelta !== 0) {
                $insights[] = 'Attendance rate changed ' . ($rateDelta > 0 ? 'up ' : 'down ') . abs($rateDelta) . '% vs yesterday.';
            }
            ?>
            <div class="content-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                    <h2 style="margin: 0;">🤖 AI Attendance Insights</h2>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <!-- Date Range Picker -->
                        <div>
                            <label style="display: block; font-size: 0.85em; color: #666; margin-bottom: 4px;">View Period:</label>
                            <select id="analyticsPeriod" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9em;">
                                <option value="today">Today</option>
                                <option value="week" selected>Last 7 Days</option>
                                <option value="month">Last 30 Days</option>
                            </select>
                        </div>

                        <!-- Export Buttons -->
                        <button onclick="exportAnalyticsCSV()" style="padding: 8px 16px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9em;">
                            📊 Export CSV
                        </button>
                        <button onclick="exportAnalyticsPDF()" style="padding: 8px 16px; background: #e74c3c; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9em;">
                            📄 Export PDF
                        </button>
                    </div>
                </div>

                <?php if($totalStudents === 0): ?>
                    <p style="color:#5a6c7d">No students found for your class. Add students to view analytics.</p>
                <?php else: ?>
                    <!-- Top Summary Cards -->
                    <div class="analytics-grid">
                        <div class="analytics-card">
                            <h3>Attendance Rate</h3>
                            <div class="analytics-metric"><?php echo $attendanceRate; ?>%</div>
                            <div class="analytics-sub">Today (<?php echo $today; ?>)</div>
                            <div class="analytics-sub">
                                Change vs yesterday:
                                <?php
                                    $badgeClass = $rateDelta > 0 ? 'badge-up' : ($rateDelta < 0 ? 'badge-down' : 'badge-flat');
                                    $badgeLabel = $rateDelta > 0 ? '▲ +' . abs($rateDelta) . '%' : ($rateDelta < 0 ? '▼ -' . abs($rateDelta) . '%' : '■ 0%');
                                ?>
                                <span class="badge-delta <?php echo $badgeClass; ?>"><?php echo $badgeLabel; ?></span>
                            </div>
                        </div>
                        <div class="analytics-card">
                            <h3>Late & Excused</h3>
                            <div class="analytics-metric"><?php echo $lateToday; ?></div>
                            <div class="analytics-sub">Late today (<?php echo $today; ?>)</div>
                            <div class="analytics-sub">Excused: <?php echo $excuseToday; ?></div>
                        </div>
                        <div class="analytics-card">
                            <h3>Absent</h3>
                            <div class="analytics-metric" style="color:#c62828"><?php echo $absentToday; ?></div>
                            <div class="analytics-sub">Absent today</div>
                        </div>
                        <div class="analytics-card">
                            <h3>Total Students</h3>
                            <div class="analytics-metric" style="color:#3498db"><?php echo $totalStudents; ?></div>
                            <div class="analytics-sub">Class size</div>
                        </div>
                    </div>

                    <!-- Side-by-side Charts -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="content-section" style="padding: 20px;">
                            <h3 style="margin-top: 0;">📊 Today's Distribution</h3>
                            <canvas id="attendanceDonut" height="200"></canvas>
                        </div>
                        <div class="content-section" style="padding: 20px;">
                            <h3 style="margin-top: 0;">📈 7-Day Trend</h3>
                            <canvas id="attendanceTrend" height="200"></canvas>
                        </div>
                    </div>

                    <!-- Bottom Section - Insights and Top Late -->
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                        <div class="content-section" style="padding: 20px;">
                            <h3 style="margin-top: 0;">💡 AI Highlights</h3>
                            <ul class="insights-list">
                                <?php foreach($insights as $line): ?>
                                    <li><?php echo htmlspecialchars($line); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="content-section" style="padding: 20px;">
                            <h3 style="margin-top: 0;">⏰ Frequent Late (7 days)</h3>
                            <?php if(empty($topLate)): ?>
                                <p class="analytics-sub">No late patterns detected.</p>
                            <?php else: ?>
                                <table class="mini-table">
                                    <thead>
                                        <tr><th>Student</th><th style="text-align:right">Count</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($topLate as $row): ?>
                                            <tr style="background: #fff3cd;">
                                                <td style="font-weight: 600;"><?php echo htmlspecialchars($studentNames[$row['student_id']] ?? $row['student_id']); ?></td>
                                                <td style="text-align:right; font-weight:700; color:#c77800"><?php echo (int)$row['c']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
            <script>
                // Analytics export functions
                function exportAnalyticsCSV() {
                    const data = [
                        ['Attendance Analytics Report'],
                        ['Date', new Date().toLocaleDateString()],
                        [''],
                        ['Status', 'Count'],
                        ['Present', <?php echo $presentToday; ?>],
                        ['Late', <?php echo $lateToday; ?>],
                        ['Absent', <?php echo $absentToday; ?>],
                        ['Excused', <?php echo $excuseToday; ?>]
                    ];

                    const csv = data.map(row => row.join(',')).join('\n');
                    const blob = new Blob([csv], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'attendance-analytics-' + new Date().toISOString().slice(0,10) + '.csv';
                    a.click();
                }

                function exportAnalyticsPDF() {
                    window.print();
                }

                (function(){
                    // Wait for DOM and Chart.js to be ready
                    setTimeout(function(){
                        const donutElement = document.getElementById('attendanceDonut');
                        const trendElement = document.getElementById('attendanceTrend');

                        if (!donutElement || !trendElement) {
                            console.warn('Canvas elements not found');
                            return;
                        }

                        try {
                            const donutCtx = donutElement.getContext('2d');
                            const trendCtx = trendElement.getContext('2d');

                            if (!donutCtx || !trendCtx) {
                                console.error('Failed to get canvas context');
                                return;
                            }

                            // Destroy existing charts if they exist
                            if (window.attendanceDonutChart) window.attendanceDonutChart.destroy();
                            if (window.attendanceTrendChart) window.attendanceTrendChart.destroy();

                            const donutData = {
                                labels: ['Present','Late','Absent','Excused','Half-day'],
                                datasets: [{
                                    data: [
                                        <?php echo json_encode($presentToday); ?>,
                                        <?php echo json_encode($lateToday); ?>,
                                        <?php echo json_encode($absentToday); ?>,
                                        <?php echo json_encode($excuseToday); ?>,
                                        <?php echo json_encode($halfToday); ?>
                                    ],
                                    backgroundColor: ['#1e5128','#f4a300','#c62828','#2d9cdb','#7b61ff'],
                                    borderWidth: 0
                                }]
                            };

                            window.attendanceDonutChart = new Chart(donutCtx, {
                                type: 'doughnut',
                                data: donutData,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: true,
                                    plugins: {
                                        legend: {
                                            position: 'bottom',
                                            labels:{
                                                boxWidth: 12,
                                                font: { size: 12 }
                                            }
                                        }
                                    },
                                    cutout: '55%'
                                }
                            });

                            const trendData = {
                                labels: <?php echo json_encode($dailyLabels); ?>,
                                datasets: [{
                                    label: 'Attendance %',
                                    data: <?php echo json_encode($dailyRates); ?>,
                                    borderColor: '#1e5128',
                                    backgroundColor: 'rgba(46, 139, 87, 0.15)',
                                    fill: true,
                                    tension: 0.3,
                                    borderWidth: 2,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#1e5128'
                                }]
                            };

                            window.attendanceTrendChart = new Chart(trendCtx, {
                                type: 'line',
                                data: trendData,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: true,
                                    plugins: {
                                        legend: { display: true, position: 'top' }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            suggestedMin: 0,
                                            suggestedMax: 100,
                                            ticks: {
                                                callback: function(v) { return v + '%'; }
                                            }
                                        }
                                    }
                                }
                            });
                        } catch(err) {
                            console.error('Chart initialization error:', err);
                        }
                    }, 100);
                })();
            </script>
            </div>

        <?php elseif($section === 'corrections'): ?>
            <?php
            // Handle attendance correction
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['correct_action'])) {
                $studentId = (int)($_POST['student_id'] ?? 0);
                $status = trim($_POST['status'] ?? '');
                $date = trim($_POST['correction_date'] ?? '');
                $reason = trim($_POST['correction_reason'] ?? '');

                if ($studentId && in_array($status, ['present', 'late', 'absent', 'excuse', 'morning_half_day', 'afternoon_half_day'])) {
                    try {
                        // Check if record exists
                        $checkStmt = $pdo->prepare('SELECT id FROM attendance_records WHERE student_id = :sid AND attendance_date = :date');
                        $checkStmt->execute([':sid' => $studentId, ':date' => $date]);
                        $existing = $checkStmt->fetch();

                        if ($existing) {
                            // Update existing record
                            $updateStmt = $pdo->prepare('UPDATE attendance_records SET status = :status, correction_reason = :reason, corrected_at = NOW(), corrected_by = :teacher_id WHERE student_id = :sid AND attendance_date = :date');
                            $updateStmt->execute([':status' => $status, ':reason' => $reason, ':teacher_id' => $uid, ':sid' => $studentId, ':date' => $date]);
                            $success = '✅ Attendance corrected successfully';
                        } else {
                            // Insert new record if doesn't exist
                            $insertStmt = $pdo->prepare('INSERT INTO attendance_records (student_id, attendance_date, status, correction_reason, corrected_by, corrected_at) VALUES (:sid, :date, :status, :reason, :teacher_id, NOW())');
                            $insertStmt->execute([':sid' => $studentId, ':date' => $date, ':status' => $status, ':reason' => $reason, ':teacher_id' => $uid]);
                            $success = '✅ Attendance record created';
                        }
                    } catch (Exception $e) {
                        $success = '❌ Error: ' . $e->getMessage();
                    }
                }
            }

            // Get students and their attendance for correction
            $grade = $user['grade_level'];
            $strand = $user['strand'];
            $block = $user['section_block'];
            // Wrap the corrections query in a try/catch so missing DB columns don't cause a fatal error.
            try {
                $studentsStmt = $pdo->prepare('SELECT s.student_id, s.full_name, a.status, a.attendance_date, a.correction_reason FROM students s LEFT JOIN attendance_records a ON s.student_id = a.student_id AND a.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) WHERE s.grade_level = :grade AND s.strand = :strand AND s.section_block = :block ORDER BY s.full_name, a.attendance_date DESC');
                $studentsStmt->execute([':grade' => $grade, ':strand' => $strand, ':block' => $block]);
                $studentRecords = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Log the actual DB error for the admin and degrade gracefully in the UI
                error_log('[teacher_dashboard][corrections] DB error: ' . $e->getMessage());
                $studentRecords = [];
                $corrections_error = 'Corrections temporarily unavailable (database schema mismatch). Please run the migration to add correction columns.';
            }
            ?>
            <div class="content-section">
                <h2>📝 Correct Student Attendance</h2>
                <?php if (!empty($success)): ?>
                    <div style="background: <?php echo strpos($success, '✅') ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo strpos($success, '✅') ? '#155724' : '#721c24'; ?>; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; border-left: 4px solid <?php echo strpos($success, '✅') ? '#28a745' : '#dc3545'; ?>;">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;">
                    <strong>ℹ️ Info:</strong> Correct attendance records from the last 7 days. All corrections are logged with teacher ID and timestamp.
                </div>

                <table class="mini-table" style="width: 100%; margin-bottom: 16px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Last Attendance</th>
                            <th>Current Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $studentsSeen = [];
                        foreach ($studentRecords as $record):
                            if (isset($studentsSeen[$record['student_id']])) continue;
                            $studentsSeen[$record['student_id']] = true;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['full_name']); ?></td>
                                <td style="font-weight: bold; color: #2d6a4f;"><?php echo htmlspecialchars($record['student_id']); ?></td>
                                <td><?php echo $record['attendance_date'] ? date('M d, Y', strtotime($record['attendance_date'])) : 'N/A'; ?></td>
                                <td>
                                    <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.9em;
                                    <?php
                                    $statusColor = match($record['status'] ?? '') {
                                        'present' => 'background: #d4edda; color: #155724;',
                                        'late' => 'background: #fff3cd; color: #856404;',
                                        'absent' => 'background: #f8d7da; color: #721c24;',
                                        'excuse' => 'background: #d1ecf1; color: #0c5460;',
                                        default => 'background: #e2e3e5; color: #383d41;'
                                    };
                                    echo $statusColor;
                                    ?>">
                                        <?php echo $record['status'] ? ucfirst($record['status']) : 'No record'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button onclick="openCorrectionModal('<?php echo htmlspecialchars($record['student_id']); ?>', '<?php echo htmlspecialchars($record['full_name']); ?>')" style="background: #2d6a4f; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.9em;">
                                        ✏️ Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Correction Modal -->
            <div id="correctionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
                <div style="background: white; padding: 24px; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
                    <h3 style="margin-top: 0; margin-bottom: 16px; color: #2c3e50;">Correct Attendance</h3>
                    <form method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                        <input type="hidden" name="correct_action" value="1">
                        <input type="hidden" name="student_id" id="modal_student_id">

                        <div>
                            <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #2c3e50;">Student: <span id="modal_student_name"></span></label>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #2c3e50;">Date:</label>
                            <input type="date" name="correction_date" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;">
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #2c3e50;">New Status:</label>
                            <select name="status" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;">
                                <option value="">-- Select Status --</option>
                                <option value="present">✅ Present</option>
                                <option value="late">⏰ Late</option>
                                <option value="absent">❌ Absent</option>
                                <option value="excuse">📋 Excused</option>
                                <option value="morning_half_day">🌅 Morning Half-Day</option>
                                <option value="afternoon_half_day">🌆 Afternoon Half-Day</option>
                            </select>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #2c3e50;">Reason for Correction:</label>
                            <textarea name="correction_reason" placeholder="e.g., Make-up attendance, System error, Parent request..." style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em; min-height: 80px; resize: vertical;"></textarea>
                        </div>

                        <div style="display: flex; gap: 8px; margin-top: 8px;">
                            <button type="submit" style="flex: 1; background: #27ae60; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                                💾 Save Correction
                            </button>
                            <button type="button" onclick="closeCorrectionModal()" style="flex: 1; background: #95a5a6; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                                ✕ Cancel
                            </button>
                        </div>

                        <div style="background: #f0f0f0; padding: 10px 12px; border-radius: 6px; font-size: 0.85em; color: #555; margin-top: 8px;">
                            <strong>Note:</strong> This action is logged with your teacher ID and timestamp for audit purposes.
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function openCorrectionModal(studentId, studentName) {
                    document.getElementById('modal_student_id').value = studentId;
                    document.getElementById('modal_student_name').textContent = studentName + ' (' + studentId + ')';
                    document.getElementById('correction_date').valueAsDate = new Date();
                    document.getElementById('correctionModal').style.display = 'flex';
                }

                function closeCorrectionModal() {
                    document.getElementById('correctionModal').style.display = 'none';
                }

                // Close modal on outside click
                document.getElementById('correctionModal').addEventListener('click', function(e) {
                    if (e.target === this) closeCorrectionModal();
                });
            </script>
        <?php endif; ?>
        </div>
    </div>

    <script>
        // Prevent back button from showing cached page
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
        };
    </script>
</body>
</html>
