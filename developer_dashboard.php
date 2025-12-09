<?php
// developer_dashboard.php - Main dashboard for developers/admins
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/page_security.php';

// Initialize page security
init_page_security();

// Check if logged in as developer
if (!isset($_SESSION['developer_id'])) {
    header('Location: developer_login.php');
    exit;
}

$pdo = get_db();

// Fetch system statistics
try {
    $stats = [];
    
    // Total students
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM students');
    $stats['students'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total teachers
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM teachers');
    $stats['teachers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total attendance records
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM attendance_records');
    $stats['attendance'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Today's attendance
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM attendance_records WHERE DATE(attendance_date) = CURDATE()');
    $stats['today_attendance'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Traffic analytics (last 24 hours)
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM system_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stats['traffic_24h'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM system_events WHERE event_type LIKE '%login%' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stats['logins_24h'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM system_events WHERE success = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stats['failed_24h'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Recent system events (last 10)
        $stmt = $pdo->query("SELECT * FROM system_events ORDER BY created_at DESC LIMIT 10");
        $recent_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $stats['traffic_24h'] = 0;
        $stats['logins_24h'] = 0;
        $stats['failed_24h'] = 0;
        $recent_events = [];
    }
    
    // Recent activity (last 10 attendance records)
    $stmt = $pdo->query('
        SELECT ar.*, s.full_name, s.student_id 
        FROM attendance_records ar
        LEFT JOIN students s ON ar.student_id = s.student_id
        ORDER BY ar.attendance_date DESC, ar.created_at DESC
        LIMIT 10
    ');
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Dashboard - PNS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            background: linear-gradient(135deg, #1e5128 0%, #2d6a4f 100%);
            min-height: 100vh;
            padding: 24px;
            color: #2c3e50;
        }
        .container {
            max-width: 1440px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
            border-radius: 20px;
            padding: 28px 40px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid rgba(255,255,255,0.8);
        }
        .header h1 {
            color: #1e5128;
            font-size: 2em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 14px;
            letter-spacing: -0.5px;
        }
        .header-actions {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .user-info {
            color: #5a6c7d;
            font-size: 0.95em;
            margin-right: 20px;
            padding: 8px 16px;
            background: #f0f7f4;
            border-radius: 10px;
            border-left: 3px solid #2d6a4f;
        }
        .user-info strong {
            color: #1e5128;
        }
        .btn {
            padding: 11px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 0.95em;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .btn-logout {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: #fff;
        }
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
            border-radius: 16px;
            padding: 32px 28px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            border: 1px solid rgba(45, 106, 79, 0.1);
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #2d6a4f 0%, #1e5128 100%);
        }
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06);
        }
        .stat-icon {
            font-size: 3em;
            margin-bottom: 16px;
            filter: grayscale(0.2);
        }
        .stat-value {
            font-size: 2.5em;
            font-weight: 800;
            color: #1e5128;
            margin-bottom: 8px;
            letter-spacing: -1px;
        }
        .stat-label {
            color: #5a6c7d;
            font-size: 0.95em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section {
            background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
            border-radius: 20px;
            padding: 32px 40px;
            margin-bottom: 28px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1), 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid rgba(45, 106, 79, 0.08);
        }
        .section h2 {
            color: #1e5128;
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 3px solid #d8f3dc;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }
        .quick-link {
            background: linear-gradient(135deg, #2d6a4f 0%, #1e5128 100%);
            color: #fff;
            padding: 24px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95em;
            transition: all 0.3s ease;
            text-align: center;
            box-shadow: 0 4px 12px rgba(45, 106, 79, 0.3);
            border: 2px solid rgba(255,255,255,0.1);
        }
        .quick-link:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(45, 106, 79, 0.5);
            background: linear-gradient(135deg, #1e5128 0%, #2d6a4f 100%);
        }
        .activity-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border-radius: 12px;
        }
        .activity-table th {
            background: linear-gradient(135deg, #d8f3dc 0%, #b7e4c7 100%);
            padding: 16px 14px;
            text-align: left;
            font-weight: 700;
            color: #1e5128;
            font-size: 0.88em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .activity-table th:first-child {
            border-radius: 12px 0 0 0;
        }
        .activity-table th:last-child {
            border-radius: 0 12px 0 0;
        }
        .activity-table td {
            padding: 14px;
            border-bottom: 1px solid #e8f5e9;
            font-size: 0.92em;
            background: #fff;
        }
        .activity-table tr:last-child td:first-child {
            border-radius: 0 0 0 12px;
        }
        .activity-table tr:last-child td:last-child {
            border-radius: 0 0 12px 0;
        }
        .activity-table tbody tr:hover {
            background: #f0f7f4;
        }
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.82em;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .status-present { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724; border: 1px solid #b1dfbb; }
        .status-late { background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); color: #856404; border: 1px solid #ffe08a; }
        .status-absent { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #721c24; border: 1px solid #f1aeb5; }
        .status-excuse { background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); color: #0c5460; border: 1px solid #abdde5; }
        
        /* AI Assistant Styles */
        .ai-container { background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%); border-radius: 20px; padding: 32px 40px; margin-bottom: 28px; box-shadow: 0 10px 40px rgba(0,0,0,0.1), 0 2px 8px rgba(0,0,0,0.05); border: 1px solid rgba(45, 106, 79, 0.08); }
        .ai-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .ai-header h2 { color: #1e5128; font-size: 1.5em; margin: 0; }
        .ai-badge { background: linear-gradient(135deg, #2d6a4f 0%, #1e5128 100%); color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 0.75em; font-weight: 700; }
        .ai-chat { background: #f8fffe; border: 2px solid #d8f3dc; border-radius: 12px; padding: 20px; max-height: 400px; overflow-y: auto; margin-bottom: 16px; }
        .ai-message { margin-bottom: 16px; padding: 12px 16px; border-radius: 10px; line-height: 1.6; }
        .ai-message.user { background: #e6f7ed; border-left: 4px solid #2d6a4f; }
        .ai-message.assistant { background: #fff; border-left: 4px solid #1e5128; white-space: pre-line; }
        .ai-input-group { display: flex; gap: 10px; }
        .ai-input { flex: 1; padding: 12px 16px; border: 2px solid #d8f3dc; border-radius: 10px; font-size: 0.95em; }
        .ai-input:focus { outline: none; border-color: #2d6a4f; }
        .ai-btn { padding: 12px 24px; background: linear-gradient(135deg, #2d6a4f 0%, #1e5128 100%); color: #fff; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .ai-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(45, 106, 79, 0.4); }
        .ai-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .ai-suggestions { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
        .ai-suggestion { padding: 6px 12px; background: #d8f3dc; color: #1e5128; border: 1px solid #b7e4c7; border-radius: 20px; font-size: 0.85em; cursor: pointer; transition: all 0.2s; }
        .ai-suggestion:hover { background: #b7e4c7; transform: scale(1.05); }
    </style>
    <script>
        let chatHistory = [];
        
        async function askAI(question) {
            if (!question.trim()) return;
            
            const chatDiv = document.getElementById('ai-chat');
            const input = document.getElementById('ai-input');
            const btn = document.getElementById('ai-btn');
            
            // Add user message
            const userMsg = document.createElement('div');
            userMsg.className = 'ai-message user';
            userMsg.innerHTML = '<strong>You:</strong> ' + escapeHtml(question);
            chatDiv.appendChild(userMsg);
            
            // Disable input
            input.value = '';
            input.disabled = true;
            btn.disabled = true;
            btn.textContent = 'Thinking...';
            
            // Add loading message
            const loadingMsg = document.createElement('div');
            loadingMsg.className = 'ai-message assistant';
            loadingMsg.id = 'loading-msg';
            loadingMsg.innerHTML = '<strong>Jether:</strong> Analyzing data...';
            chatDiv.appendChild(loadingMsg);
            chatDiv.scrollTop = chatDiv.scrollHeight;
            
            try {
                const response = await fetch('developer_ai_assistant.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ question: question })
                });
                
                const data = await response.json();
                
                // Remove loading message
                loadingMsg.remove();
                
                if (data.success) {
                    const assistantMsg = document.createElement('div');
                    assistantMsg.className = 'ai-message assistant';
                    assistantMsg.innerHTML = '<strong>Jether:</strong>\n' + escapeHtml(data.answer);
                    chatDiv.appendChild(assistantMsg);
                    
                    chatHistory.push({ question, answer: data.answer });
                } else {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'ai-message assistant';
                    errorMsg.innerHTML = '<strong>Jether:</strong>\n' + escapeHtml(data.answer || data.error || 'Unknown error');
                    chatDiv.appendChild(errorMsg);
                }
            } catch (error) {
                loadingMsg.remove();
                const errorMsg = document.createElement('div');
                errorMsg.className = 'ai-message assistant';
                errorMsg.innerHTML = '<strong>Error:</strong> Failed to connect to AI assistant.\n\n' +
                                   'Details: ' + escapeHtml(error.message) + '\n\n' +
                                   'Please check:\n' +
                                   '• Database connection is active\n' +
                                   '• system_events table exists (run complete_database_setup.sql)\n' +
                                   '• PHP error logs for details';
                chatDiv.appendChild(errorMsg);
            }
            
            // Re-enable input
            input.disabled = false;
            btn.disabled = false;
            btn.textContent = 'Ask';
            input.focus();
            chatDiv.scrollTop = chatDiv.scrollHeight;
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('ai-input');
            const btn = document.getElementById('ai-btn');
            
            btn.addEventListener('click', () => askAI(input.value));
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') askAI(input.value);
            });
            
            // Suggestion buttons
            document.querySelectorAll('.ai-suggestion').forEach(btn => {
                btn.addEventListener('click', () => {
                    input.value = btn.textContent;
                    askAI(btn.textContent);
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Developer Dashboard</h1>
            <div class="header-actions">
                <span class="user-info">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['developer_username']); ?></strong></span>
                <a href="developer_logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>
        
        <div class="ai-container">
            <div class="ai-header">
                <h2>🤖 Jether AI</h2>
                <span class="ai-badge">LIVE</span>
            </div>
            
            <div class="ai-suggestions">
                <button class="ai-suggestion">Who created this system?</button>
                <button class="ai-suggestion">Explain the database schema</button>
                <button class="ai-suggestion">What are the main PHP files?</button>
                <button class="ai-suggestion">Security analysis</button>
                <button class="ai-suggestion">Show attendance trends</button>
                <button class="ai-suggestion">System overview</button>
            </div>
            
            <div id="ai-chat" class="ai-chat">
                <div class="ai-message assistant">
                    <strong>Jether:</strong> Hi! I'm Jether AI Assistant, powered by Llama 3.2. I have comprehensive knowledge of this QR-Based Attendance System including:
                    
• Frontend: HTML5, CSS3, JavaScript (QR scanning)
• Backend: PHP files, database schema, system architecture
• Database: All 8 tables (students, teachers, attendance_records, system_events, posts, etc.)
• Real-time data: Traffic logs, attendance patterns, security analysis
• System info: Created by Jether Garque (Grade 12 ICT Student) for Palawan National School

Ask me about the system architecture, database schema, creator information, or analyze live data!
                </div>
            </div>
            
            <div class="ai-input-group">
                <input type="text" id="ai-input" class="ai-input" placeholder="Ask about system status, security, attendance patterns..." />
                <button id="ai-btn" class="ai-btn">Ask</button>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👨‍🎓</div>
                <div class="stat-value"><?php echo number_format($stats['students']); ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-value"><?php echo number_format($stats['teachers']); ?></div>
                <div class="stat-label">Total Teachers</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-value"><?php echo number_format($stats['attendance']); ?></div>
                <div class="stat-label">Total Attendance Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?php echo number_format($stats['today_attendance']); ?></div>
                <div class="stat-label">Today's Attendance</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value"><?php echo number_format($stats['traffic_24h']); ?></div>
                <div class="stat-label">Events (24h)</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔐</div>
                <div class="stat-value"><?php echo number_format($stats['logins_24h']); ?></div>
                <div class="stat-label">Logins (24h)</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⚠️</div>
                <div class="stat-value"><?php echo number_format($stats['failed_24h']); ?></div>
                <div class="stat-label">Failed Events (24h)</div>
            </div>
        </div>
        
        <div class="section">
            <h2>🚀 Quick Actions</h2>
            <div class="quick-links">
                <a href="admin_setup.php" class="quick-link">🔧 Setup Admin Table</a>
                <a href="init_db.php" class="quick-link">💾 Initialize Database</a>
                <a href="scan.php" class="quick-link">📷 QR Scanner</a>
                <a href="test_connection.php" class="quick-link">🔌 Test DB Connection</a>
                <a href="developer_students.php" class="quick-link">👤 Student Directory</a>
                <a href="developer_teachers.php" class="quick-link">👨‍🏫 Teacher Directory</a>
                <a href="developer_traffic.php" class="quick-link">📈 Traffic Monitor</a>
            </div>
        </div>
        
        <div class="section">
            <h2>🔔 Recent System Events</h2>
            <?php if (!empty($recent_events)): ?>
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Event</th>
                            <th>User</th>
                            <th>IP</th>
                            <th>Device</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_events as $evt): ?>
                            <?php
                              // Parse device from UA
                              $ua = $evt['user_agent'] ?? '';
                              $device = 'Unknown';
                              if ($ua) {
                                if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false) {
                                  $device = 'Mobile';
                                } elseif (stripos($ua, 'Tablet') !== false || stripos($ua, 'iPad') !== false) {
                                  $device = 'Tablet';
                                } else {
                                  $device = 'Desktop';
                                }
                              }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('M d, H:i', strtotime($evt['created_at']))); ?></td>
                                <td><?php echo htmlspecialchars($evt['event_type']); ?></td>
                                <td><?php echo htmlspecialchars($evt['email'] ?? ($evt['user_role'] ?? 'N/A')); ?></td>
                                <td><?php echo htmlspecialchars($evt['ip_address'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($device); ?></td>
                                <td>
                                    <?php if ((int)$evt['success'] === 1): ?>
                                        <span class="status-badge status-present">OK</span>
                                    <?php else: ?>
                                        <span class="status-badge status-absent">FAIL</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 20px;">No recent system events found.</p>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>📊 Recent Activity</h2>
            <?php if (!empty($recent_activity)): ?>
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_activity as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['student_id'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($record['full_name'] ?? 'Unknown'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo htmlspecialchars($record['status']); ?>">
                                        <?php echo strtoupper($record['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($record['attendance_date']))); ?></td>
                                <td><?php echo htmlspecialchars($record['time_in'] ?? '--'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 20px;">No recent activity found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Prevent back button from showing cached page
        window.history.pushState(null, "", window.location.href);        
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
        };
    </script>
</body>
</html>
