<?php
// test_connection.php - Simple test page to verify mobile connectivity
require_once __DIR__ . '/config.php';

$user_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$server_ip = SERVER_IP;
$current_time = date('F j, Y \a\t g:i:s A');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connection Test - School QR System</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #d6f5d6 0%, #eaffea 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 50px rgba(33, 140, 33, 0.2);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .success {
            color: #218c21;
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #218c21;
            margin-bottom: 20px;
        }
        
        .info-box {
            background: #f8fff8;
            border: 2px solid #d4edda;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .info-item {
            margin: 10px 0;
            font-size: 1.1em;
        }
        
        .label {
            font-weight: bold;
            color: #176617;
        }
        
        .value {
            color: #333;
            font-family: 'Courier New', monospace;
            background: #f1f3f4;
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        .btn {
            background: linear-gradient(135deg, #218c21, #2ecc71);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1.1em;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(33, 140, 33, 0.4);
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
            
            .success {
                font-size: 3em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success">✅</div>
        <h1>Connection Successful!</h1>
        <p>Great! Your device can successfully connect to the QR Code system.</p>
        
        <div class="info-box">
            <div class="info-item">
                <span class="label">Your IP Address:</span>
                <span class="value"><?php echo htmlspecialchars($user_ip); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Server IP:</span>
                <span class="value"><?php echo htmlspecialchars($server_ip); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Connection Time:</span>
                <span class="value"><?php echo $current_time; ?></span>
            </div>
            <div class="info-item">
                <span class="label">Server URL:</span>
                <span class="value"><?php echo SERVER_URL; ?></span>
            </div>
        </div>
        
        <p><strong>🎉 QR Code scanning should work perfectly now!</strong></p>
        <p>All student QR codes will open properly on this device.</p>
        
        <div style="margin-top: 30px;">
            <a href="index.php" class="btn">🏠 Go to Home Page</a>
            <a href="student_info.php?id=SAMPLE123" class="btn">📄 Test Student Info Page</a>
        </div>
        
        <div style="margin-top: 20px; font-size: 0.9em; color: #666;">
            <strong>Network Status:</strong> Connected ✅ | 
            <strong>System:</strong> School QR System v2.0 |
            <strong>Device:</strong> Mobile Compatible 📱
        </div>
    </div>
</body>
</html>