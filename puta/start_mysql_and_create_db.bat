@echo off
echo ===================================
echo STARTING MYSQL FOR ATTENDANCE SYSTEM
echo ===================================

echo Step 1: Starting MySQL Server...
start "MySQL Server" /MIN c:\xampp\mysql\bin\mysqld.exe --console

echo Waiting for MySQL to start...
timeout /t 5

echo Step 2: Testing MySQL connection...
c:\xampp\mysql\bin\mysql.exe -u root -e "SELECT 'MySQL is running!' as Status;"

if %ERRORLEVEL% EQU 0 (
    echo ✓ MySQL is running successfully!
    echo.
    echo Step 3: Creating database...
    c:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS attendance_qr_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    
    if %ERRORLEVEL% EQU 0 (
        echo ✓ Database 'attendance_qr_system' created!
        echo.
        echo Step 4: Creating tables...
        c:\xampp\mysql\bin\mysql.exe -u root attendance_qr_system < complete_database_setup.sql
        
        if %ERRORLEVEL% EQU 0 (
            echo ✓ All tables created successfully!
            echo.
            echo ===================================
            echo DATABASE SETUP COMPLETED!
            echo ===================================
            echo.
            echo You can now:
            echo 1. Visit http://localhost/puta/
            echo 2. Login with: teacher@test.com / password123
            echo 3. Or signup new accounts
            echo.
        ) else (
            echo ✗ Error creating tables. Check complete_database_setup.sql
        )
    ) else (
        echo ✗ Error creating database
    )
) else (
    echo ✗ MySQL connection failed. Please start XAMPP Control Panel first.
    echo   1. Open c:\xampp\xampp-control.exe
    echo   2. Click Start on MySQL
    echo   3. Then run this script again
)

pause