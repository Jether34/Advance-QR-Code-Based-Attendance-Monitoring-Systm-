# Copilot Instructions - Palawan National School QR Attendance System

## Project Overview
This is a PHP-based QR code attendance monitoring system for Palawan National School. It features time-based attendance tracking with morning/afternoon sessions, role-based access (teachers/students), and a developer dashboard with AI analytics.

## Critical Architecture Patterns

### 1. Time Zone & Attendance Date Logic
**ALWAYS use Manila timezone (`Asia/Manila`) for all date/time operations.**

- Import `auto_reset_7pm.php` and call `get_attendance_date()` for attendance operations
- System auto-switches to next day after 7PM (19:00) to prepare for morning attendance
- Attendance periods: Morning In (6:00-11:35), Morning Out (11:36-12:45), Afternoon In (12:46-15:45), Afternoon Out (15:46-19:00)
- Status logic: Present if morning_in before 9:00 AM, Late if 9:01-11:35 AM
- See `ATTENDANCE_TIME_RULES.md` for complete time-based rules

### 2. File Loading Order (Critical!)
**Standard page initialization pattern:**
```php
<?php
require_once __DIR__ . '/bootstrap.php';  // FIRST: loads config.php + starts secure session
require_once __DIR__ . '/db.php';         // Database connection
require_once __DIR__ . '/logging.php';    // Event logging (optional)
require_once __DIR__ . '/auto_reset_7pm.php'; // For attendance pages
```

- `bootstrap.php` must come first (initializes session with secure cookie settings)
- Never call `session_start()` manually - `bootstrap.php` handles it
- Use `config.php` for SERVER_URL, APP_ENV, APP_DEBUG constants

### 3. Database Access Pattern
```php
$pdo = get_db();  // Returns configured PDO instance
// ALWAYS use prepared statements
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
```

- Database credentials loaded from `.env` file (never hardcode)
- MySQL timezone set to `+08:00` automatically in `get_db()`
- Schema: `students`, `teachers`, `attendance_records`, `system_events`, `posts`

### 4. Environment Configuration
- **NEVER commit** `.env` to git (use `.env.example` as template)
- Load with `config.php` or `db.php` which parse `.env` manually (no Composer dependency)
- Key variables: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_ENV`, `SERVER_URL`
- `APP_ENV=production` disables debug output and console.log

### 5. Security Patterns

**Page Security (prevent back button):**
```php
require_once __DIR__ . '/page_security.php';
$page_token = init_page_security();  // Sets no-cache headers + page token
validate_page_token();  // Validates fresh page load
```

**Output sanitization:**
```php
// ALWAYS escape user data for HTML output
echo htmlspecialchars($user_data, ENT_QUOTES, 'UTF-8');
```

**Security headers set in `bootstrap.php`:**
- CSP (Content-Security-Policy) restricts external resources
- X-Frame-Options prevents clickjacking
- X-Content-Type-Options prevents MIME sniffing

### 6. QR Code Formats (Multi-Format Support)
The scanner in `scan.php` handles three formats:

1. **Embedded text** (preferred):
   ```
   === STUDENT INFORMATION ===
   Student ID: 2024-12345
   Name: Juan Dela Cruz
   Grade: 12
   Strand: STEM
   ```

2. **URL-based**: `https://example.com/qr.php?s=2024-12345` or `student_info.php?id=123`

3. **Legacy barcode**: Direct student_id string

Detection logic in `scan.php` lines 768-800 (check `includes()` patterns)

### 7. Logging & Analytics
```php
log_event($pdo, 'attendance_scan', [
    'student_id' => $student_id,
    'status' => $status,
    'time_period' => $period,
    'success' => 1
]);
```

- All events stored in `system_events` table
- Developer dashboard (`developer_dashboard.php`) aggregates logs
- Include `request_id`, `latency_ms`, `ip_address` for request tracing

## Development Workflows

### Local Setup
1. Run `setup_project.ps1` (Windows) to create DB
2. Ensure XAMPP/MySQL running on localhost
3. Access via `http://localhost/puta/` (project subdirectory)

### Database Changes
1. Modify SQL in `complete_database_setup.sql` (source of truth)
2. For schema changes, also create migration file (e.g., `alter_attendance_records.sql`)
3. Test with `mysql -u root -p attendance_qr_system < your_migration.sql`

### Testing
- Manual QR testing: Use `scan.php` + `card.php?id=X` to generate/scan QR codes
- Security verification: Run `tools/final_security_audit.php` CLI
- No automated test suite - verify functionality in browser/mobile

### Deployment
- See `DEPLOYMENT_GUIDE.md` for production checklist
- Set `APP_ENV=production` in `.env`
- Enable HTTPS and set `SESSION_SECURE=1`
- Oracle Cloud deployment: Use `quick_oracle_deploy.sh`

## Project-Specific Conventions

### Naming Conventions
- **PHP files**: Snake_case (e.g., `student_dashboard.php`, `record_attendance.php`)
- **SQL tables**: Lowercase with underscores (`attendance_records`, `system_events`)
- **Database columns**: Snake_case (`student_id`, `created_at`)
- **Session vars**: Snake_case (`$_SESSION['user_id']`, `$_SESSION['role']`)

### Role-Based Pages
- `student_dashboard.php`, `teacher_dashboard.php` - user dashboards
- `developer_dashboard.php`, `developer_login.php` - admin analytics (separate auth)
- Check role: `if ($_SESSION['role'] === 'teacher')`

### File Organization
- `/includes/` - Shared utilities (e.g., `console_protection.php`)
- `/assets/` - Static files (CSS, JS, images)
- `/storage/` - User uploads (protected with `.htaccess`)
- `/tools/` - Development/audit scripts
- Root: Page files, configuration, SQL setup scripts

### API Endpoints (JSON responses)
```php
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true, 'data' => $result]);
```

- `record_attendance.php` - POST endpoint for QR scans
- `developer_ai_assistant.php` - POST endpoint for AI queries
- Always include `success` boolean and `error`/`data` fields

## Common Pitfalls & Solutions

1. **Date/Time Issues**: Always use `get_attendance_date()` from `auto_reset_7pm.php`, never `date('Y-m-d')` directly for attendance
2. **Session Problems**: Include `bootstrap.php` first, never start sessions manually
3. **Database Timezone**: PDO connection sets MySQL timezone - don't override
4. **QR Scanner**: Requires HTTPS or localhost for camera access (see `scan.php` line 265)
5. **Environment Variables**: Use `getenv('VAR_NAME')` with fallback: `getenv('DB_HOST') ?: 'localhost'`

## Key Files Reference

| Purpose | File | Notes |
|---------|------|-------|
| Database schema | `complete_database_setup.sql` | Full schema, run once |
| Main scanner | `scan.php` | QR/barcode scanning with html5-qrcode.min.js |
| Attendance logic | `record_attendance.php` | Time-based status calculation |
| Student card | `card.php` | Generates QR code for student |
| Config | `config.php`, `.env` | Environment-based configuration |
| Security | `bootstrap.php`, `page_security.php` | Session + headers |
| Time logic | `auto_reset_7pm.php` | 7PM rollover logic |
| Logging | `logging.php` | `log_event()` function |

## External Dependencies
- **html5-qrcode.min.js** - QR scanner library (local copy included)
- **JsBarcode** - Barcode generation (CDN in `card.php`)
- **Google Charts API** - QR image generation (requires internet)
- **Composer** (optional) - For OpenAI/LLM integration (see `AI_ASSISTANT_README.md`)

## Additional Context
- Created by Jether Garque (Grade 12 ICT student at Palawan National School)
- Designed for Philippine Senior High School (Grades 11-12, Strands: STEM, ABM, HUMSS, GAS, TVL)
- Extensive documentation: `SYSTEM_KNOWLEDGE_BASE.md`, `SECURITY_GUIDE.md`, `CONTRIBUTING.md`
- Mobile-optimized with responsive design (`style.css`)
