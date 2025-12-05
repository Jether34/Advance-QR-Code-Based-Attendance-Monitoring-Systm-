<?php
// developer_login.php - Dedicated login page for developers/admins
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/logging.php';

$error = '';

// Redirect if already logged in as developer
if (isset($_SESSION['developer_id'])) {
    header('Location: developer_dashboard.php');
    exit;
}

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $pdo = get_db();
            $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = :username');
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Successful login
                $_SESSION['developer_id'] = $admin['id'];
                $_SESSION['developer_username'] = $admin['username'];
                $_SESSION['developer_role'] = $admin['role'];
                log_event($pdo, 'developer_login_success', [
                    'user_role' => 'developer',
                    'user_id'   => $admin['id'],
                    'email'     => $admin['username'],
                ]);
                header('Location: developer_dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
                log_event($pdo, 'developer_login_failed', [
                    'user_role' => 'developer',
                    'email'     => $username,
                    'success'   => 0,
                    'message'   => 'Invalid username or password',
                ]);
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please contact system administrator.';
            try {
                if (!isset($pdo)) { $pdo = get_db(); }
                log_event($pdo, 'developer_login_failed', [
                    'user_role' => 'developer',
                    'email'     => $username,
                    'success'   => 0,
                    'message'   => 'DB error during login',
                ]);
            } catch (Throwable $ignored) {}
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Dashboard - Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e5128 0%, #2d6a4f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dev-login-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            max-width: 420px;
            width: 100%;
        }
        .dev-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .dev-header h1 {
            color: #1e5128;
            font-size: 1.8em;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .dev-header p {
            color: #7f8c8d;
            font-size: 0.95em;
        }
        .form-group {
            margin-bottom: 24px;
        }
        .form-group label {
            display: block;
            color: #1e5128;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95em;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2d6a4f;
            box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
        }
        .error-message {
            background: #e74c3c;
            color: #fff;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9em;
            text-align: center;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2d6a4f 0%, #1e5128 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #1e5128 0%, #163d20 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 106, 79, 0.4);
        }
        .footer-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }
        .footer-link a {
            color: #7f8c8d;
            text-decoration: none;
            font-size: 0.9em;
        }
        .footer-link a:hover {
            color: #2d6a4f;
        }
    </style>
</head>
<body>
    <div class="dev-login-container">
        <div class="dev-header">
            <h1>🔧 Developer Dashboard</h1>
            <p>Restricted Access - Authorized Personnel Only</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username" autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn-login">Sign In</button>
        </form>
        
        <div class="footer-link">
            <a href="index.php">← Back to Home</a>
        </div>
    </div>
</body>
</html>
