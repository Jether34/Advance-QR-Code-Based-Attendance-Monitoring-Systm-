@echo off
echo =======================================
echo    QR Code Mobile Access Setup
echo =======================================
echo.
echo This will configure Windows Firewall to allow mobile devices
echo to connect to your XAMPP server for QR code scanning.
echo.
echo Current computer IP address:
ipconfig | findstr IPv4
echo.
pause
echo.
echo Adding Windows Firewall rules...

:: Add inbound rule for HTTP (port 80)
netsh advfirewall firewall add rule name="XAMPP Apache HTTP - QR System" dir=in action=allow protocol=TCP localport=80

:: Add rule for Apache executable
netsh advfirewall firewall add rule name="XAMPP Apache HTTP Server" dir=in action=allow program="C:\xampp\apache\bin\httpd.exe"

echo.
echo ✅ Firewall rules added successfully!
echo.
echo Next steps:
echo 1. Make sure your phone is on the same Wi-Fi network
echo 2. Update the IP address in config.php if needed
echo 3. Test by visiting http://YOUR_IP/puta/test_connection.php from your phone
echo.
echo =======================================
echo    Setup Complete!
echo =======================================
pause