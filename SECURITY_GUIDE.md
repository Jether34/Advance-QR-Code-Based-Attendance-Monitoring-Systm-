# Security Implementation Guide

## 🔒 Security Measures Against View Source (Ctrl+U)

This document outlines all security measures implemented to protect sensitive data when users view the page source.

---

## ✅ Implemented Security Measures

### 1. **Environment Variables (.env file)**

**What it does:**
- Moves sensitive configuration (database credentials, API keys) from PHP files to `.env` file
- `.env` file is NOT accessible via web browser (server-side only)
- Protected by `.gitignore` (never committed to version control)

**Files affected:**
- `db.php` - Database credentials now loaded from environment variables
- `config.php` - Server configuration loaded from environment variables
- `.env` - Contains all sensitive configuration

**Security benefit:**
✅ Database password not hardcoded in source files  
✅ If PHP fails, credentials are not exposed  
✅ Easy to change credentials without modifying code  

---

### 2. **Console.log Protection**

**What it does:**
- Automatically disables `console.log()` in production mode
- Removes debug information from browser console
- Prevents sensitive data leakage through console output

**Files affected:**
- `includes/console_protection.php` - Auto-disables console in production
- `security_utils.php` - Provides `minify_js_for_production()` function

**How to use:**
```php
<?php require_once 'config.php'; ?>
<?php include 'includes/console_protection.php'; ?>
```

**Security benefit:**
✅ No student data visible in browser console  
✅ No API endpoints logged to console  
✅ No debug information exposed  

---

### 3. **Security HTTP Headers**

**What it does:**
- Adds multiple security headers to every page response
- Protects against XSS, clickjacking, MIME sniffing attacks
- Implements Content Security Policy (CSP)

**Headers implemented:**
```php
Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline'...
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
```

**Security benefit:**
✅ Prevents loading of malicious external scripts  
✅ Prevents page embedding in iframes (clickjacking)  
✅ Blocks XSS attacks  
✅ Limits referrer information leakage  

---

### 4. **Output Sanitization**

**What it does:**
- Escapes HTML special characters in all user-generated content
- Prevents XSS attacks through input/output validation

**Helper function:**
```php
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
```

**How to use:**
```php
// Before (UNSAFE):
echo $user['name'];

// After (SAFE):
echo sanitize_output($user['name']);
```

**Security benefit:**
✅ Prevents script injection  
✅ Protects against XSS attacks  
✅ Safe display of user input  

---

### 5. **Production/Development Mode**

**What it does:**
- Separates production and development environments
- Hides error messages in production
- Disables debug features in production

**Configuration in `.env`:**
```env
APP_ENV=production        # production or development
APP_DEBUG=false          # true or false
```

**Security benefit:**
✅ No error messages exposing file paths  
✅ No debug data in production  
✅ Clean user experience  

---

### 6. **CSRF Protection** (Bonus)

**What it does:**
- Generates unique tokens for each session
- Validates tokens on form submission
- Prevents Cross-Site Request Forgery attacks

**Helper functions:**
```php
// Generate token
$token = generate_csrf_token();

// Verify token
if (!verify_csrf_token($_POST['csrf_token'])) {
    die('Invalid request');
}
```

**How to use:**
```html
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <!-- other fields -->
</form>
```

---

### 7. **Rate Limiting** (Bonus)

**What it does:**
- Limits number of requests per time window
- Prevents brute force attacks
- Protects against DDoS

**Usage:**
```php
if (!check_rate_limit('login_' . $_POST['email'], 5, 300)) {
    die('Too many attempts. Please try again in 5 minutes.');
}
```

---

## 🔍 What Users Can/Cannot See

### ✅ What is PROTECTED (Users CANNOT see):

| Item | Protection Method |
|------|------------------|
| Database password | Environment variables (.env file) |
| Database username | Environment variables (.env file) |
| Database host | Environment variables (.env file) |
| API keys | Environment variables (.env file) |
| Session data | Server-side PHP sessions |
| PHP server code | Server-side execution (not sent to browser) |
| Debug messages | Disabled in production mode |
| Console logs | Disabled in production mode |
| Error details | Hidden in production mode |

### ⚠️ What Users CAN See (But is Safe):

| Item | Reason | Security Risk |
|------|--------|--------------|
| HTML structure | Normal web behavior | Low - no sensitive data |
| CSS styles | Visual design only | None |
| JavaScript code | Client-side execution required | Low - no credentials |
| API endpoints | Needed for AJAX calls | Low - protected by authentication |
| Form fields | User interface | None |
| IP addresses | Already visible in URL | Low - local network only |

---

## 🛠️ How to Verify Security

### 1. Test View Source (Ctrl+U)

```
1. Open any page (e.g., https://192.168.1.12/puta/login.php)
2. Press Ctrl+U to view source
3. Search for:
   - "password" - Should NOT show database password
   - "root" - Should NOT show database username
   - "console.log" - Should be disabled in production
   - "DB_PASS" - Should NOT appear
```

### 2. Check Browser Console (F12)

```
1. Open any page
2. Press F12 to open Developer Tools
3. Go to Console tab
4. In production mode: All console.log should be disabled
5. In development mode: Console logs are visible (for debugging)
```

### 3. Check Security Headers

```
1. Open Developer Tools (F12)
2. Go to Network tab
3. Reload page
4. Click on the page request
5. Go to Headers
6. Verify security headers are present:
   ✓ X-Frame-Options: SAMEORIGIN
   ✓ X-Content-Type-Options: nosniff
   ✓ X-XSS-Protection: 1; mode=block
   ✓ Content-Security-Policy: ...
```

---

## 📋 Setup Checklist

Before going to production:

- [ ] Copy `.env.example` to `.env` and set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Verify `.env` is in `.gitignore`
- [ ] Test that `.env` is not accessible via browser
- [ ] Add `<?php require_once 'config.php'; ?>` to all PHP files
- [ ] Add `<?php include 'includes/console_protection.php'; ?>` after `<head>` tag
- [ ] Use `sanitize_output()` for all user-generated content
- [ ] Test view source - no sensitive data visible
- [ ] Test browser console - console.log disabled
- [ ] Verify security headers in network tab

---

## 🔐 Additional Security Recommendations

### 1. Database Security
```sql
-- Create dedicated database user (instead of root)
CREATE USER 'attendance_user'@'localhost' IDENTIFIED BY 'REPLACE_WITH_SECURE_PASSWORD_FROM_VAULT_OR_SCRIPT';
GRANT SELECT, INSERT, UPDATE, DELETE ON attendance_qr_system.* TO 'attendance_user'@'localhost';
FLUSH PRIVILEGES;
```

Then update `.env`:
```env
DB_USER=attendance_user
# Replace with a secure password retrieved from your secret store or the deployment script
DB_PASS=REPLACE_WITH_SECURE_PASSWORD
```

### 2. File Permissions
```bash
# Make .env readable only by web server
chmod 600 .env
chown www-data:www-data .env  # Linux/Mac
```

### 3. Regular Updates
- Update PHP to latest stable version
- Update all Composer dependencies monthly
- Monitor security advisories

### 4. Backup Strategy
- Daily automated database backups
- Store backups off-site
- Test restoration procedures quarterly

---

## 🚨 Emergency Response

If sensitive data is exposed:

1. **Immediately change all passwords:**
   ```bash
   # Update .env with new credentials
   nano .env
   ```

2. **Invalidate all sessions:**
   ```php
   session_destroy();
   ```

3. **Review access logs:**
   ```bash
   tail -f /var/log/apache2/access.log
   ```

4. **Notify affected users** (if personal data exposed)

---

## 📞 Support

For security concerns or questions:
- Developer: Jether (System Creator)
- Email: (your contact email)
- Documentation: See `SECURITY.md` and `SSL_TLS_GUIDE.md`

---

**Last Updated:** December 9, 2025  
**Version:** 1.0  
**Security Level:** Production-Ready ✅
