<?php
// teacher_dashboard.php - main dashboard for teachers with sidebar
session_start();
date_default_timezone_set('Asia/Manila'); // Set Manila timezone
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auto_reset_7pm.php'; // Auto-reset system

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

// here are the css styles for sidebar and main content

function active($s, $section) { return $s === $section ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - PNS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            background: linear-gradient(135deg, #1e5128 0%, #2d6a4f 100%);
            min-height: 100vh;
            display: flex;
            color: #2c3e50;
        }
                .sidebar {
                        width: 280px;
                        background: linear-gradient(180deg, #1e5128 0%, #2d6a4f 100%);
                        color: #fff;
                        min-height: 100vh;
                        padding: 0 0 0 0;
                        box-shadow: 4px 0 20px rgba(0,0,0,0.15);
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
            border-bottom: 2px solid rgba(255,255,255,0.1);
            background: linear-gradient(180deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
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
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
            color: #fff;
        }
        .school-name {
            font-size: 0.7em;
            color: #b7e4c7;
            margin: 2px 0 0;
            font-weight: 600;
        }
        .teacher-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            background: rgba(255,255,255,0.08);
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
            font-size: 0.9em;
            font-weight: 700;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #fff;
        }
        .teacher-role {
            font-size: 0.75em;
            color: #b7e4c7;
            margin: 2px 0 0;
            font-weight: 600;
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
            color: #d8f3dc;
            font-size: 0.7em;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: #d8f3dc;
            padding-left: 24px;
        }
        .sidebar a.active {
            background: linear-gradient(90deg, rgba(216,243,220,0.2) 0%, rgba(216,243,220,0.1) 100%);
            border-left-color: #d8f3dc;
            box-shadow: inset 2px 0 8px rgba(0,0,0,0.2);
        }
        .sidebar a.active .menu-badge {
            background: #d8f3dc;
            color: #1e5128;
        }
        .sidebar a.logout {
            margin-top: auto;
            border-top: 2px solid rgba(255,255,255,0.1);
            padding-top: 16px;
            margin-bottom: 12px;
            color: #ffcccc;
            border-left: 4px solid transparent;
        }
        .sidebar a.logout:hover {
            background: rgba(231,76,60,0.2);
            border-left-color: #e74c3c;
            color: #fff;
        }
        .main {
            margin-left: 280px;
            flex: 1;
            padding: 0;
            width: calc(100% - 280px);
            background: #f5f5f5;
        }
        .main-header {
            background: #fff;
            padding: 20px 32px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .main-header h1 {
            font-size: 1.5em;
            color: #333;
            margin: 0 0 4px 0;
            font-weight: 700;
        }
        .main-header p {
            margin: 0;
            font-size: 0.85em;
            color: #999;
        }
        .main-header-stats {
            font-size: 0.85em;
            color: #999;
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
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 32px;
        }
        .page-header {
            display: none;
        }
        .page-header h1 {
            color: #1e5128;
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .page-header p {
            color: #5a6c7d;
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
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .stat-card.present { border-left-color: #28a745; }
        .stat-card.late { border-left-color: #ffc107; }
        .stat-card.absent { border-left-color: #dc3545; }
        .stat-card.excuse { border-left-color: #17a2b8; }
        .stat-value {
            font-size: 2em;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .stat-card.present .stat-value { color: #28a745; }
        .stat-card.late .stat-value { color: #ffc107; }
        .stat-card.absent .stat-value { color: #dc3545; }
        .stat-card.excuse .stat-value { color: #17a2b8; }
        .stat-label {
            color: #5a6c7d;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .content-section {
            background: #fff;
            border-radius: 8px;
            padding: 28px 32px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
        }
        .content-section h2 {
            color: #333;
            font-size: 1.2em;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 2px solid #f0f0f0;
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
            border-color: #2d6a4f;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(45,106,79,0.1);
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
        }
        table th {
            background: #f5f5f5;
            padding: 14px 12px;
            text-align: left;
            font-weight: 700;
            color: #333;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e0e0e0;
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

            /* Analytics mobile layout */
            .main-content { padding: 16px; }
            .analytics-grid { grid-template-columns: 1fr; gap: 12px; }
            .analytics-card { padding: 14px 16px; }
            .analytics-metric { font-size: 1.4em; }
            .analytics-card[style*="grid-column"] { grid-column: auto; }
            canvas { max-width: 100%; height: auto !important; }
            
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
                <img src="<?php echo file_exists(__DIR__.'/uploads/OIP (1).webp') ? 'uploads/OIP (1).webp' : '#'; ?>" alt="PNS Logo" class="sidebar-logo">
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
        </div>
        
        <a href="logout.php" class="logout">🚪 Logout</a>
    </div>
    <div class="main">
        <div class="main-header">
            <div>
                <h1><?php echo $section === 'scanner' ? '📷 Attendance Scanner' : ($section === 'today' ? '📅 Today\'s Attendance' : ($section === 'student_list' ? '👥 Student List' : ($section === 'analytics' ? '📈 Analytics' : '📊 Dashboard'))); ?></h1>
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
                        
                        <!-- Exit Button -->
                        <button onclick="exitFullscreenCamera()" style="position: absolute; top: 20px; right: 20px; padding: 12px 24px; background: rgba(231,76,60,0.9); color: white; border: none; border-radius: 8px; font-size: 1.1em; font-weight: 600; cursor: pointer; z-index: 10000; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                            ✕ Exit
                        </button>

                        <!-- Manual Input Box -->
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.8); padding: 30px; backdrop-filter: blur(10px);">
                            <div style="max-width: 600px; margin: 0 auto;">
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

                    document.getElementById('scanStatus').textContent = '⏳ Recording...';

                    fetch('record_attendance.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            code: studentId,
                            type: 'manual',
                            period: 'scan'
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const student = data.student_info;
                            showSuccessPopup(
                                `Attendance for ${student.full_name}`,
                                `Grade ${student.grade_level} - ${student.strand} - ${student.section_block}<br>Status: ${data.attendance_info.status}`
                            );
                            document.getElementById('manualStudentId').value = '';
                            document.getElementById('scanStatus').textContent = '';
                        } else {
                            document.getElementById('scanStatus').textContent = '❌ ' + (data.error || 'Student not found');
                            setTimeout(() => {
                                document.getElementById('scanStatus').textContent = '';
                            }, 3000);
                        }
                    })
                    .catch(err => {
                        document.getElementById('scanStatus').textContent = '❌ Network error';
                        setTimeout(() => {
                            document.getElementById('scanStatus').textContent = '';
                        }, 3000);
                    });
                }

                function showSuccessPopup(title, message) {
                    document.getElementById('popupTitle').textContent = '✅ ' + title;
                    document.getElementById('popupMessage').innerHTML = message;
                    document.getElementById('successPopup').style.display = 'block';
                    
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
            ?>
            <div class="stats-grid">
                <div class="stat-card present">
                    <div class="stat-header">
                        <div class="stat-label">Present</div>
                        <div class="stat-icon">✓</div>
                    </div>
                    <div class="stat-number"><?php echo $present; ?></div>
                    <div class="stat-progress">
                        <div class="stat-progress-bar" style="width: <?php echo $presentPct; ?>%"></div>
                    </div>
                    <div class="stat-label" style="margin-top: 8px; color: #27ae60;"><?php echo $presentPct; ?>%</div>
                </div>
                <div class="stat-card late">
                    <div class="stat-header">
                        <div class="stat-label">Late</div>
                        <div class="stat-icon">⏱️</div>
                    </div>
                    <div class="stat-number"><?php echo $late; ?></div>
                    <div class="stat-progress">
                        <div class="stat-progress-bar" style="width: <?php echo $latePct; ?>%"></div>
                    </div>
                    <div class="stat-label" style="margin-top: 8px; color: #f39c12;"><?php echo $latePct; ?>%</div>
                </div>
                <div class="stat-card absent">
                    <div class="stat-header">
                        <div class="stat-label">Absent</div>
                        <div class="stat-icon">✕</div>
                    </div>
                    <div class="stat-number"><?php echo $absent; ?></div>
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
                                                    <?php foreach($studentList as $s): ?>
                                                    <tr>
                                                            <td title="<?= htmlspecialchars($s['lrn'] ?? '') ?>">
                                                                <?= htmlspecialchars($s['lrn'] ?? '') ?>
                                                            </td>
                                                            <td title="<?= htmlspecialchars($s['student_id'] ?? '') ?>">
                                                                <?= htmlspecialchars($s['student_id'] ?? '') ?>
                                                            </td>
                                                            <td title="<?= htmlspecialchars($s['full_name']) ?>">
                                                                <?= htmlspecialchars($s['full_name']) ?>
                                                            </td>
                                                            <td title="<?= htmlspecialchars($s['email']) ?>">
                                                                <?= htmlspecialchars($s['email']) ?>
                                                            </td>
                                                            <td title="<?= htmlspecialchars($s['gender'] ?? '') ?>">
                                                                <?= htmlspecialchars($s['gender'] ?? '') ?>
                                                            </td>
                                                            <td>
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
            
            // Update student count in sidebar
            document.addEventListener('DOMContentLoaded', function() {
                const studentBadge = document.getElementById('student-count');
                if (studentBadge) {
                    // Count student list items or get from page context
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
                <h2>🤖 AI Attendance Insights (Daily)</h2>
                <?php if($totalStudents === 0): ?>
                    <p style="color:#5a6c7d">No students found for your class. Add students to view analytics.</p>
                <?php else: ?>
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
                        <div class="analytics-card" style="grid-column: span 2; min-width: 260px;">
                            <h3>Visualization</h3>
                            <canvas id="attendanceDonut" height="140"></canvas>
                            <div style="margin-top:14px;"></div>
                            <canvas id="attendanceTrend" height="140"></canvas>
                        </div>
                    </div>

                    <div class="analytics-grid">
                        <div class="analytics-card" style="grid-column: span 2; min-width: 260px;">
                            <h3>AI Highlights</h3>
                            <ul class="insights-list">
                                <?php foreach($insights as $line): ?>
                                    <li><?php echo htmlspecialchars($line); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="analytics-card">
                            <h3>Frequent Late (last 7 days)</h3>
                            <?php if(empty($topLate)): ?>
                                <p class="analytics-sub">No late patterns detected.</p>
                            <?php else: ?>
                                <table class="mini-table">
                                    <thead>
                                        <tr><th>Student</th><th style="text-align:right">Late Count</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($topLate as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($studentNames[$row['student_id']] ?? $row['student_id']); ?></td>
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
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                (function(){
                    const donutCtx = document.getElementById('attendanceDonut');
                    const trendCtx = document.getElementById('attendanceTrend');
                    if (!donutCtx || !trendCtx) return;
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
                    new Chart(donutCtx, {
                        type: 'doughnut',
                        data: donutData,
                        options: {
                            plugins: { legend: { position: 'bottom', labels:{ boxWidth:12 } } },
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
                            pointRadius: 3
                        }]
                    };
                    new Chart(trendCtx, {
                        type: 'line',
                        data: trendData,
                        options: {
                            plugins: { legend: { display: false } },
                            scales: { y: { suggestedMin:0, suggestedMax:100, ticks:{ callback:(v)=> v+'%' } } }
                        }
                    });
                })();
            </script>
        <?php endif; ?>
        </div>
    </div>
</body>
</html>
