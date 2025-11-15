<?php
// Simple index with links
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>School Attendance System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #d6f5d6 0%, #eaffea 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .home-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(33, 140, 33, 0.2);
            padding: 60px 40px;
            max-width: 600px;
            text-align: center;
        }
        .home-container h1 {
            color: #218c21;
            font-size: 2.5em;
            margin-bottom: 16px;
        }
        .home-container p {
            color: #176617;
            font-size: 1.1em;
            margin-bottom: 40px;
        }
        .btn-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-home {
            padding: 16px 32px;
            background: #218c21;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-home:hover {
            background: #176617;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 140, 33, 0.3);
        }
        .btn-secondary {
            background: #fff;
            color: #218c21;
            border: 2px solid #218c21;
        }
        .btn-secondary:hover {
            background: #eaffea;
        }
        .home-footer {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid #b2e2b2;
        }
        .home-footer a {
            color: #176617;
            font-size: 0.9em;
            text-decoration: none;
        }
        .home-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="home-container">
        <h1>🎓 School Attendance System</h1>
        <p>Streamline your attendance tracking with our modern, user-friendly system</p>
        <div class="btn-group">
            <a href="login.php" class="btn-home">Login</a>
            <a href="signup.php" class="btn-home btn-secondary">Sign Up</a>
        </div>
        
        <div class="home-footer">
            <a href="developer_login.php">🔧 Developer Dashboard</a>
        </div>
    </div>
</body>
</html>