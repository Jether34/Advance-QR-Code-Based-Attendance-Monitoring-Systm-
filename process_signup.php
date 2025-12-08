<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/logging.php';

$role = $_POST['role'] ?? '';
$full_name = trim($_POST['full_name'] ?? '');
$grade = $_POST['grade'] ?? null;
$strand = isset($_POST['strand']) && trim($_POST['strand']) !== '' ? trim($_POST['strand']) : 'N/A';
$block = $_POST['block_section'] ?? null;
$lrn = $_POST['lrn'] ?? null;
$email = $_POST['email'] ?? null;
$faculty = $_POST['faculty'] ?? null;
$password = $_POST['password'] ?? null;
$gender = $_POST['gender'] ?? null;
$accept_terms = isset($_POST['accept_terms']) ? 1 : 0;

// Validate terms acceptance
if (!$accept_terms) {
    die('You must accept the Terms and Conditions and Privacy Policy to sign up');
}

if(!$role || !$full_name || !$email || !$password || !$gender){
    die('Missing required fields');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$code = uniqid('u', true); // unique code used in QR/barcode


$pdo = get_db();
if ($role === 'teacher') {
    $stmt = $pdo->prepare('INSERT INTO teachers (full_name, email, password, gender, grade_level, strand, section_block, faculty) VALUES (:full_name, :email, :password, :gender, :grade_level, :strand, :section_block, :faculty)');
    $stmt->execute([
        ':full_name' => $full_name,
        ':email' => $email,
        ':password' => $hash,
        ':gender' => $gender,
        ':grade_level' => $grade,
        ':strand' => $strand,
        ':section_block' => $block,
        ':faculty' => $faculty
    ]);
    // Log signup success for teacher
    $newId = $pdo->lastInsertId();
    try {
        log_event($pdo, 'signup_success', [
            'user_role' => 'teacher',
            'user_id'   => $newId,
            'email'     => $email,
        ]);
    } catch (Throwable $ignored) {}
} else {
    // Generate random 6-character student_id with letters, numbers, and symbols
    function generateStudentId($length = 6) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*';
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $id;
    }
    $student_id = generateStudentId();
    $stmt = $pdo->prepare('INSERT INTO students (student_id, full_name, email, password, gender, grade_level, strand, section_block) VALUES (:student_id, :full_name, :email, :password, :gender, :grade_level, :strand, :section_block)');
    $stmt->execute([
        ':student_id' => $student_id,
        ':full_name' => $full_name,
        ':email' => $email,
        ':password' => $hash,
        ':gender' => $gender,
        ':grade_level' => $grade,
        ':strand' => $strand,
        ':section_block' => $block
    ]);
    // Log signup success for student
    $newId = $pdo->lastInsertId();
    try {
        log_event($pdo, 'signup_success', [
            'user_role' => 'student',
            'user_id'   => $newId,
            'email'     => $email,
        ]);
    } catch (Throwable $ignored) {}
}

$id = $pdo->lastInsertId();

// Redirect to login page after signup
header('Location: login.php?signup=1');
exit;
