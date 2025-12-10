# Pre-Deployment Security Hardening Checklist

**Repository:** Advance-QR-Code-Based-Attendance-Monitoring-Systm-  
**Branch:** update-2025-11-dev-qr  
**Date:** December 10, 2025

---

## 📋 Pre-Staging Verification (Dev/Local)

- [ ] **Session Bootstrap**
  - [ ] Verify `bootstrap.php` exists and includes in `login.php`
  - [ ] Test login → session cookie set with `HttpOnly`, `SameSite` flags
  - [ ] Verify all pages use `require_once __DIR__ . '/bootstrap.php'` instead of `session_start()`

- [ ] **CSRF Protection**
  - [ ] Check `import_students.php` has csrf_token input in form
  - [ ] Verify POST submission without valid token is rejected
  - [ ] Test `developer_students.php` CSRF token validation

- [ ] **SQL & Queries**
  - [ ] Run `grep "->query(" */*.php` — should show only read-only analytics
  - [ ] Verify `developer_teachers.php` uses `INFORMATION_SCHEMA` prepared query (not `SHOW COLUMNS`)
  - [ ] Verify `developer_dashboard.php` uses `IN()` clause (not `LIKE '%login%'`)

- [ ] **File Upload Security**
  - [ ] Test PDF→CSV upload with valid PDF
  - [ ] Verify generated CSV saved to `storage/data/` (not webroot)
  - [ ] Verify storage directory is not HTTP-accessible
  - [ ] Run `php tools/test_csv_validation.php` — all tests pass

- [ ] **Admin Setup**
  - [ ] Run `php admin_setup.php` from CLI → generates random password
  - [ ] Verify accessing `admin_setup.php` via browser shows error
  - [ ] Verify admin account created with hashed password in DB

- [ ] **Env & Secrets**
  - [ ] Create `.env` from `.env.example`
  - [ ] Set DB credentials in `.env` (not in code)
  - [ ] Verify `.env` in `.gitignore`
  - [ ] Verify `api_config.php` reads from `getenv()` with fallback

---

## 📋 Staging Environment Setup

### Database
- [ ] Create MySQL database: `attendance_system`
- [ ] User has proper grants
- [ ] Run schema setup (from existing dump or `create_tables.sql`)
- [ ] Run `php admin_setup.php` to create admin user (record password securely)

### Webserver (Apache)
- [ ] Enable `.htaccess` (AllowOverride All)
- [ ] Verify `storage/.htaccess` blocks public access
- [ ] Test: `curl http://staging/storage/data/ | grep -i denied` (should be denied)

### Webserver (Nginx)
- [ ] Add `storage/nginx_deny.conf` rules to server block
- [ ] Reload config: `sudo nginx -t && sudo systemctl reload nginx`
- [ ] Test: `curl http://staging/storage/data/ | grep -i 403` (should be 403)

### PHP & Environment
- [ ] Copy `.env` to server (keep secure, not in git)
- [ ] Set file permissions: `chmod 755 storage/data/`
- [ ] Ensure PHP `disable_functions` includes `shell_exec`
- [ ] Verify PHP error logs are not exposed to clients

### HTTPS (Optional but Recommended)
- [ ] Obtain SSL certificate (Let's Encrypt)
- [ ] Update `SERVER_PROTOCOL=https` in `.env`
- [ ] Set `FORCE_HTTPS=1` in `.env`
- [ ] Set `SESSION_SECURE=1` in `.env`
- [ ] Configure webserver to redirect HTTP → HTTPS

---

## 📋 Staging Smoke Tests

### Login & Session
- [ ] Student login → redirects to `student_dashboard.php`
- [ ] Teacher login → redirects to `teacher_dashboard.php`
- [ ] Developer login → redirects to `developer_dashboard.php`
- [ ] Open DevTools → verify session cookie has:
  - `HttpOnly` flag
  - `SameSite=Strict` flag
  - `Secure` flag (if HTTPS)

### CSRF Protection
- [ ] Go to `import_students.php`
- [ ] Open browser DevTools → Network tab
- [ ] Submit CSV without CSRF token (manually remove from form) → should reject
- [ ] Submit with valid token → should process

### File Upload
- [ ] Review Center → Upload PDF
- [ ] Verify CSV is created and named with random hash (e.g., `a1b2c3d4.csv`)
- [ ] Try to access directly: `curl http://staging/storage/data/a1b2c3d4.csv` → should 404/403
- [ ] Verify CSV header matches: `topic,content,difficulty,subject`
- [ ] Verify content is sanitized (no HTML tags)

### Database & Admin
- [ ] Query `admin_users` table → verify developer account exists
- [ ] Developer login with generated password → works
- [ ] Change password in developer dashboard → works

### Analytics & Queries
- [ ] Developer Dashboard → stats load (students, teachers, attendance counts)
- [ ] Developer Traffic → filter by event type → loads
- [ ] Developer Teachers → list loads, can edit teacher

### Security Headers (Optional)
- [ ] Check response headers: `curl -i http://staging/login.php | grep -i "x-content-type"`
- [ ] Should include XSS protection, content-type, etc.

---

## 📋 Pre-Production Checklist

- [ ] Staging tests all pass (above)
- [ ] `.env` file secured (restricted permissions, secure location)
- [ ] Database backup taken
- [ ] SSL/TLS certificate installed
- [ ] Webserver deny rules deployed
- [ ] PHP configuration hardened (`disable_functions`, error logs, etc.)
- [ ] Rate-limiting configured (optional)
- [ ] Logging & monitoring configured
- [ ] Team trained on new features (CSRF tokens, env vars, etc.)
- [ ] Deployment runbook reviewed with ops team

---

## 📋 Post-Production Verification

- [ ] All login paths work (students, teachers, developers)
- [ ] CSRF tokens present and validated
- [ ] Storage directory not accessible via HTTP
- [ ] Session cookies secure (HttpOnly, SameSite, Secure if HTTPS)
- [ ] Admin setup CLI-only (web access blocked)
- [ ] PDF uploads save to non-web storage
- [ ] Database credentials not logged or exposed
- [ ] Error pages don't leak system paths
- [ ] Logs monitored for suspicious activity

---

## 📋 Ongoing Maintenance

- [ ] Monitor error logs weekly for XSS/CSRF/injection attempts
- [ ] Review `system_events` table for failed logins or suspicious patterns
- [ ] Keep MySQL and PHP updated
- [ ] Audit file permissions monthly (especially `storage/data/`)
- [ ] Backup database regularly
- [ ] Review and update `.env` secrets quarterly

---

## 📚 Key Documentation

| Document | Purpose | Who Reads |
|----------|---------|-----------|
| `SECURITY_HARDENING_SUMMARY.md` | Overview of all changes | Everyone |
| `SECURITY_REPORT.md` | Detailed technical changes | Developers, Security |
| `DEPLOYMENT_GUIDE.md` | Step-by-step deployment | DevOps, Ops |
| `SECURITY_AUDIT_NOTES.md` (this file) | Verification checklist | QA, Testing |

---

## ✅ Sign-Off

**Developer:** [Security Hardening Complete]  
**QA Lead:** [ ] Tested and verified  
**DevOps Lead:** [ ] Infrastructure ready  
**Product Owner:** [ ] Approved for production  

---

**Questions?** Refer to the relevant documentation or contact the development team.
