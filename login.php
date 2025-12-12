<?php
// login.php - login form and handler
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/logging.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limit based on IP+email
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rlKey = 'login:' . $ip . ':' . strtolower(trim($_POST['email'] ?? ''));
    rate_limit_check($rlKey, 5, 300);
    // CSRF validation
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $pdo = get_db();
        // Try teachers table first
        $stmt = $pdo->prepare('SELECT * FROM teachers WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && $user['password'] && password_verify($password, $user['password'])) {
            // Regenerate session ID on successful login to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role'] = 'teacher';
            // Log successful teacher login
            log_event($pdo, 'login_success', [
                'user_role' => 'teacher',
                'user_id'   => $user['id'],
                'email'     => $email,
            ]);
            // Bypass page security check on first login by not using page token
            $_SESSION['skip_page_token_check'] = true;
            header('Location: teacher_dashboard.php');
            exit;
        }
        // Try students table next
        $stmt = $pdo->prepare('SELECT * FROM students WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && $user['password'] && password_verify($password, $user['password'])) {
            // Regenerate session ID on successful login to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role'] = 'student';
            // Log successful student login
            log_event($pdo, 'login_success', [
                'user_role' => 'student',
                'user_id'   => $user['id'],
                'email'     => $email,
            ]);
            // Bypass page security check on first login by not using page token
            $_SESSION['skip_page_token_check'] = true;
            header('Location: student_dashboard.php');
            exit;
        }
        $error = 'Invalid email or password';
        // Log failed login attempt
        log_event($pdo, 'login_failed', [
            'email'   => $email,
            'success' => 0,
            'message' => 'Invalid credentials',
        ]);
    } else {
        $error = 'Please enter email and password';
        // Log failed login attempt due to missing fields
        $pdo = get_db();
        log_event($pdo, 'login_failed', [
            'email'   => $email ?: null,
            'success' => 0,
            'message' => 'Missing email or password',
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Palawan National School</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background:
                radial-gradient(circle at 18% 20%, rgba(34, 211, 238, 0.18), transparent 38%),
                radial-gradient(circle at 82% -10%, rgba(34, 197, 94, 0.16), transparent 42%),
                linear-gradient(140deg, #0b1221 0%, #0f1c33 50%, #0b243d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 26px;
            color: var(--text);
        }
        .login-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 28px 80px rgba(8, 47, 73, 0.22);
            border: 1px solid #e2e8f0;
            padding: 42px 36px;
            max-width: 640px;
            width: 100%;
            margin: 20px auto;
        }
        .logo-header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid #e2e8f0;
        }
        .logo-header img {
            max-width: 92px;
            height: auto;
            margin-bottom: 10px;
        }
        .logo-header .school-name {
            color: #0ea5e9;
            font-size: 1.2em;
            font-weight: 800;
            letter-spacing: 0.02em;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #0ea5e9;
            font-size: 2.1em;
            margin-bottom: 8px;
            letter-spacing: 0.01em;
        }
        .login-header p {
            color: #475569;
            font-size: 0.98em;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #0f172a;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d7e0eb;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.2s ease;
            box-sizing: border-box;
            background: #f7f9fc;
        }
        .form-group input:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.16);
            background: #ffffff;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0ea5e9 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1.05em;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(14, 165, 233, 0.28);
        }
        .form-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 22px;
            border-top: 1px solid #e2e8f0;
            color: #475569;
        }
        .form-footer a {
            color: #0ea5e9;
            font-weight: 700;
            text-decoration: none;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-header">
            <picture>
                <source type="image/webp" srcset="uploads/System logo-72.webp 72w, uploads/System logo-150.webp 150w, uploads/System logo-300.webp 300w">
                <source type="image/jpeg" srcset="uploads/System logo.jpg 150w">
                <img src="uploads/System logo-150.webp" srcset="uploads/System logo-72.webp 72w, uploads/System logo-150.webp 150w, uploads/System logo-300.webp 300w" sizes="72px" alt="QR Attendance System Logo" width="70" height="70" loading="eager">
            </picture>
            <div class="school-name">Palawan National School</div>
        </div>
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Hybrid QR Code Based Attendance System</p>
        </div>
        <?php if($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>" />
            <div class="form-group">
                <label>Email Address</label>
                <input name="email" type="email" placeholder="Enter your email" required />
            </div>
            <div class="form-group">
                <label>Password</label>
                <input name="password" type="password" placeholder="Enter your password" required />
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
        <div class="form-footer">
            <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
            <p><a href="index.php">← Back to Home</a></p>
        </div>
    </div>
</body>
</html>
