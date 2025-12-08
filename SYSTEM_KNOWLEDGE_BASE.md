# QR-Based Attendance Monitoring System - Knowledge Base

## System Information

**Creator:** Jether Garque  
**Position:** Grade 12 ICT Programming Student  
**School:** Palawan National School  
**Purpose:** Created exclusively for Palawan National School  
**Technology Stack:** PHP, MySQL, HTML5, CSS3, JavaScript

---

## System Architecture

### Frontend Technologies
- **HTML5** - Semantic markup, forms, QR code display
- **CSS3** - Styling (`style.css`), responsive design
- **JavaScript** - QR scanning (`html5-qrcode.min.js`), AJAX, DOM manipulation
- **Responsive Design** - Mobile and desktop compatibility

### Backend Technologies
- **PHP** - Procedural programming, version 7.4+
- **MySQL** - Relational database via PDO
- **Session Management** - Role-based authentication
- **PDO** - Prepared statements for SQL injection prevention

---

## Database Schema

### 1. `teachers` Table
```sql
- id (INT, PRIMARY KEY)
- full_name (VARCHAR 100)
- email (VARCHAR 100, UNIQUE)
- password (VARCHAR 255, hashed)
- gender (ENUM: Male, Female, Other)
- grade_level (VARCHAR 10)
- strand (VARCHAR 50)
- section_block (VARCHAR 20)
- faculty (VARCHAR 50)
- created_at (TIMESTAMP)
```

### 2. `students` Table
```sql
- id (INT, PRIMARY KEY)
- student_id (VARCHAR 20, UNIQUE)
- full_name (VARCHAR 100)
- email (VARCHAR 100)
- password (VARCHAR 255, hashed)
- gender (ENUM: Male, Female, Other)
- grade_level (VARCHAR 10)
- strand (VARCHAR 50: STEM, ABM, HUMSS, GAS, TVL)
- section_block (VARCHAR 20)
- created_at (TIMESTAMP)
```

### 3. `attendance_records` Table
```sql
- id (INT, PRIMARY KEY)
- student_id (VARCHAR 20, FOREIGN KEY)
- attendance_date (DATE)
- attendance_time (DATETIME)
- status (ENUM: present, absent, late, excuse)
- time_period (VARCHAR 20)
- morning_in (DATETIME, nullable)
- morning_out (DATETIME, nullable)
- afternoon_in (DATETIME, nullable)
- afternoon_out (DATETIME, nullable)
```

### 4. `system_events` Table (Traffic Logs)
```sql
- id (INT, PRIMARY KEY)
- event_type (VARCHAR 50)
- user_role (VARCHAR 20)
- user_id (INT)
- email (VARCHAR 150)
- ip_address (VARCHAR 45)
- user_agent (VARCHAR 255)
- success (TINYINT 1)
- message (VARCHAR 255)
- request_id (VARCHAR 32)
- method (VARCHAR 10)
- path (VARCHAR 255)
- status_code (INT)
- latency_ms (INT)
- object_type (VARCHAR 50)
- object_id (VARCHAR 50)
- context_json (TEXT)
- created_at (TIMESTAMP)
```

### 5. `posts` Table (Community Wall)
```sql
- id (INT, PRIMARY KEY)
- user_id (INT)
- content (TEXT)
- image_path (VARCHAR 255)
- file_path (VARCHAR 255)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### 6. `post_likes` Table
```sql
- id (INT, PRIMARY KEY)
- post_id (INT)
- user_id (INT)
- created_at (TIMESTAMP)
- UNIQUE(post_id, user_id)
```

### 7. `post_comments` Table
```sql
- id (INT, PRIMARY KEY)
- post_id (INT)
- user_id (INT)
- comment (TEXT)
- created_at (TIMESTAMP)
```

### 8. `profile_edits` Table
```sql
- id (INT, PRIMARY KEY)
- user_id (INT, FOREIGN KEY)
- full_name (VARCHAR 100)
- email (VARCHAR 100)
- gender (ENUM: Male, Female, Other)
- profile_picture (VARCHAR 255)
- edited_at (TIMESTAMP)
```

---

## Core PHP Files

### Authentication
- `login.php` - Login page for students, teachers, developers
- `signup.php` - Registration page
- `process_signup.php` - Backend registration handler
- `logout.php` - Session termination
- `db.php` - Database connection handler
- `config.php` - Configuration constants

### Dashboards
- `student_dashboard.php` - Student interface
- `teacher_dashboard.php` - Teacher interface (attendance, SF2 reports)
- `developer_dashboard.php` - Developer portal (analytics, AI assistant, traffic logs)

### Attendance System
- `record_attendance.php` - QR/barcode scanning endpoint
- `scan.php` - Student QR scanning interface
- `qr.php` - QR code generator for students
- `student_qr.php` - Student QR code display
- `update_attendance_status.php` - Manual attendance updates

### Reporting
- `print_monthly_sf2.php` - SF2 form generation (DepEd format)

### Social Features
- `wall.php` - Community announcement board
- `chatroom.php` - Messaging system

### Profile Management
- `edit_profile.php` - Profile editor
- `student_info.php` - Student information display

### Developer Tools
- `developer_traffic.php` - System traffic monitor
- `developer_ai_assistant.php` - AI assistant backend (Ollama/Llama 3.2)
- `logging.php` - Event logging helper

### Database Management
- `init_db.php` - Database initialization
- `seed.php` - Sample data seeder
- `test_connection.php` - Database connection test

---

## Key Features

### 1. QR Code Attendance
- Students scan QR codes via mobile devices
- Barcode scanning support
- Four daily checkpoints: Morning In, Morning Out, Afternoon In, Afternoon Out
- Real-time status updates (Present, Absent, Late, Excuse)

### 2. SF2 Form Reporting
- Automated School Form 2 generation
- Monthly attendance reports
- Grade level, strand, section filtering
- Teacher-specific reports
- HTML and PDF export (planned)

### 3. Role-Based Access Control
- **Student Role:** View attendance, QR codes, profile
- **Teacher Role:** Manage attendance, generate reports, view students
- **Developer Role:** Full system access, analytics, AI assistant, traffic logs

### 4. Traffic Monitoring & Analytics
- Real-time event logging (logins, scans, reports, errors)
- IP address and user-agent tracking
- Request correlation via unique request IDs
- Performance metrics (latency, status codes)
- CSV export (up to 10,000 records)
- Device/platform analytics

### 5. AI Assistant (Jether)
- Powered by Ollama and Llama 3.2
- Natural language queries
- Database access for dynamic queries
- System architecture knowledge
- Security analysis
- Attendance pattern insights

### 6. Community Features
- Announcement wall with posts, images, files
- Like and comment system
- Real-time chat/messaging

### 7. Security Features
- Password hashing (bcrypt)
- SQL injection prevention (PDO prepared statements)
- Session-based authentication
- Failed login tracking
- IP address logging
- Audit trail (system_events)

---

## File Structure

```
puta/
├── index.php                       # Landing page
├── login.php                       # Login interface
├── signup.php                      # Registration interface
├── process_signup.php              # Registration backend
├── logout.php                      # Logout handler
├── config.php                      # Configuration
├── db.php                          # Database connection
├── logging.php                     # Event logging helper
│
├── student_dashboard.php           # Student portal
├── student_qr.php                  # Student QR display
├── student_info.php                # Student profile
│
├── teacher_dashboard.php           # Teacher portal
├── print_monthly_sf2.php           # SF2 report generator
│
├── developer_dashboard.php         # Developer portal
├── developer_traffic.php           # Traffic monitor
├── developer_ai_assistant.php      # AI backend
│
├── scan.php                        # QR scanning interface
├── qr.php                          # QR generator
├── record_attendance.php           # Attendance recording
├── update_attendance_status.php    # Manual attendance
│
├── wall.php                        # Community wall
├── chatroom.php                    # Chat interface
├── edit_profile.php                # Profile editor
│
├── init_db.php                     # DB initialization
├── seed.php                        # Data seeder
├── test_connection.php             # DB test
│
├── style.css                       # Global styles
├── html5-qrcode.min.js             # QR scanning library
│
├── complete_database_setup.sql     # Full DB schema
├── database/
│   └── migrations/
│       ├── 001_initial_schema.sql
│       ├── 002_default_data.sql
│       └── 003_system_events.sql
│
├── assets/                         # Uploaded files
├── uploads/                        # User uploads
└── data/                           # Data directory
```

---

## Department of Education (DepEd) Integration

### SF2 Form (School Form 2)
- Official attendance record format required by DepEd
- Monthly reporting by grade level and strand
- Teacher-specific advisory sections
- Tracks daily attendance for entire school year
- Columns: Student names, LRN, daily attendance marks

### Senior High School Strands
- **STEM** - Science, Technology, Engineering, Mathematics
- **ABM** - Accountancy, Business, Management
- **HUMSS** - Humanities and Social Sciences
- **GAS** - General Academic Strand
- **TVL** - Technical-Vocational-Livelihood

---

## AI Assistant Capabilities

### Jether AI Features
1. **Natural Language Understanding** - Powered by Llama 3.2
2. **Database Queries** - Direct SQL execution for data questions
3. **System Knowledge** - Full awareness of architecture, files, schema
4. **Security Analysis** - Failed login detection, threat identification
5. **Attendance Insights** - Pattern analysis, trends, recommendations
6. **Performance Monitoring** - Latency tracking, slow query detection
7. **Device Analytics** - Platform and device usage statistics

### Sample Queries
- "Who created this system?"
- "Explain the database schema"
- "How many male and female STEM students?"
- "Show me all teachers in HUMSS faculty"
- "Who was absent today?"
- "Are there any security threats?"
- "What's the system performance like?"
- "Show me attendance trends for Grade 12"

---

## Deployment Information

**Primary School:** Palawan National School  
**Environment:** XAMPP (localhost development)  
**Database:** MySQL via phpMyAdmin  
**Web Server:** Apache  
**PHP Version:** 7.4+  
**AI Model:** Ollama with Llama 3.2 (local, offline)

---

## Creator Information

**Name:** Jether Garque  
**Grade Level:** Grade 12  
**Track:** ICT Programming  
**Institution:** Palawan National School  
**GitHub:** Jether34  
**Repository:** Advance-QR-Code-Based-Attendance-Monitoring-Systm-

---

## System Purpose

This QR-Based Attendance Monitoring System was developed exclusively for **Palawan National School** to:
1. Modernize attendance tracking with QR technology
2. Reduce manual paperwork for teachers
3. Provide real-time attendance data
4. Generate DepEd-compliant SF2 reports automatically
5. Enable data-driven insights via AI analytics
6. Enhance security through comprehensive logging
7. Improve student engagement with digital tools

---

*This knowledge base is maintained by Jether Garque and integrated into the Jether AI Assistant for comprehensive system support.*
 