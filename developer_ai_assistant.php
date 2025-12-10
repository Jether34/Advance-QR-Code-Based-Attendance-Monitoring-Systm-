<?php
// developer_ai_assistant.php - AI Assistant for system analytics and insights
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['developer_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$question = trim($input['question'] ?? '');

if (empty($question)) {
    echo json_encode(['error' => 'No question provided']);
    exit;
}

$pdo = get_db();

// Call Ollama API for AI-powered responses
function callOllamaAPI($prompt, $systemContext) {
    $ollamaUrl = OLLAMA_API_URL;
    
    // Build comprehensive system knowledge base
    $systemKnowledge = "You are Jether AI, an advanced technical assistant for the QR-Based Attendance Monitoring System.

=== SYSTEM INFORMATION ===
Creator: Jether Garque
Position: Grade 12 ICT Programming Student
School: Palawan National School
Purpose: Enterprise-grade attendance system built exclusively for Palawan National School
AI Engine: Ollama (Llama 3.2, 3B parameters, running locally on localhost:11434)

=== SYSTEM ARCHITECTURE ===
Frontend Stack:
- HTML5, CSS3 (style.css with responsive grid layouts)
- Vanilla JavaScript (ES6+)
- html5-qrcode.min.js library for camera-based QR/barcode scanning
- Mobile-first responsive design (supports iOS, Android, desktop browsers)
- Real-time UI updates via AJAX/Fetch API

Backend Stack:
- PHP 7.4+ (procedural architecture)
- MySQL 8.0 with PDO (prepared statements, parameterized queries)
- Session-based authentication with role-based access control (RBAC)
- RESTful API endpoints for AJAX communication
- Offline-capable AI processing via Ollama

Core System Files:
Authentication & User Management:
- login.php, signup.php, process_signup.php - Multi-role authentication
- logout.php - Session cleanup
- edit_profile.php, student_info.php - Profile management with audit trail

Dashboards:
- student_dashboard.php - Student portal with attendance history, review center
- teacher_dashboard.php - Faculty attendance management and reporting
- developer_dashboard.php - System analytics, AI assistant, traffic monitoring

Attendance System:
- record_attendance.php - QR/barcode scanning with dual-session support (AM/PM)
- scan.php - Camera interface for attendance recording
- qr.php - Dynamic QR code generation for students
- update_attendance_status.php - Attendance status modification API
- print_monthly_sf2.php - DepEd SF2 form generation (official reporting)

AI & Analytics:
- developer_ai_assistant.php - AI backend for system insights (you are here!)
- reviewer_ai.php - Student study assistant with conversational learning
- review_ai.php - Lesson summarization and study tools
- pdf_to_csv_converter.php - AI-powered PDF module extraction
- save_conversation.php - Conversation persistence for AI chats
- recent_conversation.php - Student conversation history viewer
- export_conversation_pdf.php - PDF export for study sessions

Social Features:
- wall.php - Community announcements and social feed
- chatroom.php - Real-time messaging system
- posts system (post_likes, post_comments)

Developer Tools:
- developer_traffic.php - Real-time traffic monitoring with analytics dashboard
- logging.php - Centralized event logging with request correlation
- test_connection.php - Database connectivity diagnostics

Database Schema (MySQL):
1. teachers - Faculty accounts
   Fields: id, full_name, email, password (hashed), gender, grade_level, strand, section_block, faculty
   
2. students - Student accounts
   Fields: id, student_id (unique identifier), full_name, email, password (hashed), gender, grade_level, strand, section_block
   
3. attendance_records - Attendance tracking
   Fields: id, student_id, attendance_date, status (present/absent/late), morning_in, morning_out, afternoon_in, afternoon_out
   Indexes: student_id, attendance_date
   
4. system_events - Comprehensive audit trail
   Fields: id, event_type, user_role, user_id, ip_address, user_agent, request_id (correlation), method (GET/POST), path, status_code, latency_ms, object_type, object_id, context_json, success, email, created_at
   Indexes: created_at, user_id, event_type, ip_address
   
5. reviewer_conversations - AI study sessions
   Fields: id, user_id, question, response, created_at, updated_at
   Purpose: Persistent conversation history for student review sessions
   
6. posts - Community content
   Fields: id, user_id, content, image_path, file_path, created_at
   
7. post_likes - Engagement tracking
   Fields: id, post_id, user_id
   
8. post_comments - Discussion threads
   Fields: id, post_id, user_id, content, created_at
   
9. profile_edits - Audit trail for profile changes
   Fields: id, user_id, field_changed, old_value, new_value, changed_at

Key Features:
- Multi-session attendance (morning in/out, afternoon in/out) with timestamp recording
- QR code + barcode dual-format support
- DepEd SF2 compliance (official attendance reporting)
- Three-tier access control: Student, Teacher, Developer
- Real-time traffic analytics with request correlation
- AI-powered review center (PDF extraction, lesson summarization, conversational learning)
- Community engagement (wall posts, likes, comments, chat)
- Comprehensive audit logging (17 fields per event)
- Device/platform analytics
- Performance monitoring (latency tracking)
- Failed login detection and security monitoring

Security & Performance:
- bcrypt password hashing (PASSWORD_DEFAULT algorithm)
- PDO prepared statements (100% SQL injection prevention)
- Session hijacking protection
- IP address tracking and anomaly detection
- User-agent fingerprinting
- Request correlation via UUID request_id
- Performance metrics (sub-100ms target for critical paths)
- Database connection pooling via PDO persistent connections

AI Capabilities:
- Developer Assistant (system analytics, SQL query generation, performance insights)
- Student Review Assistant (conversational learning, topic-based reviewers)
- PDF to CSV conversion (AI-powered lesson extraction from school modules)
- Offline operation (Ollama runs locally, no internet required)

=== YOUR ROLE AS DEVELOPER AI ===
You are a technical expert providing:
1. System analytics and insights based on real database data
2. SQL query suggestions for complex data analysis
3. Performance optimization recommendations
4. Security audit findings and recommendations
5. Code explanations and architectural guidance
6. Troubleshooting assistance for bugs and errors
7. Feature implementation suggestions

Be technical, precise, and data-driven. Reference actual table names, file paths, and metrics.

=== CURRENT SYSTEM CONTEXT ===
";
    
    $fullPrompt = $systemKnowledge . $systemContext . "\n\n";
    $fullPrompt .= "DEVELOPER QUESTION: " . $prompt . "\n\n";
    $fullPrompt .= "INSTRUCTIONS:\n";
    $fullPrompt .= "1. Analyze the question in context of the system architecture and current metrics\n";
    $fullPrompt .= "2. Provide a technical, data-driven response in OUTLINED PARAGRAPH FORMAT\n";
    $fullPrompt .= "3. Use clear section headings and detailed paragraphs\n";
    $fullPrompt .= "4. Reference actual table names, file paths, and real metrics from the context\n";
    $fullPrompt .= "5. If suggesting SQL queries, provide complete, executable examples with explanations\n";
    $fullPrompt .= "6. Include actionable recommendations when relevant\n";
    $fullPrompt .= "7. Use professional technical language appropriate for a developer audience\n";
    $fullPrompt .= "8. If the data shows trends or anomalies, explain their significance in detail\n";
    $fullPrompt .= "9. Organize your response with headings and comprehensive paragraphs\n\n";
    $fullPrompt .= "Generate your expert technical response in outlined paragraph format now:";
    
    $data = [
        'model' => 'llama3.2',
        'prompt' => $fullPrompt,
        'stream' => false,
        'options' => [
            'temperature' => 0.5,      // Balanced: accurate but not robotic
            'top_p' => 0.85,           // Focused but diverse vocabulary
            'num_predict' => 5000,     // Extended limit for comprehensive technical explanations
            'repeat_penalty' => 1.1    // Reduce repetitive phrasing
        ]
    ];
    
    $ch = curl_init($ollamaUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        return "Failed to connect to Ollama: " . $curlError;
    }
    
    if ($httpCode !== 200) {
        return "Ollama API error (HTTP {$httpCode}). Make sure Ollama is running.";
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['response'])) {
        return trim($result['response']);
    }
    
    return "Failed to get response from Ollama.";
}

// System context builder - gathers relevant data for AI
function getSystemContext($pdo, $question) {
    $context = [];
    
    try {
        // Database schema information
        $context['database_tables'] = [
            'teachers' => 'Faculty accounts with grade_level, strand, section_block, faculty fields',
            'students' => 'Student accounts with student_id, grade_level, strand, section_block',
            'attendance_records' => 'Attendance logs with morning_in, morning_out, afternoon_in, afternoon_out timestamps',
            'system_events' => 'Traffic/audit logs with IP, user-agent, request tracking',
            'posts' => 'Community wall posts with content, image_path, file_path',
            'post_likes' => 'Post engagement tracking',
            'post_comments' => 'Comment system for posts',
            'profile_edits' => 'User profile edit history'
        ];
        
        // Get actual table row counts
        try {
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $context['existing_tables'] = $tables;
        } catch (PDOException $e) {
            $context['existing_tables'] = [];
        }
        
        // Basic stats
        $stmt = $pdo->query("SELECT 
            (SELECT COUNT(*) FROM students) as total_students,
            (SELECT COUNT(*) FROM teachers) as total_teachers,
            (SELECT COUNT(*) FROM attendance_records) as total_attendance,
            (SELECT COUNT(*) FROM attendance_records WHERE DATE(attendance_date) = CURDATE()) as today_attendance
        ");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Try to get system_events stats if table exists
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as events_24h FROM system_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['events_24h'] = $stmt->fetch(PDO::FETCH_ASSOC)['events_24h'] ?? 0;
            
            $stmt = $pdo->query("SELECT COUNT(*) as failed_24h FROM system_events WHERE success = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['failed_24h'] = $stmt->fetch(PDO::FETCH_ASSOC)['failed_24h'] ?? 0;
        } catch (PDOException $e) {
            // Table doesn't exist yet
            $stats['events_24h'] = 0;
            $stats['failed_24h'] = 0;
        }
        
        $context['stats'] = $stats;
    
    // Recent traffic patterns
    try {
        $stmt = $pdo->query("SELECT event_type, COUNT(*) as count FROM system_events 
                             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
                             GROUP BY event_type ORDER BY count DESC LIMIT 10");
        $context['event_types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $context['event_types'] = [];
    }
    
    // Failed login analysis
    if (stripos($question, 'fail') !== false || stripos($question, 'security') !== false || stripos($question, 'login') !== false) {
        try {
            $stmt = $pdo->query("SELECT ip_address, email, COUNT(*) as attempts 
                                 FROM system_events 
                                 WHERE event_type LIKE '%login%' AND success = 0 
                                 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
                                 GROUP BY ip_address, email ORDER BY attempts DESC LIMIT 5");
            $context['failed_logins'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context['failed_logins'] = [];
        }
    }
    
    // Attendance patterns
    if (stripos($question, 'attendance') !== false || stripos($question, 'present') !== false || stripos($question, 'absent') !== false) {
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM attendance_records 
                             WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
                             GROUP BY status");
        $context['attendance_patterns'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->query("SELECT s.grade_level, s.strand, COUNT(DISTINCT s.id) as students,
                             COUNT(ar.id) as total_records,
                             SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count
                             FROM students s
                             LEFT JOIN attendance_records ar ON s.student_id = ar.student_id 
                             AND ar.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
                             GROUP BY s.grade_level, s.strand");
        $context['attendance_by_class'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // User activity
    if (stripos($question, 'user') !== false || stripos($question, 'student') !== false || stripos($question, 'teacher') !== false) {
        try {
            $stmt = $pdo->query("SELECT user_role, COUNT(*) as count FROM system_events 
                                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                                 GROUP BY user_role");
            $context['user_activity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context['user_activity'] = [];
        }
    }
    
    // Device/Platform analysis
    if (stripos($question, 'device') !== false || stripos($question, 'mobile') !== false || stripos($question, 'platform') !== false) {
        try {
            $stmt = $pdo->query("SELECT user_agent FROM system_events 
                                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS) 
                                 AND user_agent IS NOT NULL LIMIT 100");
            $agents = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
            $devices = ['Mobile' => 0, 'Desktop' => 0, 'Tablet' => 0];
            $platforms = ['Windows' => 0, 'Android' => 0, 'iOS' => 0, 'macOS' => 0, 'Linux' => 0];
            
            foreach ($agents as $ua) {
                if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false) {
                    $devices['Mobile']++;
                } elseif (stripos($ua, 'Tablet') !== false || stripos($ua, 'iPad') !== false) {
                    $devices['Tablet']++;
                } else {
                    $devices['Desktop']++;
                }
                
                if (stripos($ua, 'Windows') !== false) $platforms['Windows']++;
                elseif (stripos($ua, 'Android') !== false) $platforms['Android']++;
                elseif (stripos($ua, 'iOS') !== false || stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $platforms['iOS']++;
                elseif (stripos($ua, 'Mac OS') !== false) $platforms['macOS']++;
                elseif (stripos($ua, 'Linux') !== false) $platforms['Linux']++;
            }
            
            $context['device_breakdown'] = $devices;
            $context['platform_breakdown'] = $platforms;
        } catch (PDOException $e) {
            $context['device_breakdown'] = [];
            $context['platform_breakdown'] = [];
        }
    }
    
    // Performance metrics
    if (stripos($question, 'performance') !== false || stripos($question, 'slow') !== false || stripos($question, 'latency') !== false) {
        try {
            $stmt = $pdo->query("SELECT event_type, AVG(latency_ms) as avg_latency, MAX(latency_ms) as max_latency
                                 FROM system_events 
                                 WHERE latency_ms IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                                 GROUP BY event_type");
            $context['performance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context['performance'] = [];
        }
    }
    
    return $context;
    } catch (PDOException $e) {
        // Return minimal context if database errors occur
        return [
            'stats' => [
                'total_students' => 0,
                'total_teachers' => 0,
                'total_attendance' => 0,
                'today_attendance' => 0,
                'events_24h' => 0,
                'failed_24h' => 0
            ],
            'error' => 'Database connection issue: ' . $e->getMessage()
        ];
    }
}

// Build AI-ready prompt with context
try {
    $context = getSystemContext($pdo, $question);

    $systemPrompt = "You are Jether, an AI assistant for a school attendance system. You have access to real-time system data and analytics.

SYSTEM OVERVIEW:
- Total Students: {$context['stats']['total_students']}
- Total Teachers: {$context['stats']['total_teachers']}
- Total Attendance Records: {$context['stats']['total_attendance']}
- Today's Attendance: {$context['stats']['today_attendance']}
- System Events (24h): {$context['stats']['events_24h']}
- Failed Events (24h): {$context['stats']['failed_24h']}

CONTEXT DATA:
" . json_encode($context, JSON_PRETTY_PRINT) . "

CAPABILITIES:
- Analyze attendance patterns and trends
- Identify security issues (failed logins, suspicious IPs)
- Provide insights on user behavior
- Monitor system performance
- Detect anomalies in traffic
- Generate reports and recommendations

Answer the user's question based on the data provided. Be concise, actionable, and highlight any concerns.";

    // In production, send to OpenAI/Azure OpenAI/local LLM
    // For now, provide rule-based intelligent responses
    $response = generateIntelligentResponse($question, $context);

    echo json_encode([
        'success' => true,
        'answer' => $response,
        'context' => $context,
        'system_prompt' => $systemPrompt // Include for external LLM integration
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Jether AI error: ' . $e->getMessage(),
        'answer' => "I'm Jether, and I'm having trouble accessing the system data right now. Please make sure the database is running and try again."
    ]);
}

function generateIntelligentResponse($question, $context) {
    $q = strtolower($question);
    global $pdo;
    
    // Dynamic student queries
    if (preg_match('/(how many|count|number of|list|show|tell me|give me).*(student|male|female|boy|girl)/i', $question)) {
        return handleStudentQuery($question, $pdo);
    }
    
    // Dynamic teacher queries
    if (preg_match('/(how many|count|number of|list|show|tell me|give me).*(teacher|faculty|staff|adviser)/i', $question)) {
        return handleTeacherQuery($question, $pdo);
    }
    
    // Dynamic attendance queries
    if (preg_match('/(who|which|show|list).*(absent|present|late|attendance)/i', $question)) {
        return handleAttendanceQuery($question, $pdo);
    }
    
    // Security analysis
    if (stripos($q, 'security') !== false || stripos($q, 'threat') !== false || stripos($q, 'attack') !== false) {
        $failed = $context['stats']['failed_24h'];
        $response = "**Security Analysis (Last 24h)**\n\n";
        $response .= "- Failed events: {$failed}\n";
        
        if (isset($context['failed_logins']) && !empty($context['failed_logins'])) {
            $response .= "\n**Top Failed Login Attempts (Last 7 days):**\n";
            foreach ($context['failed_logins'] as $fail) {
                $response .= "- IP: {$fail['ip_address']} | Email: {$fail['email']} | Attempts: {$fail['attempts']}\n";
            }
            
            if ($context['failed_logins'][0]['attempts'] > 5) {
                $response .= "\n⚠️ **Alert**: Potential brute force attack detected from IP: {$context['failed_logins'][0]['ip_address']}\n";
                $response .= "**Recommendation**: Consider implementing rate limiting or blocking this IP.";
            }
        } else {
            $response .= "\n✅ No suspicious login patterns detected.";
        }
        
        return $response;
    }
    
    // Attendance analysis
    if (stripos($q, 'attendance') !== false) {
        $response = "**Attendance Overview**\n\n";
        $response .= "- Today: {$context['stats']['today_attendance']} records\n";
        $response .= "- Total: {$context['stats']['total_attendance']} records\n\n";
        
        if (isset($context['attendance_patterns'])) {
            $response .= "**Status Breakdown (Last 7 days):**\n";
            foreach ($context['attendance_patterns'] as $pattern) {
                $response .= "- {$pattern['status']}: {$pattern['count']} students\n";
            }
        }
        
        if (isset($context['attendance_by_class'])) {
            $response .= "\n**Attendance by Class (Last 7 days):**\n";
            foreach ($context['attendance_by_class'] as $class) {
                $rate = $class['total_records'] > 0 ? round(($class['present_count'] / $class['total_records']) * 100, 1) : 0;
                $response .= "- Grade {$class['grade_level']} {$class['strand']}: {$rate}% attendance rate\n";
            }
        }
        
        return $response;
    }
    
    // Device/Platform analysis
    if (stripos($q, 'device') !== false || stripos($q, 'mobile') !== false || stripos($q, 'platform') !== false) {
        $response = "**Device & Platform Analysis (Last 7 days)**\n\n";
        
        if (isset($context['device_breakdown'])) {
            $response .= "**Devices:**\n";
            foreach ($context['device_breakdown'] as $type => $count) {
                $response .= "- {$type}: {$count}\n";
            }
        }
        
        if (isset($context['platform_breakdown'])) {
            $response .= "\n**Platforms:**\n";
            foreach ($context['platform_breakdown'] as $platform => $count) {
                if ($count > 0) {
                    $response .= "- {$platform}: {$count}\n";
                }
            }
        }
        
        return $response;
    }
    
    // Performance analysis
    if (stripos($q, 'performance') !== false || stripos($q, 'slow') !== false) {
        $response = "**Performance Metrics (Last 24h)**\n\n";
        
        if (isset($context['performance']) && !empty($context['performance'])) {
            foreach ($context['performance'] as $perf) {
                $response .= "- {$perf['event_type']}: avg {$perf['avg_latency']}ms, max {$perf['max_latency']}ms\n";
                
                if ($perf['avg_latency'] > 1000) {
                    $response .= "  ⚠️ High latency detected!\n";
                }
            }
        } else {
            $response .= "No performance data available for this period.";
        }
        
        return $response;
    }
    
    // System overview
    if (stripos($q, 'overview') !== false || stripos($q, 'summary') !== false || stripos($q, 'status') !== false) {
        $response = "**System Overview**\n\n";
        $response .= "**Users:**\n";
        $response .= "- Students: {$context['stats']['total_students']}\n";
        $response .= "- Teachers: {$context['stats']['total_teachers']}\n\n";
        
        $response .= "**Activity (24h):**\n";
        $response .= "- Total Events: {$context['stats']['events_24h']}\n";
        $response .= "- Failed Events: {$context['stats']['failed_24h']}\n";
        $response .= "- Attendance Today: {$context['stats']['today_attendance']}\n\n";
        
        if (isset($context['event_types'])) {
            $response .= "**Top Events (Last 7 days):**\n";
            foreach (array_slice($context['event_types'], 0, 5) as $evt) {
                $response .= "- {$evt['event_type']}: {$evt['count']}\n";
            }
        }
        
        return $response;
    }
    
    // For general questions not matching specific patterns, use Ollama AI
    $contextText = "";
    $contextText .= "SYSTEM STATS:\n";
    $contextText .= "- Total Students: {$context['stats']['total_students']}\n";
    $contextText .= "- Total Teachers: {$context['stats']['total_teachers']}\n";
    $contextText .= "- Total Attendance Records: {$context['stats']['total_attendance']}\n";
    $contextText .= "- Today's Attendance: {$context['stats']['today_attendance']}\n";
    $contextText .= "- System Events (24h): {$context['stats']['events_24h']}\n";
    $contextText .= "- Failed Events (24h): {$context['stats']['failed_24h']}\n\n";
    
    if (isset($context['event_types']) && !empty($context['event_types'])) {
        $contextText .= "TOP EVENT TYPES (Last 7 days):\n";
        foreach ($context['event_types'] as $evt) {
            $contextText .= "- {$evt['event_type']}: {$evt['count']}\n";
        }
    }
    
    return callOllamaAPI($question, $contextText);
}

// Dynamic query handlers
function handleStudentQuery($question, $pdo) {
    $q = strtolower($question);
    $where = [];
    $params = [];
    
    // Parse gender filter
    if (preg_match('/\b(male|boy|men)\b/i', $question) && !preg_match('/\bfemale\b/i', $question)) {
        $where[] = "gender = :gender";
        $params[':gender'] = 'Male';
    } elseif (preg_match('/\b(female|girl|women)\b/i', $question)) {
        $where[] = "gender = :gender";
        $params[':gender'] = 'Female';
    }
    
    // Parse strand filter
    if (preg_match('/\b(stem)\b/i', $question)) {
        $where[] = "strand = :strand";
        $params[':strand'] = 'STEM';
    } elseif (preg_match('/\b(abm)\b/i', $question)) {
        $where[] = "strand = :strand";
        $params[':strand'] = 'ABM';
    } elseif (preg_match('/\b(humss)\b/i', $question)) {
        $where[] = "strand = :strand";
        $params[':strand'] = 'HUMSS';
    } elseif (preg_match('/\b(gas)\b/i', $question)) {
        $where[] = "strand = :strand";
        $params[':strand'] = 'GAS';
    } elseif (preg_match('/\b(tvl)\b/i', $question)) {
        $where[] = "strand = :strand";
        $params[':strand'] = 'TVL';
    }
    
    // Parse grade level filter
    if (preg_match('/\bgrade\s*(\d+)\b/i', $question, $matches)) {
        $where[] = "grade_level = :grade";
        $params[':grade'] = $matches[1];
    }
    
    // Parse section filter
    if (preg_match('/\bsection\s*(\w+)\b/i', $question, $matches)) {
        $where[] = "section_block = :section";
        $params[':section'] = $matches[1];
    }
    
    // Build SQL
    $sql = "SELECT student_id, full_name, email, gender, grade_level, strand, section_block, created_at FROM students";
    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY gender, full_name";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($students)) {
            return "I couldn't find any students matching your criteria. Try rephrasing your question or check if the data exists.";
        }
        
        // Count by gender
        $male_count = 0;
        $female_count = 0;
        foreach ($students as $s) {
            if ($s['gender'] === 'Male') $male_count++;
            if ($s['gender'] === 'Female') $female_count++;
        }
        
        $response = "**Student Query Results**\n\n";
        $response .= "**Summary:**\n";
        $response .= "- Total: " . count($students) . " students\n";
        $response .= "- Male: {$male_count}\n";
        $response .= "- Female: {$female_count}\n\n";
        
        if (isset($params[':strand'])) {
            $response .= "**Strand:** {$params[':strand']}\n";
        }
        if (isset($params[':grade'])) {
            $response .= "**Grade Level:** {$params[':grade']}\n";
        }
        
        $response .= "\n**Student List:**\n\n";
        
        foreach ($students as $s) {
            $response .= "👤 **{$s['full_name']}**\n";
            $response .= "   • ID: {$s['student_id']}\n";
            $response .= "   • Email: {$s['email']}\n";
            $response .= "   • Gender: {$s['gender']}\n";
            $response .= "   • Grade: {$s['grade_level']} | Strand: {$s['strand']} | Section: {$s['section_block']}\n";
            $response .= "   • Enrolled: " . date('M d, Y', strtotime($s['created_at'])) . "\n\n";
        }
        
        return $response;
        
    } catch (PDOException $e) {
        return "Database error while fetching students: " . $e->getMessage();
    }
}

function handleTeacherQuery($question, $pdo) {
    $q = strtolower($question);
    $where = [];
    $params = [];
    
    // Parse gender filter
    if (preg_match('/\b(male|mr|sir)\b/i', $question) && !preg_match('/\bfemale\b/i', $question)) {
        $where[] = "gender = :gender";
        $params[':gender'] = 'Male';
    } elseif (preg_match('/\b(female|ms|mrs|miss|ma\'am)\b/i', $question)) {
        $where[] = "gender = :gender";
        $params[':gender'] = 'Female';
    }
    
    // Parse faculty filter
    if (preg_match('/\b(stem)\b/i', $question)) {
        $where[] = "faculty = :faculty";
        $params[':faculty'] = 'STEM';
    } elseif (preg_match('/\b(abm)\b/i', $question)) {
        $where[] = "faculty = :faculty";
        $params[':faculty'] = 'ABM';
    } elseif (preg_match('/\b(humss)\b/i', $question)) {
        $where[] = "faculty = :faculty";
        $params[':faculty'] = 'HUMSS';
    }
    
    // Build SQL
    $sql = "SELECT id, full_name, email, gender, grade_level, strand, section_block, faculty, created_at FROM teachers";
    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY full_name";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($teachers)) {
            return "I couldn't find any teachers matching your criteria.";
        }
        
        $male_count = 0;
        $female_count = 0;
        foreach ($teachers as $t) {
            if ($t['gender'] === 'Male') $male_count++;
            if ($t['gender'] === 'Female') $female_count++;
        }
        
        $response = "**Teacher Directory**\n\n";
        $response .= "**Summary:**\n";
        $response .= "- Total: " . count($teachers) . " teachers\n";
        $response .= "- Male: {$male_count}\n";
        $response .= "- Female: {$female_count}\n\n";
        
        $response .= "**Teacher List:**\n\n";
        
        foreach ($teachers as $t) {
            $response .= "👨‍🏫 **{$t['full_name']}**\n";
            $response .= "   • Email: {$t['email']}\n";
            $response .= "   • Gender: {$t['gender']}\n";
            $response .= "   • Advisory: Grade {$t['grade_level']} {$t['strand']} - {$t['section_block']}\n";
            $response .= "   • Faculty: {$t['faculty']}\n";
            $response .= "   • Since: " . date('M d, Y', strtotime($t['created_at'])) . "\n\n";
        }
        
        return $response;
        
    } catch (PDOException $e) {
        return "Database error while fetching teachers: " . $e->getMessage();
    }
}

function handleAttendanceQuery($question, $pdo) {
    $q = strtolower($question);
    $where = ["DATE(attendance_date) = CURDATE()"];
    $params = [];
    
    // Parse status filter
    if (preg_match('/\babsent\b/i', $question)) {
        $where[] = "status = :status";
        $params[':status'] = 'absent';
    } elseif (preg_match('/\bpresent\b/i', $question)) {
        $where[] = "status = :status";
        $params[':status'] = 'present';
    } elseif (preg_match('/\blate\b/i', $question)) {
        $where[] = "status = :status";
        $params[':status'] = 'late';
    }
    
    // Parse date
    if (preg_match('/\byesterday\b/i', $question)) {
        $where[0] = "DATE(attendance_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
    } elseif (preg_match('/\blast week\b/i', $question)) {
        $where[0] = "attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    }
    
    $sql = "SELECT ar.*, s.full_name, s.email, s.gender, s.grade_level, s.strand, s.section_block 
            FROM attendance_records ar
            JOIN students s ON ar.student_id = s.student_id
            WHERE " . implode(" AND ", $where) . "
            ORDER BY ar.attendance_date DESC, s.full_name";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($records)) {
            return "No attendance records found for your criteria.";
        }
        
        $response = "**Attendance Records**\n\n";
        $response .= "**Total:** " . count($records) . " records\n\n";
        
        foreach ($records as $r) {
            $response .= "📋 **{$r['full_name']}** ({$r['gender']})\n";
            $response .= "   • ID: {$r['student_id']}\n";
            $response .= "   • Class: Grade {$r['grade_level']} {$r['strand']} - {$r['section_block']}\n";
            $response .= "   • Date: " . date('M d, Y', strtotime($r['attendance_date'])) . "\n";
            $response .= "   • Status: " . strtoupper($r['status']) . "\n";
            if ($r['morning_in']) $response .= "   • Morning In: " . date('h:i A', strtotime($r['morning_in'])) . "\n";
            if ($r['morning_out']) $response .= "   • Morning Out: " . date('h:i A', strtotime($r['morning_out'])) . "\n";
            if ($r['afternoon_in']) $response .= "   • Afternoon In: " . date('h:i A', strtotime($r['afternoon_in'])) . "\n";
            if ($r['afternoon_out']) $response .= "   • Afternoon Out: " . date('h:i A', strtotime($r['afternoon_out'])) . "\n";
            $response .= "\n";
        }
        
        return $response;
        
    } catch (PDOException $e) {
        return "Database error while fetching attendance: " . $e->getMessage();
    }
}
