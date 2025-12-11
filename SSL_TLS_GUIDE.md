# SSL/TLS Security Guide

## 🔒 HTTPS Encryption for QR Attendance System

This guide provides complete instructions for enabling SSL/TLS encryption to secure your QR Attendance System with HTTPS.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Quick Setup](#quick-setup)
3. [Manual Setup](#manual-setup)
4. [Certificate Details](#certificate-details)
5. [Browser Trust Configuration](#browser-trust-configuration)
6. [Mobile Device Setup](#mobile-device-setup)
7. [Security Features](#security-features)
8. [Troubleshooting](#troubleshooting)
9. [Production Considerations](#production-considerations)

---

## 🎯 Overview

### What is SSL/TLS?

**SSL (Secure Sockets Layer)** and **TLS (Transport Layer Security)** are cryptographic protocols that provide secure communication over networks. TLS is the successor to SSL.

### Why Use HTTPS?

✅ **Data Encryption**: All data transmitted between server and clients is encrypted
✅ **Authentication**: Verifies the server identity
✅ **Data Integrity**: Prevents data tampering during transmission
✅ **Privacy Protection**: Protects sensitive student/teacher information
✅ **Compliance**: Meets Data Privacy Act requirements for educational data

### Certificate Type

This setup uses a **self-signed certificate**, which provides encryption but will show browser warnings. For production, consider a certificate from a trusted Certificate Authority (CA).

---

## ⚡ Quick Setup

### Automated Installation (Recommended)

Run the automated setup script as Administrator:

```powershell
# Navigate to project directory
cd C:\xampp\htdocs\puta

# Run SSL setup script
PowerShell -ExecutionPolicy Bypass -File setup_ssl.ps1
```

The script will:
1. ✓ Check prerequisites (XAMPP, OpenSSL)
2. ✓ Generate SSL certificate (2048-bit RSA, SHA-256)
3. ✓ Configure Apache for HTTPS
4. ✓ Enable required modules
5. ✓ Configure Windows Firewall
6. ✓ Offer to restart Apache

**Total setup time:** ~2 minutes

---

## 🔧 Manual Setup

If you prefer manual configuration or the automated script fails:

### Step 1: Generate SSL Certificate

Open PowerShell as Administrator:

```powershell
cd C:\xampp\apache

# Create directories
New-Item -ItemType Directory -Path "conf\ssl.crt" -Force
New-Item -ItemType Directory -Path "conf\ssl.key" -Force

# Generate certificate (valid 365 days)
.\bin\openssl.exe req -x509 -nodes -days 365 -newkey rsa:2048 `
  -keyout conf\ssl.key\server.key `
  -out conf\ssl.crt\server.crt `
  -subj "/C=PH/ST=Palawan/L=Puerto Princesa/O=Palawan National School/OU=ICT/CN=QR Attendance System"
```

**Answer the prompts:**
- Country: `PH`
- State: `Palawan`
- City: `Puerto Princesa`
- Organization: `Palawan National School`
- Organizational Unit: `ICT Department`
- Common Name: `QR Attendance System`
- Email: `ict@pns.edu.ph`

### Step 2: Configure Apache SSL

Edit `C:\xampp\apache\conf\extra\httpd-ssl.conf`:

```apache
Listen 443

<VirtualHost _default_:443>
    DocumentRoot "C:/xampp/htdocs"
    ServerName localhost:443
    ServerAlias 192.168.1.12:443
    ServerAlias 192.168.254.254:443

    SSLEngine on
    SSLCertificateFile "conf/ssl.crt/server.crt"
    SSLCertificateKeyFile "conf/ssl.key/server.key"

    # Security configuration
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:MEDIUM:!aNULL:!MD5

    # Security headers
    Header always set Strict-Transport-Security "max-age=31536000"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"

    <Directory "C:/xampp/htdocs">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Step 3: Enable Apache Modules

Edit `C:\xampp\apache\conf\httpd.conf`:

Uncomment these lines (remove the `#`):

```apache
LoadModule ssl_module modules/mod_ssl.so
LoadModule socache_shmcb_module modules/mod_socache_shmcb.so
LoadModule headers_module modules/mod_headers.so

Include conf/extra/httpd-ssl.conf
```

### Step 4: Configure Firewall

```powershell
# Allow HTTPS (port 443)
New-NetFirewallRule -DisplayName "XAMPP HTTPS" -Direction Inbound -LocalPort 443 -Protocol TCP -Action Allow
```

### Step 5: Restart Apache

```powershell
net stop Apache2.4
net start Apache2.4
```

Or use XAMPP Control Panel:
1. Click "Stop" for Apache
2. Wait 2 seconds
3. Click "Start" for Apache

---

## 📜 Certificate Details

### Generated Certificate Specifications

| Property | Value |
|----------|-------|
| **Type** | Self-Signed X.509 |
| **Algorithm** | RSA |
| **Key Size** | 2048 bits |
| **Hash Algorithm** | SHA-256 |
| **Validity** | 365 days (1 year) |
| **Subject** | CN=QR Attendance System, O=Palawan National School |
| **Supported IPs** | 127.0.0.1, 192.168.1.12, 192.168.254.254 |
| **Supported Domains** | localhost, qr-attendance.local |

### Certificate Files Location

```
C:\xampp\apache\conf\
├── ssl.crt\
│   └── server.crt    (Public certificate)
└── ssl.key\
    └── server.key    (Private key - KEEP SECURE!)
```

⚠️ **IMPORTANT**: Never share `server.key` - it's the private key!

### Certificate Renewal

Certificate expires after 365 days. To renew:

```powershell
# Re-run setup script
PowerShell -ExecutionPolicy Bypass -File setup_ssl.ps1

# Or manually regenerate
cd C:\xampp\apache
.\bin\openssl.exe req -x509 -nodes -days 365 -newkey rsa:2048 `
  -keyout conf\ssl.key\server.key `
  -out conf\ssl.crt\server.crt
```

---

## 🌐 Browser Trust Configuration

### Why Browser Shows Warning?

Self-signed certificates trigger security warnings because they're not verified by a trusted Certificate Authority (CA). The connection is still encrypted, but browsers can't verify the server's identity.

### Trust Certificate on Windows

#### Method 1: Browser Exception (Quick)

**Chrome/Edge:**
1. Visit `https://localhost/puta`
2. Click "Advanced"
3. Click "Proceed to localhost (unsafe)"
4. Connection is now encrypted

**Firefox:**
1. Visit `https://localhost/puta`
2. Click "Advanced"
3. Click "Accept the Risk and Continue"

#### Method 2: Install to Trusted Root (Permanent)

**Windows Certificate Store:**

```powershell
# Import certificate to Trusted Root
$cert = New-Object System.Security.Cryptography.X509Certificates.X509Certificate2
$cert.Import("C:\xampp\apache\conf\ssl.crt\server.crt")

$store = New-Object System.Security.Cryptography.X509Certificates.X509Store("Root","LocalMachine")
$store.Open("ReadWrite")
$store.Add($cert)
$store.Close()
```

Or using GUI:
1. Double-click `server.crt`
2. Click "Install Certificate"
3. Select "Local Machine"
4. Choose "Place all certificates in the following store"
5. Browse → "Trusted Root Certification Authorities"
6. Click "Next" → "Finish"

### Trust Certificate on macOS

```bash
# Copy certificate to Mac
scp user@192.168.1.12:C:/xampp/apache/conf/ssl.crt/server.crt ~/Downloads/

# Import to keychain
sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain ~/Downloads/server.crt
```

### Trust Certificate on Linux

```bash
# Copy certificate
scp user@192.168.1.12:C:/xampp/apache/conf/ssl.crt/server.crt /tmp/

# Install (Ubuntu/Debian)
sudo cp /tmp/server.crt /usr/local/share/ca-certificates/qr-attendance.crt
sudo update-ca-certificates

# Install (CentOS/RHEL)
sudo cp /tmp/server.crt /etc/pki/ca-trust/source/anchors/
sudo update-ca-trust
```

---

## 📱 Mobile Device Setup

### Android Devices

#### Method 1: Accept Warning (Quick)

1. Open browser (Chrome, Firefox)
2. Visit `https://192.168.254.254/puta`
3. Tap "Advanced"
4. Tap "Proceed to 192.168.254.254 (unsafe)"

#### Method 2: Install Certificate (Secure)

1. **Transfer certificate to phone:**
   - Email `server.crt` to yourself
   - Or use USB transfer
   - Or host on HTTP: `http://192.168.254.254/puta/server.crt`

2. **Install certificate:**
   - Settings → Security → Encryption & credentials
   - Install from storage → CA certificate
   - Browse to downloaded `server.crt`
   - Enter device PIN
   - Certificate name: "QR Attendance System"

3. **Verify:**
   - Visit `https://192.168.254.254/puta`
   - Should show padlock without warning

### iOS Devices (iPhone/iPad)

#### Method 1: Accept Warning

1. Open Safari
2. Visit `https://192.168.254.254/puta`
3. Tap "Show Details"
4. Tap "visit this website"
5. Tap "Visit Website"

#### Method 2: Install Profile

1. **Transfer certificate:**
   - Email `server.crt` as attachment
   - Or AirDrop from Mac

2. **Install profile:**
   - Tap certificate attachment
   - Tap "Install" (top right)
   - Enter passcode
   - Tap "Install" → "Done"

3. **Trust certificate:**
   - Settings → General → About
   - Certificate Trust Settings
   - Enable "QR Attendance System"
   - Tap "Continue"

4. **Verify:**
   - Open Safari
   - Visit `https://192.168.254.254/puta`
   - Should work without warning

---

## 🛡️ Security Features

### Enabled Security Configurations

#### TLS Protocol Configuration

```apache
SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
```

- ✅ **TLS 1.2** - Enabled (Secure)
- ✅ **TLS 1.3** - Enabled (Most Secure)
- ❌ **SSLv3** - Disabled (Vulnerable to POODLE)
- ❌ **TLS 1.0** - Disabled (Deprecated)
- ❌ **TLS 1.1** - Disabled (Deprecated)

#### Cipher Suites

```apache
SSLCipherSuite HIGH:MEDIUM:!aNULL:!MD5:!SEED:!IDEA
```

- ✅ Strong encryption algorithms only
- ❌ Anonymous (no authentication) - Disabled
- ❌ MD5 hashing - Disabled
- ❌ Weak ciphers - Disabled

#### HTTP Security Headers

```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

| Header | Purpose | Protection |
|--------|---------|-----------|
| **HSTS** | Force HTTPS for 1 year | Prevents downgrade attacks |
| **X-Frame-Options** | Prevent embedding in frames | Clickjacking protection |
| **X-Content-Type-Options** | Disable MIME sniffing | XSS prevention |
| **X-XSS-Protection** | Enable XSS filter | Cross-site scripting protection |
| **Referrer-Policy** | Control referrer information | Privacy protection |

### Encryption Strength

- **Key Exchange**: RSA 2048-bit or ECDHE (Elliptic Curve)
- **Symmetric Encryption**: AES-256 or AES-128
- **Hash Algorithm**: SHA-256 or SHA-384
- **Overall Security**: **A-** grade (with self-signed cert)

---

## 🔍 Troubleshooting

### Issue: "Apache won't start after SSL setup"

**Check error logs:**
```powershell
Get-Content C:\xampp\apache\logs\error.log -Tail 50
```

**Common causes:**
1. **Port 443 already in use**
   ```powershell
   netstat -ano | findstr :443
   tasklist /FI "PID eq [PID_NUMBER]"
   ```
   Solution: Stop the conflicting process

2. **Certificate files missing**
   ```powershell
   Test-Path C:\xampp\apache\conf\ssl.crt\server.crt
   Test-Path C:\xampp\apache\conf\ssl.key\server.key
   ```
   Solution: Re-run `setup_ssl.ps1`

3. **Module not loaded**
   Check `httpd.conf` for:
   ```apache
   LoadModule ssl_module modules/mod_ssl.so
   LoadModule socache_shmcb_module modules/mod_socache_shmcb.so
   ```

### Issue: "ERR_SSL_PROTOCOL_ERROR"

**Solution 1: Clear browser SSL cache**
```
Chrome: chrome://net-internals/#sockets → Flush socket pools
Edge: edge://net-internals/#sockets → Flush socket pools
```

**Solution 2: Regenerate certificate**
```powershell
cd C:\xampp\htdocs\puta
PowerShell -ExecutionPolicy Bypass -File setup_ssl.ps1
```

### Issue: "NET::ERR_CERT_AUTHORITY_INVALID"

This is **normal for self-signed certificates**. Options:

1. **Accept the warning** (connection still encrypted)
2. **Install certificate** to trusted root (see Browser Trust section)
3. **Get CA-signed certificate** for production

### Issue: "Mixed Content" warnings

When HTTPS page loads HTTP resources:

**Solution: Update `api_config.php`**
```php
// Force HTTPS
$protocol = 'https';
define('APP_URL', 'https://' . $host);
```

**Update hardcoded URLs in PHP/JS:**
```javascript
// Before
const apiUrl = 'http://192.168.254.254/puta/api';

// After
const apiUrl = 'https://192.168.254.254/puta/api';
// Or better: use relative URLs
const apiUrl = '/puta/api';
```

### Issue: Certificate expired

```powershell
# Check expiry date
openssl x509 -in C:\xampp\apache\conf\ssl.crt\server.crt -noout -enddate

# Regenerate if expired
cd C:\xampp\htdocs\puta
PowerShell -ExecutionPolicy Bypass -File setup_ssl.ps1
```

---

## 🚀 Production Considerations

### For School-Wide Deployment

#### Option 1: Self-Signed Certificate (Current)

**Pros:**
- ✅ Free
- ✅ Quick setup
- ✅ Full encryption
- ✅ Works offline

**Cons:**
- ⚠️ Browser warnings
- ⚠️ Manual trust required on each device
- ⚠️ Not suitable for public internet

**Best for:** Local school network deployment

#### Option 2: Private Certificate Authority (CA)

Create your own CA for the school:

```powershell
# Generate CA private key
openssl genrsa -aes256 -out ca-key.pem 4096

# Generate CA certificate
openssl req -new -x509 -days 3650 -key ca-key.pem -sha256 -out ca-cert.pem

# Sign server certificate with CA
openssl x509 -req -in server.csr -CA ca-cert.pem -CAkey ca-key.pem -CAcreateserial -out server.crt -days 365 -sha256
```

**Pros:**
- ✅ No browser warnings (after CA trust)
- ✅ Centralized certificate management
- ✅ Can issue multiple certificates

**Cons:**
- ⚠️ Requires CA installation on all devices
- ⚠️ More complex setup

**Best for:** Multiple schools in a district

#### Option 3: Let's Encrypt (Free Trusted CA)

**Requirements:**
- Public domain name (e.g., qr-attendance.pns.edu.ph)
- Internet-accessible server
- Port 80/443 accessible from internet

**Setup with Certbot:**
```powershell
# Install Certbot for Windows
choco install certbot

# Obtain certificate
certbot certonly --standalone -d qr-attendance.pns.edu.ph
```

**Pros:**
- ✅ Free trusted certificate
- ✅ No browser warnings
- ✅ Auto-renewal available
- ✅ Industry standard

**Cons:**
- ⚠️ Requires public internet access
- ⚠️ Needs domain name
- ⚠️ 90-day validity (auto-renew)

**Best for:** Public-facing deployment

#### Option 4: Commercial Certificate

Purchase from trusted CA (DigiCert, Sectigo, GoDaddy):

**Cost:** ₱3,000 - ₱15,000/year

**Pros:**
- ✅ Trusted by all browsers
- ✅ Extended validation options
- ✅ Warranty/insurance
- ✅ Technical support

**Best for:** Critical production systems

### Security Recommendations

#### For Local Network (Current Setup):

```apache
# In httpd-ssl.conf
<VirtualHost *:443>
    # Restrict to local networks only
    <RequireAll>
        Require ip 192.168.1.0/24
        Require ip 192.168.254.0/24
        Require ip 127.0.0.1
    </RequireAll>
</VirtualHost>
```

#### Force HTTPS Redirect:

Create `.htaccess` in `C:\xampp\htdocs\puta`:

```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### Enable Certificate Pinning (Advanced):

```apache
Header always set Public-Key-Pins "pin-sha256=\"[BASE64_PIN]\"; max-age=2592000"
```

#### Regular Security Audits:

```powershell
# Test SSL configuration
Invoke-WebRequest https://localhost/puta -SkipCertificateCheck

# Check certificate details
openssl s_client -connect localhost:443 -showcerts
```

---

## 📊 HTTPS URLs Reference

### Network A (192.168.1.x)

| Service | HTTP | HTTPS |
|---------|------|-------|
| **Main System** | http://192.168.1.12/puta | https://192.168.1.12/puta |
| **Teacher Login** | http://192.168.1.12/puta/login.php | https://192.168.1.12/puta/login.php |
| **Student Dashboard** | http://192.168.1.12/puta/student_dashboard.php | https://192.168.1.12/puta/student_dashboard.php |
| **AI Features** | http://192.168.1.12/puta/review_center.php | https://192.168.1.12/puta/review_center.php |

### Network B (192.168.254.x)

| Service | HTTP | HTTPS |
|---------|------|-------|
| **Main System** | http://192.168.254.254/puta | https://192.168.254.254/puta |
| **Teacher Login** | http://192.168.254.254/puta/login.php | https://192.168.254.254/puta/login.php |
| **Student Dashboard** | http://192.168.254.254/puta/student_dashboard.php | https://192.168.254.254/puta/student_dashboard.php |
| **AI Features** | http://192.168.254.254/puta/review_center.php | https://192.168.254.254/puta/review_center.php |

### Localhost (Development)

| Service | HTTP | HTTPS |
|---------|------|-------|
| **Main System** | http://localhost/puta | https://localhost/puta |

---

## ✅ Security Checklist

Before going to production:

- [ ] SSL certificate generated and valid
- [ ] Apache configured for HTTPS (port 443)
- [ ] Firewall rules allow port 443
- [ ] Certificate installed on all client devices (or warnings accepted)
- [ ] HTTPS tested from PC
- [ ] HTTPS tested from mobile devices (both networks)
- [ ] Mixed content warnings resolved
- [ ] Security headers enabled
- [ ] Weak protocols disabled (SSLv3, TLS 1.0, TLS 1.1)
- [ ] Certificate expiry monitoring set up
- [ ] Backup of certificate files created
- [ ] `.htaccess` redirect to HTTPS (optional)
- [ ] Documentation updated with HTTPS URLs

---

## 📝 Quick Commands Reference

```powershell
# Setup SSL (automated)
cd C:\xampp\htdocs\puta
PowerShell -ExecutionPolicy Bypass -File setup_ssl.ps1

# Restart Apache
net stop Apache2.4; net start Apache2.4

# Check certificate expiry
openssl x509 -in C:\xampp\apache\conf\ssl.crt\server.crt -noout -enddate

# Test HTTPS
Invoke-WebRequest https://localhost/puta -SkipCertificateCheck

# View certificate details
openssl x509 -in C:\xampp\apache\conf\ssl.crt\server.crt -text -noout

# Check Apache SSL configuration
httpd -t -D DUMP_VHOSTS

# Monitor SSL connections
Get-Content C:\xampp\apache\logs\ssl_request.log -Tail 20 -Wait

# Firewall rules
Get-NetFirewallRule -DisplayName "*HTTPS*"
```

---

**Last Updated:** December 9, 2025
**System Version:** 2025.12
**SSL/TLS Version:** TLS 1.2/1.3
**Certificate Validity:** 365 days
