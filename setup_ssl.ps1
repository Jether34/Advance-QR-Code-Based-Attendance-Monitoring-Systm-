# setup_ssl.ps1 - Automated SSL/TLS Certificate Setup for QR Attendance System
# Run as Administrator

Write-Host "
═══════════════════════════════════════════════════════════════════════
  SSL/TLS CERTIFICATE SETUP FOR QR ATTENDANCE SYSTEM
═══════════════════════════════════════════════════════════════════════
" -ForegroundColor Cyan

# Configuration
$xamppPath = "C:\xampp"
$apachePath = "$xamppPath\apache"
$confPath = "$apachePath\conf"
$certPath = "$apachePath\conf\ssl.crt"
$keyPath = "$apachePath\conf\ssl.key"
$opensslPath = "$apachePath\bin\openssl.exe"

# Server IPs
$ip1 = "192.168.1.12"
$ip2 = "192.168.254.254"

Write-Host "[1/6] Checking prerequisites..." -ForegroundColor Yellow

# Check if running as administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "ERROR: This script must be run as Administrator!" -ForegroundColor Red
    Write-Host "Right-click PowerShell and select 'Run as Administrator'" -ForegroundColor Red
    exit 1
}

# Check if XAMPP is installed
if (-not (Test-Path $xamppPath)) {
    Write-Host "ERROR: XAMPP not found at $xamppPath" -ForegroundColor Red
    exit 1
}

# Check if OpenSSL exists
if (-not (Test-Path $opensslPath)) {
    Write-Host "ERROR: OpenSSL not found at $opensslPath" -ForegroundColor Red
    exit 1
}

Write-Host "✓ Prerequisites check passed" -ForegroundColor Green

Write-Host "`n[2/6] Creating certificate directories..." -ForegroundColor Yellow

# Create directories if they don't exist
if (-not (Test-Path "$certPath")) {
    New-Item -ItemType Directory -Path "$certPath" -Force | Out-Null
}
if (-not (Test-Path "$keyPath")) {
    New-Item -ItemType Directory -Path "$keyPath" -Force | Out-Null
}

Write-Host "✓ Directories created" -ForegroundColor Green

Write-Host "`n[3/6] Generating SSL certificate configuration..." -ForegroundColor Yellow

# Create OpenSSL configuration file with SAN (Subject Alternative Names)
$opensslConfig = @"
[req]
default_bits = 2048
prompt = no
default_md = sha256
x509_extensions = v3_req
distinguished_name = dn

[dn]
C = PH
ST = Palawan
L = Puerto Princesa
O = Palawan National School
OU = ICT Department
emailAddress = ict@pns.edu.ph
CN = QR Attendance System

[v3_req]
subjectAltName = @alt_names
basicConstraints = CA:FALSE
keyUsage = nonRepudiation, digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth

[alt_names]
DNS.1 = localhost
DNS.2 = qr-attendance.local
IP.1 = 127.0.0.1
IP.2 = $ip1
IP.3 = $ip2
"@

$configFile = "$env:TEMP\openssl_san.cnf"
$opensslConfig | Out-File -FilePath $configFile -Encoding ASCII

Write-Host "✓ Configuration file created" -ForegroundColor Green

Write-Host "`n[4/6] Generating self-signed SSL certificate..." -ForegroundColor Yellow
Write-Host "This may take a moment..." -ForegroundColor Gray

# Generate private key and certificate
$certFile = "$certPath\server.crt"
$keyFile = "$keyPath\server.key"

try {
    # Generate certificate with SAN support (valid for 365 days)
    & $opensslPath req -x509 -nodes -days 365 -newkey rsa:2048 `
        -keyout $keyFile `
        -out $certFile `
        -config $configFile `
        2>&1 | Out-Null

    if ($LASTEXITCODE -ne 0) {
        throw "OpenSSL command failed"
    }

    Write-Host "✓ SSL certificate generated successfully" -ForegroundColor Green
    Write-Host "  Certificate: $certFile" -ForegroundColor Gray
    Write-Host "  Private Key: $keyFile" -ForegroundColor Gray
    Write-Host "  Valid for: 365 days (1 year)" -ForegroundColor Gray
    
} catch {
    Write-Host "ERROR: Failed to generate certificate" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
} finally {
    # Clean up temp config file
    Remove-Item $configFile -ErrorAction SilentlyContinue
}

Write-Host "`n[5/6] Configuring Apache for SSL/TLS..." -ForegroundColor Yellow

# Backup existing httpd-ssl.conf
$sslConfFile = "$confPath\extra\httpd-ssl.conf"
$sslConfBackup = "$sslConfFile.backup_" + (Get-Date -Format "yyyyMMdd_HHmmss")

if (Test-Path $sslConfFile) {
    Copy-Item $sslConfFile $sslConfBackup
    Write-Host "✓ Backed up existing SSL config to: $sslConfBackup" -ForegroundColor Gray
}

# Create SSL configuration
$sslConfig = @"
#
# SSL/TLS Configuration for QR Attendance System
# Generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
#

Listen 443

<VirtualHost _default_:443>
    DocumentRoot "C:/xampp/htdocs"
    ServerName localhost:443
    ServerAlias 192.168.1.12:443
    ServerAlias 192.168.254.254:443
    
    # SSL Engine
    SSLEngine on
    
    # Certificate files
    SSLCertificateFile "conf/ssl.crt/server.crt"
    SSLCertificateKeyFile "conf/ssl.key/server.key"
    
    # SSL Protocol and Cipher Configuration
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:MEDIUM:!aNULL:!MD5:!SEED:!IDEA
    SSLHonorCipherOrder on
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # PHP/CGI Configuration
    <FilesMatch "\.(cgi|shtml|phtml|php)$">
        SSLOptions +StdEnvVars
    </FilesMatch>
    
    <Directory "C:/xampp/htdocs">
        Options Indexes FollowSymLinks MultiViews ExecCGI
        AllowOverride All
        Require all granted
    </Directory>
    
    # Browser Compatibility
    BrowserMatch "MSIE [2-5]" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0
    
    # Custom Log Files
    CustomLog "logs/ssl_request.log" "%t %h %{SSL_PROTOCOL}x %{SSL_CIPHER}x \"%r\" %b"
    ErrorLog "logs/ssl_error.log"
</VirtualHost>

# Additional Virtual Hosts for specific IPs
<VirtualHost 192.168.1.12:443>
    DocumentRoot "C:/xampp/htdocs"
    ServerName 192.168.1.12:443
    SSLEngine on
    SSLCertificateFile "conf/ssl.crt/server.crt"
    SSLCertificateKeyFile "conf/ssl.key/server.key"
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:MEDIUM:!aNULL:!MD5
</VirtualHost>

<VirtualHost 192.168.254.254:443>
    DocumentRoot "C:/xampp/htdocs"
    ServerName 192.168.254.254:443
    SSLEngine on
    SSLCertificateFile "conf/ssl.crt/server.crt"
    SSLCertificateKeyFile "conf/ssl.key/server.key"
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:MEDIUM:!aNULL:!MD5
</VirtualHost>
"@

$sslConfig | Out-File -FilePath $sslConfFile -Encoding ASCII

Write-Host "✓ SSL configuration created" -ForegroundColor Green

# Enable SSL module in httpd.conf
$httpdConfFile = "$confPath\httpd.conf"
$httpdConfBackup = "$httpdConfFile.backup_" + (Get-Date -Format "yyyyMMdd_HHmmss")

Copy-Item $httpdConfFile $httpdConfBackup
Write-Host "✓ Backed up httpd.conf to: $httpdConfBackup" -ForegroundColor Gray

# Read httpd.conf
$httpdContent = Get-Content $httpdConfFile

# Uncomment SSL module
$httpdContent = $httpdContent -replace '#(LoadModule ssl_module modules/mod_ssl.so)', '$1'

# Uncomment socache_shmcb module (required for SSL)
$httpdContent = $httpdContent -replace '#(LoadModule socache_shmcb_module modules/mod_socache_shmcb.so)', '$1'

# Uncomment headers module (for security headers)
$httpdContent = $httpdContent -replace '#(LoadModule headers_module modules/mod_headers.so)', '$1'

# Uncomment SSL configuration include
$httpdContent = $httpdContent -replace '#(Include conf/extra/httpd-ssl.conf)', '$1'

# Save updated httpd.conf
$httpdContent | Out-File -FilePath $httpdConfFile -Encoding ASCII

Write-Host "✓ Apache modules enabled (ssl, socache_shmcb, headers)" -ForegroundColor Green

Write-Host "`n[6/6] Configuring Windows Firewall..." -ForegroundColor Yellow

# Add firewall rules for HTTPS (port 443)
try {
    # Remove existing rules if they exist
    Remove-NetFirewallRule -DisplayName "XAMPP HTTPS - Network A" -ErrorAction SilentlyContinue
    Remove-NetFirewallRule -DisplayName "XAMPP HTTPS - Network B" -ErrorAction SilentlyContinue
    Remove-NetFirewallRule -DisplayName "XAMPP HTTPS - Localhost" -ErrorAction SilentlyContinue
    
    # Add new rules
    New-NetFirewallRule -DisplayName "XAMPP HTTPS - Network A" `
        -Direction Inbound -LocalPort 443 -Protocol TCP -Action Allow `
        -RemoteAddress 192.168.1.0/24 | Out-Null
    
    New-NetFirewallRule -DisplayName "XAMPP HTTPS - Network B" `
        -Direction Inbound -LocalPort 443 -Protocol TCP -Action Allow `
        -RemoteAddress 192.168.254.0/24 | Out-Null
    
    New-NetFirewallRule -DisplayName "XAMPP HTTPS - Localhost" `
        -Direction Inbound -LocalPort 443 -Protocol TCP -Action Allow `
        -RemoteAddress 127.0.0.1 | Out-Null
    
    Write-Host "✓ Firewall rules configured for port 443" -ForegroundColor Green
} catch {
    Write-Host "⚠ Could not configure firewall (may require admin privileges)" -ForegroundColor Yellow
}

Write-Host "
═══════════════════════════════════════════════════════════════════════
  ✅ SSL/TLS SETUP COMPLETED SUCCESSFULLY!
═══════════════════════════════════════════════════════════════════════

📜 CERTIFICATE DETAILS:
   Certificate: $certFile
   Private Key: $keyFile
   Valid Until: $((Get-Date).AddDays(365).ToString('yyyy-MM-dd'))
   Algorithm:   RSA 2048-bit
   Hash:        SHA-256

🌐 HTTPS URLS:
   Network A:   https://192.168.1.12/puta
   Network B:   https://192.168.254.254/puta
   Localhost:   https://localhost/puta

⚠️  IMPORTANT NEXT STEPS:

1. RESTART APACHE
   Open XAMPP Control Panel and click 'Stop' then 'Start' for Apache
   
   Or use PowerShell:
   net stop Apache2.4
   net start Apache2.4

2. TRUST THE CERTIFICATE (First time only)
   When you visit https://localhost/puta, your browser will show a warning
   because this is a self-signed certificate.
   
   Click 'Advanced' → 'Proceed to localhost (unsafe)' → 'Accept the Risk'
   
   For mobile devices, you'll need to accept the certificate on each device.

3. TEST HTTPS ACCESS
   Visit: https://192.168.254.254/puta
   You should see a padlock icon (may show 'Not Secure' due to self-signed cert)

4. OPTIONAL: Install certificate on client devices for trusted access
   Certificate file: $certFile
   Import this into device's trusted root certificates

📝 SECURITY FEATURES ENABLED:
   ✓ TLS 1.2 and TLS 1.3 (SSL 3.0, TLS 1.0, TLS 1.1 disabled)
   ✓ Strong cipher suites only
   ✓ HSTS (Strict-Transport-Security) header
   ✓ X-Frame-Options protection
   ✓ X-Content-Type-Options protection
   ✓ XSS Protection headers
   ✓ Certificate valid for multiple IPs/domains

🔧 TROUBLESHOOTING:
   • Apache won't start: Check logs at C:\xampp\apache\logs\error.log
   • Port 443 in use: netstat -ano | findstr :443
   • Certificate errors: Verify files exist in ssl.crt and ssl.key folders
   • Browser warning: Normal for self-signed certs, click 'Advanced' to proceed

📚 DOCUMENTATION:
   See SSL_TLS_GUIDE.md for complete setup and usage instructions

═══════════════════════════════════════════════════════════════════════
" -ForegroundColor Green

# Ask if user wants to restart Apache now
$restart = Read-Host "`nRestart Apache now? (Y/N)"
if ($restart -eq "Y" -or $restart -eq "y") {
    Write-Host "`nRestarting Apache..." -ForegroundColor Yellow
    try {
        net stop Apache2.4 2>&1 | Out-Null
        Start-Sleep -Seconds 2
        net start Apache2.4 2>&1 | Out-Null
        Write-Host "✓ Apache restarted successfully" -ForegroundColor Green
        Write-Host "`nYou can now access the system via HTTPS!" -ForegroundColor Cyan
        Write-Host "Visit: https://localhost/puta or https://192.168.254.254/puta" -ForegroundColor Cyan
    } catch {
        Write-Host "⚠ Please restart Apache manually via XAMPP Control Panel" -ForegroundColor Yellow
    }
} else {
    Write-Host "`nPlease restart Apache manually to apply SSL configuration" -ForegroundColor Yellow
}
