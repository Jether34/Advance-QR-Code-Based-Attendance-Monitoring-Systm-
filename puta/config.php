<?php
// config.php - Configuration file for QR Code URLs
// Update the SERVER_IP to match your computer's IP address

// IMPORTANT: To find your IP address, run this command in PowerShell:
// ipconfig
// Look for "IPv4 Address" under your active network connection (Wi-Fi or Ethernet)

// Current IP Configuration
define('SERVER_IP', '192.168.254.124'); // Change this to your computer's IP address
define('SERVER_PORT', ''); // Leave empty for default port 80, or specify like ':8080'
define('PROJECT_PATH', '/puta'); // Path to your project folder

// Full server URL for QR codes
define('SERVER_URL', 'http://' . SERVER_IP . SERVER_PORT . PROJECT_PATH);

// Instructions:
// 1. Find your computer's IP address using 'ipconfig' command
// 2. Update SERVER_IP above with your actual IP address
// 3. Make sure your phone is connected to the same Wi-Fi network
// 4. Ensure Windows Firewall allows Apache HTTP Server
// 5. Test by visiting the URL from your phone's browser first

/* 
TROUBLESHOOTING:
- If QR codes don't work from phone, check that both devices are on same network
- Try accessing http://YOUR_IP/puta/index.php from phone browser first
- Windows Firewall might be blocking connections - allow Apache HTTP Server
- Some routers block device-to-device communication (AP Isolation)
*/
?>