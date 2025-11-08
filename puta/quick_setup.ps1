# Smart Attendance System - Quick Setup Script
# Simple automation for GitHub collaboration

param(
    [string]$GitHubUsername = "yourusername",
    [switch]$Help = $false
)

if ($Help) {
    Write-Host "Smart Attendance System - Quick Setup" -ForegroundColor Green
    Write-Host "Usage: .\quick_setup.ps1 [-GitHubUsername 'username']" -ForegroundColor Cyan
    exit 0
}

Write-Host "🎓 Smart Attendance System - Quick Setup" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green

# 1. Setup Environment
Write-Host "`n📋 Setting up environment..." -ForegroundColor Cyan
if (-not (Test-Path ".env") -and (Test-Path ".env.example")) {
    Copy-Item ".env.example" ".env"
    Write-Host "✅ Created .env from template" -ForegroundColor Green
}

# 2. Initialize Git
Write-Host "`n🔧 Initializing Git repository..." -ForegroundColor Cyan
if (-not (Test-Path ".git")) {
    git init
    Write-Host "✅ Git repository initialized" -ForegroundColor Green
    
    git add .
    git commit -m "Initial commit: Smart Attendance System with team collaboration setup"
    Write-Host "✅ Initial commit created" -ForegroundColor Green
    
    # Create develop branch
    git checkout -b develop
    git checkout master
    Write-Host "✅ Created develop branch" -ForegroundColor Green
    
    # Add remote
    $remoteUrl = "https://github.com/$GitHubUsername/smart-attendance-system.git"
    git remote add origin $remoteUrl
    Write-Host "✅ Added remote: $remoteUrl" -ForegroundColor Green
} else {
    Write-Host "ℹ️  Git already initialized" -ForegroundColor Yellow
}

# 3. Database Setup
Write-Host "`n🗄️  Setting up database..." -ForegroundColor Cyan
$mysqlPath = "c:\xampp\mysql\bin\mysql.exe"
if (Test-Path $mysqlPath) {
    try {
        & $mysqlPath -u root -e "CREATE DATABASE IF NOT EXISTS attendance_qr_system;"
        Write-Host "✅ Database created" -ForegroundColor Green
        
        if (Test-Path "complete_database_setup.sql") {
            & $mysqlPath -u root attendance_qr_system -e "source complete_database_setup.sql"
            Write-Host "✅ Database schema imported" -ForegroundColor Green
        }
    }
    catch {
        Write-Host "⚠️  Database setup failed - please run manually" -ForegroundColor Yellow
    }
} else {
    Write-Host "⚠️  MySQL not found - please start XAMPP" -ForegroundColor Yellow
}

# 4. Get IP Address
$ip = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -like "192.168.*" }).IPAddress | Select-Object -First 1
if ($ip) {
    Write-Host "🌐 Network IP detected: $ip" -ForegroundColor Cyan
}

# 5. Summary
Write-Host "`n🎉 Setup Complete!" -ForegroundColor Green
Write-Host "=================" -ForegroundColor Green
Write-Host ""
Write-Host "📁 Files Created:" -ForegroundColor Yellow
Write-Host "   ✅ .gitignore (PHP exclusions)"
Write-Host "   ✅ README.md (Team documentation)"  
Write-Host "   ✅ CONTRIBUTING.md (Workflow guidelines)"
Write-Host "   ✅ .env.example (Environment template)"
Write-Host "   ✅ .vscode/ (Team VS Code settings)"
Write-Host "   ✅ database/migrations/ (Schema scripts)"
Write-Host ""
Write-Host "🚀 Next Steps:" -ForegroundColor Yellow
Write-Host "   1. Edit .env with your database credentials"
Write-Host "   2. Start XAMPP (Apache + MySQL)"
Write-Host "   3. Push to GitHub: git push -u origin master"
Write-Host "   4. Invite team members to repository"
Write-Host "   5. Install VS Code extensions (see .vscode/extensions.json)"
if ($ip) {
    Write-Host "   6. Mobile access: http://$ip/puta"
}
Write-Host ""
Write-Host "👥 Team Collaboration:" -ForegroundColor Yellow
Write-Host "   • VS Code Live Share for real-time coding"
Write-Host "   • Feature branches: git checkout -b feature/name"
Write-Host "   • Pull requests for code review"
Write-Host "   • Shared development environment"
Write-Host ""
Write-Host "🔗 Repository: https://github.com/$GitHubUsername/smart-attendance-system" -ForegroundColor Cyan