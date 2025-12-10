# Deployment & Post-Security-Hardening Guide

## Quick Start: Deploy the hardened application

### 1. Environment Setup

Copy `.env.example` to `.env` and update with your actual values:

```bash
cp .env.example .env
# Edit .env with your database, LLM, and server details
nano .env
```

**Critical fields:**
- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` — your MySQL credentials
- `OLLAMA_API_URL` — local LLM endpoint (or comment out if not using AI features)
- `APP_ENV=production` — set to `production` before deploying
- `APP_DEBUG=false` — disable debug output in production
- `SESSION_SECURE=1` — set to `1` if HTTPS is enabled

### 2. Add `.env` to `.gitignore`

Ensure `.env` is never committed to git (it contains secrets):

```bash
echo ".env" >> .gitignore
git add .gitignore
git commit -m "Add .env to .gitignore to prevent secret leaks"
```

### 3. Database Setup

Create the database and run migrations if needed:

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run initialization (if using provided setup script)
php admin_setup.php  # CLI-only; generates secure admin password

# Or import from SQL dump if available
# mysql -u root -p attendance_system < database_dump.sql
```

### 4. Webserver Configuration

#### For Apache:

1. Ensure `.htaccess` support is enabled:
   ```apache
   <Directory /path/to/app>
       AllowOverride All
       Require all granted
   </Directory>
   ```

2. The app includes `storage/.htaccess` to deny direct access to uploaded files. Verify it's in place.

#### For Nginx:

1. Add deny rule for storage directory (included in `storage/nginx_deny.conf`):
   ```nginx
   location ~ ^/storage/ {
       deny all;
   }
   ```

2. Set secure headers in your server config:
   ```nginx
   server {
       add_header X-Content-Type-Options "nosniff" always;
       add_header X-Frame-Options "SAMEORIGIN" always;
       add_header X-XSS-Protection "1; mode=block" always;
       add_header Referrer-Policy "strict-origin-when-cross-origin" always;
   }
   ```

### 5. PHP Configuration

Set secure session/security settings in `php.ini`:

```ini
; Session security
session.use_strict_mode = 1
session.cookie_httponly = 1
session.cookie_secure = 1          ; Only if using HTTPS
session.cookie_samesite = Strict

; Disable dangerous functions
disable_functions = shell_exec, exec, passthru, system, proc_open, eval

; File upload limits
upload_max_filesize = 20M
post_max_size = 20M

; Security
display_errors = Off               ; Never show errors in production
log_errors = On
error_log = /var/log/php-errors.log
```

### 6. Directory Permissions

Set correct ownership and permissions:

```bash
# Web-accessible directories
chmod 755 /path/to/app
chmod 755 /path/to/app/uploads
chmod 755 /path/to/app/storage

# Storage (non-web-accessible files)
chmod 750 /path/to/app/storage/data

# Logs
chmod 755 /path/to/app/logs
```

### 7. SSL/TLS Setup (Production)

1. Obtain a certificate (Let's Encrypt is free):
   ```bash
   certbot certonly --webroot -w /path/to/app -d yourdomain.com
   ```

2. Update `.env`:
   ```
   SERVER_PROTOCOL=https
   FORCE_HTTPS=1
   SESSION_SECURE=1
   ```

3. Configure Nginx/Apache to redirect HTTP → HTTPS.

### 8. Start Application

```bash
# Ensure PHP-FPM or Apache is running
sudo systemctl start php-fpm      # or: sudo service php8.1-fpm start
sudo systemctl start nginx         # or: apache2ctl restart

# For development/testing:
cd /path/to/app
php -S localhost:8000
```

### 9. Verify Security

Test key endpoints:

```bash
# Login page (should load without errors)
curl -i http://localhost:8000/login.php

# Check storage is not accessible
curl -i http://localhost:8000/storage/data/  # Should return 403 or 404

# Check CSRF token is present (check HTML source for csrf_token field)
curl http://localhost:8000/import_students.php | grep csrf_token
```

---

## Security Changes Applied (This Session)

### Session Management
- Centralized session startup via `bootstrap.php` with hardened cookie settings
- All application pages now use `require_once __DIR__ . '/bootstrap.php'` instead of bare `session_start()`
- Sessions now enforce: `httponly`, `samesite=Strict`, conditional `secure` based on HTTPS

### CSRF Protection
- Added CSRF tokens to high-risk forms (import, teacher/student dashboards, edits)
- All POST handlers verify tokens before processing state changes

### SQL Injection Prevention
- Replaced unsafe interpolated SQL with prepared statements in:
  - `chatroom.php` (user lookup)
  - `developer_students.php` & `developer_teachers.php` (dynamic column queries)
  - All high-risk user input paths

### File Upload & LLM Hardening
- PDF→CSV pipeline (`pdf_to_csv_converter.php`):
  - Sanitizes extracted text to prevent prompt-injection
  - Server-side CSV validation and sanitization via `tools/csv_validation_helper.php`
  - Deterministic LLM options (temperature=0, limited tokens)
  - Rate-limiting per session (5 uploads/hour)
  - Files saved to non-web-accessible `storage/data/` with randomized names
  - Strict file permissions (0600)

### Storage Protection
- `storage/.htaccess` (Apache) — deny HTTP access
- `storage/nginx_deny.conf` (Nginx) — deny HTTP access

### Admin Setup Hardening
- `admin_setup.php` now CLI-only (prevents web-based password exposure)
- Generates random password on CLI run (not printed to web)

### Config & Secrets
- DB credentials moved to environment variables (via `getenv()` in `api_config.php`)
- `.env.example` provided with template for all config options
- `.env` should never be committed (add to `.gitignore`)

---

## Testing Checklist (Pre-Production)

- [ ] Login flows (student, teacher, developer) work correctly
- [ ] CSRF tokens present on all forms and validated on POST
- [ ] PDF→CSV upload succeeds and creates CSV in `storage/data/`
- [ ] Generated CSV files are not directly accessible via HTTP
- [ ] Session cookies have `HttpOnly`, `SameSite` flags (check browser DevTools)
- [ ] Admin setup runs via CLI only and generates secure password
- [ ] Environment variables loaded from `.env`
- [ ] Database queries use prepared statements (no interpolation)
- [ ] Error logs don't leak sensitive paths/queries to client
- [ ] HTTPS redirects work (if configured)

---

## Post-Deployment: Maintenance

### Backup & Recovery
- Regularly backup the database:
  ```bash
  mysqldump -u root -p attendance_system > backup_$(date +%Y%m%d).sql
  ```

- Keep `.env` and SSL certificates in a secure location (backup separately)

### Log Monitoring
- Monitor PHP error logs for XSS/CSRF/SQL errors
- Review `system_events` table for suspicious login patterns
- Set up alerts for repeated failed logins or scan errors

### Updates & Patches
- Keep PHP and MySQL updated
- Monitor for Ollama/LLM security advisories if using AI features
- Review and apply security patches from this repo regularly

---

## Support & Troubleshooting

### Session issues
If sessions don't persist:
- Check `php.ini` `session.save_path` is writable
- Verify `bootstrap.php` is being included (check error logs)
- Ensure cookies are allowed in browser

### PDF upload fails
- Verify Ollama is running: `curl http://localhost:11434/api/tags`
- Check PDF is not scanned/image-based (text extraction requires readable PDF)
- Review error logs for timeout or LLM connection issues

### Database connection fails
- Verify MySQL is running: `mysql -u root -p`
- Check `.env` credentials match your setup
- Ensure database exists and is accessible by the PHP user

### Storage files not saving
- Check `storage/data/` directory exists and is writable
- Verify file permissions (should be 0600)
- Check disk space

---

## Questions or Issues?

Refer to `SECURITY_REPORT.md` for detailed changes, or contact your system administrator.
