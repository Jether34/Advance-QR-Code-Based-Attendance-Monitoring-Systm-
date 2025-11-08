@echo off
REM Database Setup Script for Attendance QR System
REM This script will create the MySQL database and all required tables

echo ======================================
echo  ATTENDANCE QR SYSTEM - DATABASE SETUP
echo ======================================
echo.

REM Check if XAMPP MySQL is running
echo Checking MySQL service...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo MySQL is not running. Please start XAMPP first!
    echo Starting XAMPP MySQL...
    start "" "c:\xampp\xampp_start.exe"
    timeout /t 10
)

echo.
echo Creating database and tables...
echo.

REM Create database
echo Step 1: Creating database 'attendance_qr_system'...
c:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS attendance_qr_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if %ERRORLEVEL% NEQ 0 (
    echo Error: Failed to create database. Please check your MySQL installation.
    pause
    exit /b 1
)

REM Import complete database setup
echo Step 2: Creating all tables and sample data...
c:\xampp\mysql\bin\mysql.exe -u root attendance_qr_system < complete_database_setup.sql

if %ERRORLEVEL% NEQ 0 (
    echo Error: Failed to import database schema.
    pause
    exit /b 1
)

echo.
echo ======================================
echo  DATABASE SETUP COMPLETED SUCCESSFULLY!
echo ======================================
echo.
echo Database: attendance_qr_system
echo Tables created:
echo   - teachers
echo   - students  
echo   - attendance_records
echo   - profile_edits
echo   - posts
echo   - post_likes
echo   - post_comments
echo.
echo Sample users created:
echo   Teachers: mr.santos@example.com, ms.garcia@example.com, mr.reyes@example.com
echo   Students: juan@example.com, maria@example.com, jose@example.com
echo   Default password for all: password123
echo.
echo Next steps:
echo 1. Visit http://localhost/puta/signup.php to create more users
echo 2. Visit http://localhost/puta/login.php to login
echo 3. Visit http://localhost/puta/scan.php to test QR scanning
echo.
pause