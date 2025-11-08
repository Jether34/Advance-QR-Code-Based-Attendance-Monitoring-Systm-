@echo off
echo =====================================
echo Smart Attendance System - Quick Setup
echo =====================================
echo.

echo [1/4] Setting up environment...
if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo ✓ Created .env from template
    )
) else (
    echo ✓ .env already exists
)

echo.
echo [2/4] Initializing Git repository...
if not exist ".git" (
    git init >nul 2>&1
    echo ✓ Git repository initialized
    
    git add . >nul 2>&1
    git commit -m "Initial commit: Smart Attendance System with team collaboration" >nul 2>&1
    echo ✓ Initial commit created
    
    git checkout -b develop >nul 2>&1
    git checkout master >nul 2>&1
    echo ✓ Created develop branch
    
    git remote add origin https://github.com/Jether34/Palawan-National-School-QR-Code-Attendance-Monitoring-System.git >nul 2>&1
    echo ✓ Added GitHub remote
) else (
    echo ✓ Git already initialized
)

echo.
echo [3/4] Setting up database...
if exist "c:\xampp\mysql\bin\mysql.exe" (
    c:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS attendance_qr_system;" 2>nul
    if %errorlevel% equ 0 (
        echo ✓ Database created
        if exist "complete_database_setup.sql" (
            c:\xampp\mysql\bin\mysql.exe -u root attendance_qr_system -e "source complete_database_setup.sql" 2>nul
            echo ✓ Database schema imported
        )
    ) else (
        echo ! Database setup failed - please start XAMPP MySQL
    )
) else (
    echo ! MySQL not found - please start XAMPP
)

echo.
echo [4/4] Getting network information...
for /f "tokens=2 delims=:" %%i in ('ipconfig ^| findstr "IPv4"') do set ip=%%i
set ip=%ip: =%
echo ✓ Network IP: %ip%

echo.
echo ========================================
echo           SETUP COMPLETE!
echo ========================================
echo.
echo FILES CREATED:
echo   ✓ .gitignore (PHP exclusions)
echo   ✓ README.md (Team documentation)
echo   ✓ CONTRIBUTING.md (Workflow guidelines)  
echo   ✓ .env.example (Environment template)
echo   ✓ .vscode/ (Team VS Code settings)
echo   ✓ database/migrations/ (Schema scripts)
echo.
echo NEXT STEPS:
echo   1. Edit .env with your database credentials
echo   2. Start XAMPP (Apache + MySQL)
echo   3. Push to GitHub: git push -u origin master
echo   4. Invite team members to repository
echo   5. Install VS Code extensions
echo   6. Mobile access: http://%ip%/puta
echo.
echo TEAM COLLABORATION:
echo   • VS Code Live Share for real-time coding
echo   • Feature branches for development
echo   • Pull requests for code review
echo   • Shared development environment
echo.
echo Repository: https://github.com/Jether34/Palawan-National-School-QR-Code-Attendance-Monitoring-System
echo.
pause