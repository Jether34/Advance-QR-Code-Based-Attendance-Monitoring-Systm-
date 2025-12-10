# XAMPP Local Setup Guide — Deploy to htdocs/puta/

This guide helps you deploy the TapIn system to your local XAMPP installation for development/testing.

## Quick Setup (Windows XAMPP)

### Step 1: Locate Your XAMPP htdocs

Typically at: `C:\xampp\htdocs\`

### Step 2: Copy Project to htdocs/puta/

**Option A: Using the setup script (Linux/Mac/WSL)**
```bash
./tools/deploy_to_htdocs.sh /path/to/xampp/htdocs
# Example:
./tools/deploy_to_htdocs.sh ~/xampp/htdocs
```

**Option B: Manual copy (Windows)**
1. Open File Explorer
2. Navigate to `C:\xampp\htdocs\`
3. Create a new folder named `puta`
4. Copy all files from this repository into `C:\xampp\htdocs\puta\`

**Option C: Manual copy (Linux/Mac)**
```bash
mkdir -p ~/xampp/htdocs/puta
cp -r . ~/xampp/htdocs/puta/
```

### Step 3: Create .env File

In `C:\xampp\htdocs\puta\` (or your htdocs/puta/ path), create a `.env` file:

```env
APP_ENV=development
APP_DEBUG=true
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=tapin_db
SERVER_IP=localhost
FORCE_HTTPS=false
SESSION_LIFETIME=3600
OLLAMA_API_URL=http://localhost:11434/api/generate
```

### Step 4: Create Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database named `tapin_db` with UTF-8 collation
3. Run the SQL import:
   - Go to "Import" tab
   - Select `create_tables.sql` or `complete_database_setup.sql` from the repo root
   - Click "Go"

### Step 5: Run Admin Setup (optional)

From command line in the `puta` folder:
```bash
php admin_setup.php
```

This creates a developer admin account with a random password. Save it securely.

### Step 6: Start XAMPP and Access the App

1. Start Apache and MySQL in XAMPP Control Panel
2. Open browser: http://localhost/puta
3. Login with:
   - Teacher/Student: use signup page or seed data
   - Developer: use password from admin_setup.php

## Troubleshooting

### Port 3306 (MySQL) already in use?
- Change port in XAMPP config or stop conflicting services

### Permission denied errors?
```bash
chmod -R 755 ~/xampp/htdocs/puta
chmod -R 775 ~/xampp/htdocs/puta/uploads
chmod -R 775 ~/xampp/htdocs/puta/storage
```

### Database import fails?
- Ensure `tapin_db` database exists
- Check file path is correct in phpMyAdmin import dialog
- Try importing smaller SQL files first

### Ollama AI features not working?
- Install Ollama: https://ollama.ai
- Run: `ollama pull llama3.2`
- Ensure Ollama is running on http://localhost:11434
- Or disable AI features in code for local testing

## File Structure After Setup

```
C:\xampp\htdocs\puta\
├── index.php
├── config.php
├── .env
├── bootstrap.php
├── db.php
├── security_utils.php
├── login.php
├── signup.php
├── student_dashboard.php
├── teacher_dashboard.php
├── developer_dashboard.php
├── uploads/
├── storage/
├── tools/
├── vendor/
└── ... (other files)
```

## Running Tests Locally

```bash
# PHP syntax check
php -l index.php

# Run security audit
php tools/final_security_audit.php

# Run CSV validation tests
php tools/test_csv_validation.php

# Install and run pre-commit hooks
./tools/install_hooks.sh
pre-commit run --all-files
```

## Next Steps

- Configure `.env` for your environment
- Create test accounts via signup page or seed script
- Test QR scanning on mobile
- Run pre-commit hooks before committing changes

For production deployment, see `ORACLE_CLOUD_DEPLOYMENT.md` or `DEPLOYMENT_GUIDE.md`.
