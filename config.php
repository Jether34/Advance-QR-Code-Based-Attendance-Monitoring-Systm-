<?php


// the current system setup is under development and not yet ready for deployment
// I added some tweaks on security measurements ensuring that all data collected is being monitored and well controlled under the local servers 
// config.php - Configuration file for QR Code URLs
// Update the SERVER_IP to match your computer's IP address


//next week phase 2 deployment will be scheduled:
//1. Cloud static testing
//2. Security audit and penetration testing
//3. Final review and code optimization
//4. Deployment to production environment
//5. Testing and monitoring after deployment
//6. User training and documentation preparation
//7. Documentation finalization and project closure
//


//next week implementation of SSL certificate for secure connections
//next week implementation on data encryption for sensitive user information
//next week implementation of regular security audits and vulnerability assessments
//next week implementation on local area network (LAN) security measures
//deployment via local server only (no internet access) for enhanced security
//implement 
// implementation of firewall rules to restrict unauthorized access
// implementation of secure coding practices to prevent common vulnerabilities
// implementation of regular software updates and patch management

// IMPORTANT: To find your IP address, run this command in PowerShell:
// ipconfig
// Look for "IPv4 Address" under your active network connection (Wi-Fi or Ethernet)

// Current IP Configuration
define('SERVER_IP', '192.168.1.12'); // Changed from 192.168.254.124 to current WiFi IP
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

//SSL/TLS Implementation for secure connections will be planned for the next patch (12-09-2025 at 11AM Manila zone)
// SSL certificate setup instructions will be provided in the setup_ssl.ps1 file
// Reminder: Regularly update your server software to maintain security
// further customization and security features will be added in the next patch updates

?>