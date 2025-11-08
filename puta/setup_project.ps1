# Smart Attendance System - Automated Setup Script
# This script automates the entire project setup for GitHub collaboration

param(
    [string]$GitHubUsername = "",
    [string]$RepositoryName = "smart-attendance-system",
    [switch]$SkipGitInit = $false,
    [switch]$SkipDatabase = $false,
    [switch]$Help = $false
)

# Display help information
if ($Help) {
    Write-Host "Smart Attendance System - Automated Setup Script" -ForegroundColor Green
    Write-Host "=================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Usage: .\setup_project.ps1 [OPTIONS]"
    Write-Host ""
    Write-Host "Options:"
    Write-Host "  -GitHubUsername    Your GitHub username (required for remote setup)"
    Write-Host "  -RepositoryName    Repository name (default: smart-attendance-system)"
    Write-Host "  -SkipGitInit      Skip Git initialization"
    Write-Host "  -SkipDatabase     Skip database setup"
    Write-Host "  -Help             Show this help message"
    Write-Host ""
    Write-Host "Examples:"
    Write-Host "  .\setup_project.ps1 -GitHubUsername 'myusername'"
    Write-Host "  .\setup_project.ps1 -SkipDatabase"
    exit 0
}

# Color functions for better output
function Write-Success { param($Message) Write-Host "✅ $Message" -ForegroundColor Green }
function Write-Info { param($Message) Write-Host "ℹ️  $Message" -ForegroundColor Cyan }
function Write-Warning { param($Message) Write-Host "⚠️  $Message" -ForegroundColor Yellow }
function Write-Error { param($Message) Write-Host "❌ $Message" -ForegroundColor Red }
function Write-Header { param($Message) Write-Host "`n🚀 $Message" -ForegroundColor Magenta -BackgroundColor Black }

# Check prerequisites
function Test-Prerequisites {
    Write-Header "Checking Prerequisites"
    
    # Check if Git is installed
    try {
        $gitVersion = git --version
        Write-Success "Git is installed: $gitVersion"
    }
    catch {
        Write-Error "Git is not installed. Please install Git and try again."
        exit 1
    }
    
    # Check if XAMPP MySQL is running
    $mysqlProcess = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
    if (-not $mysqlProcess) {
        Write-Warning "MySQL is not running. Please start XAMPP MySQL service."
        $response = Read-Host "Continue anyway? (y/n)"
        if ($response -ne 'y') { exit 1 }
    } else {
        Write-Success "MySQL service is running"
    }
    
    # Check if PHP is available
    try {
        $phpPath = "c:\xampp\php\php.exe"
        if (Test-Path $phpPath) {
            $phpVersion = & $phpPath -v
            Write-Success "PHP is available: XAMPP PHP"
        }
    }
    catch {
        Write-Warning "PHP not found in XAMPP path"
    }
}

# Setup environment configuration
function Setup-Environment {
    Write-Header "Setting Up Environment Configuration"
    
    if (-not (Test-Path ".env")) {
        if (Test-Path ".env.example") {
            Copy-Item ".env.example" ".env"
            Write-Success "Created .env file from template"
            
            # Get current IP address
            $ipAddress = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -like "192.168.*" -or $_.IPAddress -like "10.*" }).IPAddress | Select-Object -First 1
            if ($ipAddress) {
                (Get-Content ".env") -replace "SERVER_IP=192.168.1.XXX", "SERVER_IP=$ipAddress" | Set-Content ".env"
                Write-Success "Updated SERVER_IP to: $ipAddress"
            }
            
            Write-Info "Please edit .env file to configure your database credentials"
        } else {
            Write-Warning ".env.example not found. Creating basic .env file..."
            $envContent = @"
# Database Configuration
DB_HOST=localhost
DB_NAME=attendance_qr_system
DB_USER=root
DB_PASS=

# Server Configuration
SERVER_IP=localhost
"@
            $envContent | Out-File -FilePath ".env" -Encoding UTF8
            Write-Success "Created basic .env file"
        }
    } else {
        Write-Info ".env file already exists"
    }
}

# Initialize Git repository
function Initialize-Git {
    Write-Header "Initializing Git Repository"
    
    if ($SkipGitInit) {
        Write-Info "Skipping Git initialization"
        return
    }
    
    if (-not (Test-Path ".git")) {
        git init
        Write-Success "Initialized Git repository"
        
        # Set up initial commit
        git add .
        $commitMessage = @"
Initial commit: Smart Attendance System

- Complete QR code attendance tracking system
- Embedded student information in QR codes  
- Automatic attendance recording
- Teacher and student dashboards
- Mobile-optimized scanning interface
- Role-based access control
"@
        git commit -m $commitMessage
        
        Write-Success "Created initial commit"
        
        # Create develop branch
        git checkout -b develop
        git checkout main
        Write-Success "Created develop branch"
        
        if ($GitHubUsername) {
            $remoteUrl = "https://github.com/$GitHubUsername/$RepositoryName.git"
            git remote add origin $remoteUrl
            Write-Success "Added remote origin: $remoteUrl"
            Write-Info "To push to GitHub, run: git push -u origin main"
        } else {
            Write-Warning "No GitHub username provided. Remote origin not set."
            Write-Info "To add remote later: git remote add origin https://github.com/USERNAME/REPO.git"
        }
    } else {
        Write-Info "Git repository already initialized"
    }
}

# Setup database
function Setup-Database {
    Write-Header "Setting Up Database"
    
    if ($SkipDatabase) {
        Write-Info "Skipping database setup"
        return
    }
    
    $mysqlPath = "c:\xampp\mysql\bin\mysql.exe"
    
    if (-not (Test-Path $mysqlPath)) {
        Write-Error "MySQL not found at $mysqlPath"
        Write-Info "Please ensure XAMPP is installed and MySQL is in the correct path"
        return
    }
    
    try {
        # Create database
        & $mysqlPath -u root -e "CREATE DATABASE IF NOT EXISTS attendance_qr_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        Write-Success "Database 'attendance_qr_system' created or already exists"
        
        # Run migrations if they exist
        if (Test-Path "database\migrations\001_initial_schema.sql") {
            & $mysqlPath -u root attendance_qr_system -e "source database/migrations/001_initial_schema.sql"
            Write-Success "Applied initial schema migration"
        }
        
        if (Test-Path "database\migrations\002_default_data.sql") {
            & $mysqlPath -u root attendance_qr_system -e "source database/migrations/002_default_data.sql"
            Write-Success "Applied default data migration"
        }
        
        # Import existing complete setup if migrations don't exist
        if (Test-Path "complete_database_setup.sql") {
            & $mysqlPath -u root attendance_qr_system -e "source complete_database_setup.sql"
            Write-Success "Imported complete database setup"
        }
        
    }
    catch {
        Write-Error "Database setup failed: $($_.Exception.Message)"
        Write-Info "You may need to run database setup manually"
    }
}

# Create VS Code workspace configuration
function Setup-VSCodeWorkspace {
    Write-Header "Setting Up VS Code Workspace"
    
    if (-not (Test-Path ".vscode")) {
        New-Item -ItemType Directory -Path ".vscode" -Force | Out-Null
    }
    
    # Extensions recommendations
    $extensions = @{
        "recommendations" = @(
            "ms-vscode.live-share",
            "bmewburn.vscode-intelephense-client", 
            "eamodio.gitlens",
            "ms-vscode.vscode-json",
            "formulahendry.auto-rename-tag",
            "bradlc.vscode-tailwindcss",
            "christian-kohler.path-intellisense",
            "ms-vscode.powershell",
            "mtxr.sqltools",
            "mtxr.sqltools-driver-mysql"
        )
    }
    
    $extensions | ConvertTo-Json -Depth 3 | Out-File -FilePath ".vscode\extensions.json" -Encoding UTF8
    Write-Success "Created VS Code extensions recommendations"
    
    # Workspace settings
    $settings = @{
        "php.suggest.basic" = $false
        "intelephense.files.maxSize" = 1000000
        "files.associations" = @{
            "*.php" = "php"
        }
        "emmet.includeLanguages" = @{
            "php" = "html"
        }
    }
    
    $settings | ConvertTo-Json -Depth 3 | Out-File -FilePath ".vscode\settings.json" -Encoding UTF8
    Write-Success "Created VS Code workspace settings"
}

# Generate project summary
function Generate-Summary {
    Write-Header "Project Setup Summary"
    
    Write-Host ""
    Write-Host "📁 Project Structure:" -ForegroundColor Yellow
    Write-Host "   ├── 📄 README.md (Updated with team collaboration info)"
    Write-Host "   ├── 📄 CONTRIBUTING.md (Team guidelines and workflow)"
    Write-Host "   ├── 📄 .gitignore (PHP project exclusions)"
    Write-Host "   ├── 📄 .env.example (Environment template)"
    Write-Host "   ├── 📄 .env (Your local configuration)"
    Write-Host "   ├── 📁 database/migrations/ (Database setup scripts)"
    Write-Host "   └── 📁 .vscode/ (VS Code team settings)"
    Write-Host ""
    
    Write-Host "🔧 Next Steps:" -ForegroundColor Yellow
    Write-Host "   1. Edit .env file with your database credentials"
    Write-Host "   2. Start XAMPP Apache and MySQL services"
    Write-Host "   3. Access: http://localhost/$((Get-Location).Name)"
    if ($GitHubUsername) {
        Write-Host "   4. Push to GitHub: git push -u origin main"
        Write-Host "   5. Invite team members to: https://github.com/$GitHubUsername/$RepositoryName"
    } else {
        Write-Host "   4. Create GitHub repository and add remote"
        Write-Host "   5. Push code: git push -u origin main"
    }
    Write-Host "   6. Install recommended VS Code extensions"
    Write-Host "   7. Start Live Share session for team collaboration"
    Write-Host ""
    
    Write-Host "🌐 Team Collaboration:" -ForegroundColor Yellow
    Write-Host "   • Use 'git checkout develop' for new features"
    Write-Host "   • Create feature branches: 'git checkout -b feature/feature-name'"
    Write-Host "   • Submit pull requests for code review"
    Write-Host "   • Use VS Code Live Share for real-time collaboration"
    Write-Host ""
    
    $currentIP = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -like "192.168.*" -or $_.IPAddress -like "10.*" }).IPAddress | Select-Object -First 1
    if ($currentIP) {
        Write-Host "📱 Mobile Access:" -ForegroundColor Yellow
        Write-Host "   Network URL: http://$currentIP/$((Get-Location).Name)"
        Write-Host "   Use this URL for mobile QR code scanning"
    }
}

# Main execution
function Main {
    Clear-Host
    Write-Host "🎓 Smart Attendance System - Automated Setup" -ForegroundColor Green -BackgroundColor Black
    Write-Host "=============================================" -ForegroundColor Green
    Write-Host ""
    
    Test-Prerequisites
    Setup-Environment
    Initialize-Git
    Setup-Database  
    Setup-VSCodeWorkspace
    Generate-Summary
    
    Write-Host ""
    Write-Success "Project setup completed successfully! 🎉"
    Write-Host ""
    Write-Info "Run '.\setup_project.ps1 -Help' for more options"
}

# Execute main function
Main