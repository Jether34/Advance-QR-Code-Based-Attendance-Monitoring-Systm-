<?php
// Simple index with links
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Palawan National School - Hybrid QR Code Based Attendance Monitoring System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #d6f5d6 0%, #eaffea 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .home-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(33, 140, 33, 0.2);
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        .logo-section {
            margin-bottom: 24px;
        }
        .logo-section img {
            max-width: 100px;
            height: auto;
            margin-bottom: 16px;
        }
        .home-container h1 {
            color: #218c21;
            font-size: 2.5em;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .subtitle {
            color: #176617;
            font-size: 1.1em;
            margin-bottom: 24px;
            font-weight: 500;
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
        
        /* Mobile responsive styles */
        @media screen and (max-width: 768px) {
            body {
                padding: 12px;
            }
            
            .home-container {
                padding: 40px 20px;
                border-radius: 12px;
                max-width: 100%;
            }
            
            .logo-section img {
                max-width: 80px;
            }
            
            .home-container h1 {
                font-size: 1.8em;
                margin-bottom: 8px;
            }
            
            .subtitle {
                font-size: 0.95em;
                margin-bottom: 24px;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 12px;
                width: 100%;
            }
            
            .btn-home {
                width: 100%;
                padding: 14px 20px;
                font-size: 1em;
                box-sizing: border-box;
            }
            
            .home-footer {
                margin-top: 32px;
                padding-top: 20px;
            }
            
            .home-footer a {
                font-size: 0.85em;
            }
        }
        
        /* Small mobile devices */
        @media screen and (max-width: 480px) {
            .home-container {
                padding: 32px 20px;
            }
            
            .logo-section img {
                max-width: 70px;
            }
            
            .home-container h1 {
                font-size: 1.5em;
            }
            
            .subtitle {
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="home-container">
        <div class="logo-section">
            <img src="uploads/OIP (1).webp" alt="Palawan National School Logo" title="Palawan National School">
            <img src="uploads/System logo.jpg" alt="QR Attendance System Logo" title="QR Attendance System" style="margin-left: 20px;">
        </div>
        <h1>Palawan National School</h1>
        <div class="subtitle">Hybrid QR Code Based Attendance Monitoring System<br>with AI-Powered Dashboards & Reviewing Center</div>
        <div class="btn-group">
            <a href="login.php" class="btn-home">Login</a>
            <a href="signup.php" class="btn-home btn-secondary">Sign Up</a>
        </div>
        
        <div class="home-footer">
            <a href="developer_login.php"> Developer Dashboard</a>
        </div>
    </div>
</body>
</html>