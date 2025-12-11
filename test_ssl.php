<?php
/**
 * SSL/TLS Connection Test
 * Tests HTTPS configuration and security headers
 */

require_once 'api_config.php';

// Set security headers
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSL/TLS Connection Test - QR Attendance System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }
        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2em;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .status-box {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .status-success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .status-warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
        }
        .status-info {
            background: #d1ecf1;
            border: 2px solid #17a2b8;
            color: #0c5460;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .info-label {
            font-weight: 600;
            color: #333;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        .info-value {
            color: #667eea;
            font-size: 1.1em;
            word-break: break-all;
        }
        .icon {
            font-size: 3em;
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            margin: 5px;
            text-align: center;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        .security-headers {
            margin-top: 20px;
        }
        .header-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 8px;
            font-family: monospace;
            font-size: 0.9em;
        }
        .check {
            color: #28a745;
            font-weight: bold;
        }
        .cross {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        $is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $protocol = $is_https ? 'HTTPS' : 'HTTP';
        $server_ip = $_SERVER['SERVER_ADDR'] ?? 'Unknown';
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $port = $_SERVER['SERVER_PORT'] ?? 'Unknown';
        $host = $_SERVER['HTTP_HOST'] ?? 'Unknown';

        // Determine network
        $network = 'Unknown';
        if (strpos($server_ip, '192.168.254.') === 0) {
            $network = 'Network B (192.168.254.x)';
        } elseif (strpos($server_ip, '192.168.1.') === 0) {
            $network = 'Network A (192.168.1.x)';
        } elseif ($server_ip === '127.0.0.1' || $server_ip === '::1') {
            $network = 'Localhost (Development)';
        }
        ?>

        <div class="icon">
            <?php if ($is_https): ?>
                🔒
            <?php else: ?>
                ⚠️
            <?php endif; ?>
        </div>

        <h1>SSL/TLS Connection Test</h1>
        <p class="subtitle">QR Attendance System Security Check</p>

        <div class="status-box <?php echo $is_https ? 'status-success' : 'status-warning'; ?>">
            <h2 style="margin-bottom: 10px;">
                <?php if ($is_https): ?>
                    <span class="check">✓</span> Connection is SECURE
                <?php else: ?>
                    <span class="cross">✗</span> Connection is NOT SECURE
                <?php endif; ?>
            </h2>
            <p>
                <?php if ($is_https): ?>
                    Your connection is encrypted using <strong><?php echo $protocol; ?></strong> protocol.
                    All data transmitted between your device and the server is protected.
                <?php else: ?>
                    You are using <strong><?php echo $protocol; ?></strong> protocol.
                    Data is transmitted in plain text and can be intercepted.
                    Please use HTTPS for secure access.
                <?php endif; ?>
            </p>
        </div>

        <div class="status-box status-info">
            <h3 style="margin-bottom: 15px;">Connection Details</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Protocol</div>
                    <div class="info-value"><?php echo $protocol; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Port</div>
                    <div class="info-value"><?php echo $port; ?> <?php echo $port == 443 ? '(HTTPS)' : ($port == 80 ? '(HTTP)' : ''); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Server IP</div>
                    <div class="info-value"><?php echo $server_ip; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Your IP</div>
                    <div class="info-value"><?php echo $client_ip; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Host</div>
                    <div class="info-value"><?php echo $host; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Network</div>
                    <div class="info-value"><?php echo $network; ?></div>
                </div>
            </div>
        </div>

        <?php if ($is_https): ?>
        <div class="security-headers">
            <h3 style="margin-bottom: 15px;">Security Features Enabled</h3>
            <div class="header-item">
                <span class="check">✓</span> <strong>TLS Encryption:</strong> Data encrypted in transit
            </div>
            <div class="header-item">
                <span class="check">✓</span> <strong>HSTS:</strong> HTTP Strict Transport Security enabled
            </div>
            <div class="header-item">
                <span class="check">✓</span> <strong>X-Frame-Options:</strong> Clickjacking protection (SAMEORIGIN)
            </div>
            <div class="header-item">
                <span class="check">✓</span> <strong>X-Content-Type-Options:</strong> MIME sniffing protection (nosniff)
            </div>
            <div class="header-item">
                <span class="check">✓</span> <strong>X-XSS-Protection:</strong> Cross-site scripting filter enabled
            </div>
            <div class="header-item">
                <span class="check">✓</span> <strong>Referrer-Policy:</strong> Privacy protection configured
            </div>
        </div>
        <?php else: ?>
        <div class="security-headers">
            <h3 style="margin-bottom: 15px;">Security Recommendations</h3>
            <div class="header-item">
                <span class="cross">✗</span> <strong>No Encryption:</strong> Data transmitted in plain text
            </div>
            <div class="header-item">
                <span class="cross">✗</span> <strong>No HSTS:</strong> Browser may connect via HTTP
            </div>
            <div class="header-item">
                <span class="cross">✗</span> <strong>Vulnerable:</strong> Man-in-the-middle attacks possible
            </div>
            <p style="margin-top: 15px; padding: 15px; background: #fff3cd; border-radius: 8px;">
                <strong>⚠️ Action Required:</strong> Run <code>setup_ssl.ps1</code> to enable HTTPS encryption.
                See <code>SSL_TLS_GUIDE.md</code> for detailed instructions.
            </p>
        </div>
        <?php endif; ?>

        <div class="actions">
            <?php if ($is_https): ?>
                <a href="https://<?php echo $host; ?>/puta" class="btn btn-success">
                    ✓ Go to System (Secure)
                </a>
            <?php else: ?>
                <a href="http://<?php echo $host; ?>/puta" class="btn">
                    Go to System (HTTP)
                </a>
                <?php
                $https_host = str_replace(':' . $port, ':443', $host);
                ?>
                <a href="https://<?php echo $https_host; ?>/puta" class="btn btn-success">
                    Try HTTPS (Recommended)
                </a>
            <?php endif; ?>
            <a href="SSL_TLS_GUIDE.md" class="btn">
                📖 View SSL/TLS Guide
            </a>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px; text-align: center;">
            <p style="color: #666; margin-bottom: 10px;">
                <strong>Ollama AI Endpoint:</strong> <?php echo OLLAMA_API_URL; ?>
            </p>
            <p style="color: #666; font-size: 0.9em;">
                Detected network configuration is automatically applied
            </p>
        </div>
    </div>
</body>
</html>
