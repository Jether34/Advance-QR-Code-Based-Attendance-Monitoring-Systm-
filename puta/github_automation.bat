@echo off
color 0A
echo.
echo ========================================================
echo   Smart Attendance System - Complete GitHub Setup
echo   Username: Jether34
echo ========================================================
echo.

echo [STEP 1] Creating GitHub Repository...
echo.
echo Please follow these steps to create your GitHub repository:
echo.
echo 1. Open your browser and go to: https://github.com/new
echo 2. Repository name: Palawan National School QR Code Attendance Monitoring System
echo 3. Description: Modern QR code-based attendance tracking system for Palawan National School
echo 4. Set to PUBLIC (so team members can access)
echo 5. DON'T check "Add a README file" (we already have one)
echo 6. DON'T check "Add .gitignore" (we already have one)  
echo 7. DON'T check "Choose a license" (we can add later)
echo 8. Click "Create repository"
echo.
echo When you're done creating the repository, press any key to continue...
pause >nul

echo.
echo [STEP 2] Pushing code to GitHub...
echo.

REM Commit any changes to the username updates
git add . >nul 2>&1
git commit -m "update: set GitHub username to Jether34 in all files" >nul 2>&1

REM Push master branch
echo Pushing master branch...
git push -u origin master
if %errorlevel% equ 0 (
    echo ✓ Master branch pushed successfully!
) else (
    echo ! Error pushing master branch. Make sure the repository is created.
    echo   Repository URL: https://github.com/Jether34/Palawan-National-School-QR-Code-Attendance-Monitoring-System
    pause
    exit /b 1
)

REM Push develop branch  
echo Pushing develop branch...
git checkout develop >nul 2>&1
git push -u origin develop >nul 2>&1
git checkout master >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ Develop branch pushed successfully!
) else (
    echo ! Note: Develop branch may need manual push later
)

echo.
echo [STEP 3] Repository Setup Complete!
echo.
echo ========================================================
echo                   SUCCESS! 
echo ========================================================
echo.
echo Your repository is now live at:
echo https://github.com/Jether34/Palawan-National-School-QR-Code-Attendance-Monitoring-System
echo.
echo NEXT STEPS FOR TEAM COLLABORATION:
echo.
echo 1. ADD TEAM MEMBERS:
echo    - Go to: https://github.com/Jether34/Palawan-National-School-QR-Code-Attendance-Monitoring-System/settings/access
echo    - Click "Add people" 
echo    - Enter their GitHub usernames or email addresses
echo    - Give them "Write" permission for full collaboration
echo.
echo 2. TEAM MEMBERS SETUP:
echo    Send them this command to get started:
echo    git clone https://github.com/Jether34/Palawan-National-School-QR-Code-Attendance-Monitoring-System.git
echo    cd Palawan-National-School-QR-Code-Attendance-Monitoring-System
echo    .\team_setup.bat
echo.
echo 3. START VS CODE LIVE SHARE:
echo    - Open VS Code in this project folder
echo    - Install "Live Share" extension
echo    - Click "Live Share" in bottom status bar  
echo    - Share the invitation link with your team
echo    - Code together in real-time!
echo.
echo 4. MOBILE TESTING ACCESS:
echo    Your team can test QR codes at: http://192.168.3.35/puta
echo.
echo 5. DEVELOPMENT WORKFLOW:
echo    - Use 'develop' branch for new features
echo    - Create feature branches: git checkout -b feature/name
echo    - Submit pull requests for code review
echo    - Merge to master for releases
echo.
echo ========================================================
echo.
echo Press any key to open the repository in your browser...
pause >nul

REM Open repository in browser
start https://github.com/Jether34/Palawan-National-School-QR-Code-Attendance-Monitoring-System

echo.
echo Repository opened in browser. Happy coding with your team! 🚀
echo.