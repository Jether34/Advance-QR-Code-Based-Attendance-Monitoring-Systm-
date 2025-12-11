# 🎓 Palawan National School - QR Code Attendance Monitoring System

A modern, QR code-based attendance tracking system built with PHP, MySQL, and JavaScript specifically for Palawan National School. This system provides contactless attendance recording with embedded student information and real-time validation.

## 🚀 Features

- **QR Code Generation**: Embedded student information directly in QR codes for offline access
- **Automatic Attendance Recording**: Real-time scanning with instant database validation
- **Multi-Format QR Support**: Compatible with embedded text, URL-based, and legacy QR formats
- **Role-Based Access**: Separate dashboards for teachers and students
- **Mobile-Optimized**: Responsive design works on all devices
- **Network Flexibility**: IP-based URLs for cross-device compatibility
- **Intelligent Status Calculation**: Automatic determination of present/absent/late status

## 📋 System Requirements

- **Web Server**: XAMPP, WAMP, or LAMP stack
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **Browser**: Modern browser with camera access for QR scanning
- **Network**: Local network access for mobile devices

## 🛠️ Quick Setup for Teams

### 1. Clone the Repository
```bash
git clone https://github.com/Jether34/Palawan-National-School-QR-Code-Attendance-Monitoring-System.git
cd Palawan-National-School-QR-Code-Attendance-Monitoring-System
```

### 2. Environment Setup
```bash
# Copy environment template
copy .env.example .env

# Edit .env with your database credentials
notepad .env
```

### 3. Database Setup
```

### Alternative Manual Setup (MySQL Console):
```sql
mysql -u root -p
CREATE DATABASE attendance_qr_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE attendance_qr_system;
SOURCE complete_database_setup.sql;
EXIT;
```

## Quick Start:

1. Complete database setup (see above).
2. Visit http://localhost/puta/signup.php to create students and teachers.
3. Visit http://localhost/puta/login.php to login.
4. After signing up, open each user's card (`card.php?id=...`) to see their QR and barcode for printing.
6. To scan attendance, open http://localhost/puta/scan.php and allow camera access; the scanner will record attendance when it reads a QR code. For barcode input, focus the "Barcode input" field and use a hardware barcode scanner (it will type the code and submit).

## Database Schema

The system uses MySQL with the following tables:
- `teachers` — Teacher accounts with grade/strand assignments
- `students` — Student accounts with unique IDs
- `attendance_records` — Daily attendance with morning/afternoon tracking
- `profile_edits` — User profile modification history
- `posts` — Community wall posts
- `post_likes` — Post like system
- `post_comments` — Post commenting system

Files added:
- `db.php` — PDO wrapper for MySQL connection.
- `complete_database_setup.sql` — Complete database schema and sample data.
- `setup_database.bat` — Automated database setup script.
- `signup.php` — signup form with role selection and required fields.
- `process_signup.php` — form handler, inserts into DB and returns profile link.
- `card.php` — displays generated QR and barcode for a user.
- `scan.php` — camera QR scanner + barcode input; records attendance.
- `record_attendance.php` — endpoint that records attendance from scans.

Notes & limitations:
- QR images are generated via Google Chart API so they require internet access.
- Barcodes are rendered client-side using JsBarcode (CDN included in `card.php`).
- This is a minimal example: no authentication, basic validation only, and limited UX. Consider adding login, CSRF protection, server-side validation, and pagination for real deployments.

Next steps you may want me to implement:
- Teacher dashboard to view attendance by date and student.
- Export attendance reports (CSV / Excel).
- Login system and role-based access.
- Improve barcode image scanning using QuaggaJS for camera-based barcode scanning.

If you'd like, I can now initialize the DB and add a couple of test users.
