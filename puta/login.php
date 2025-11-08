<?php
// login.php - login form and handler
session_start();
require_once __DIR__ . '/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $pdo = get_db();
        // Try teachers table first
        $stmt = $pdo->prepare('SELECT * FROM teachers WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && $user['password'] && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'teacher';
            header('Location: teacher_dashboard.php');
            exit;
        }
        // Try students table next
        $stmt = $pdo->prepare('SELECT * FROM students WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && $user['password'] && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'student';
            header('Location: student_dashboard.php');
            exit;
        }
        $error = 'Invalid email or password';
    } else {
        $error = 'Please enter email and password';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login - School Attendance System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #d6f5d6 0%, #eaffea 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(33, 140, 33, 0.2);
            padding: 40px 32px;
            max-width: 420px;
            width: 100%;
            margin: 20px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-header h1 {
            color: #218c21;
            font-size: 2em;
            margin-bottom: 8px;
        }
        .login-header p {
            color: #176617;
            font-size: 0.95em;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #218c21;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #b2e2b2;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        .form-group input:focus {
            border-color: #218c21;
            box-shadow: 0 0 0 3px rgba(33, 140, 33, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #218c21;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #176617;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 140, 33, 0.3);
        }
        .form-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #b2e2b2;
        }
        .form-footer a {
            color: #218c21;
            font-weight: 600;
            text-decoration: none;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Login to School Attendance System</p>
        </div>
        <?php if($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post">
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
