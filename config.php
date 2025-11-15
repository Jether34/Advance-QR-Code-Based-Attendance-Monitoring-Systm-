<?php
// config.php - Configuration with environment variable overrides (.env)
// Values in environment replace defaults when provided.

// IMPORTANT: Use PowerShell `ipconfig` to find IPv4.

$envServerIp    = getenv('SERVER_IP');
$envServerPort  = getenv('SERVER_PORT'); // Expect plain port number (e.g. 8080) or blank.
$envProjectPath = getenv('PROJECT_PATH');

define('SERVER_IP',    ($envServerIp !== false && $envServerIp !== '') ? $envServerIp : '192.168.254.124');
define('SERVER_PORT',  ($envServerPort !== false && $envServerPort !== '') ? $envServerPort : '');
define('PROJECT_PATH', ($envProjectPath !== false && $envProjectPath !== '') ? $envProjectPath : '/puta');

// Build URL (add colon only if port provided)
define('SERVER_URL', 'http://' . SERVER_IP . (SERVER_PORT !== '' ? ':' . SERVER_PORT : '') . PROJECT_PATH);

// Instructions:
// 1. Configure .env (see .env.example) or rely on defaults.
// 2. Ensure phone shares network with server.
// 3. Allow Apache through Windows Firewall.
// 4. Visit SERVER_URL in mobile browser before testing QR.

/*
TROUBLESHOOTING:
- Verify network connectivity (no AP isolation).
- Test http://SERVER_IP/puta/index.php directly.
- Confirm firewall rules.
*/
?>
