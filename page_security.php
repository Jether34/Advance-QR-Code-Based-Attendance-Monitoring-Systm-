<?php
// page_security.php - Page expiration and back button prevention

/**
 * Initialize page security for the current page
 * This prevents browser back button from showing cached pages
 */
function init_page_security() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set cache control headers to prevent page caching
    if (!headers_sent()) {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    }
    
    // Generate a unique page token for this request if not present
    $current_page = basename($_SERVER['PHP_SELF']);
    if (empty($_SESSION['current_page_token']) || ($_SESSION['current_page'] ?? '') !== $current_page) {
        $page_token = generate_page_token($current_page);
        // Store the current page token in session
        $_SESSION['current_page_token'] = $page_token;
        $_SESSION['current_page'] = $current_page;
        $_SESSION['page_load_time'] = time();
    } else {
        $page_token = $_SESSION['current_page_token'];
    }
    return $page_token;
}

/**
 * Validate if the page is being accessed fresh (not from back button)
 * Returns true if valid, redirects to expired page if invalid
 */
function validate_page_token() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Skip validation for login, logout, and index pages
    $current_page = $_SERVER['PHP_SELF'];
    $exempt_pages = ['login.php', 'logout.php', 'index.php', 'signup.php', 'process_signup.php', 'developer_login.php', 'developer_logout.php'];
    
    foreach ($exempt_pages as $exempt) {
        if (strpos($current_page, $exempt) !== false) {
            return true;
        }
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        return true; // Let auth system handle this
    }
    
    // Skip token check on first login (allow the redirect to work)
    if (isset($_SESSION['skip_page_token_check']) && $_SESSION['skip_page_token_check']) {
        unset($_SESSION['skip_page_token_check']);
        init_page_security();
        return true;
    }
    
    // Check if this is a POST request (form submission)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return true; // Allow POST requests
    }
    
    // Check if page token exists and matches
    $expected_token = isset($_SESSION['current_page_token']) ? $_SESSION['current_page_token'] : null;
    $page_token = isset($_GET['_pt']) ? $_GET['_pt'] : null;
    
    // For the first visit to a page, initialize the token
    if ($expected_token === null) {
        init_page_security();
        return true;
    }

    // If accessing via back button (no token or mismatched token), show expired page
    if ($page_token === null || $page_token !== $expected_token) {
        // Check if enough time has passed (more than 10 seconds suggests back button or stale token)
        $time_diff = time() - ($_SESSION['page_load_time'] ?? time());
        if ($time_diff > 10) {
            show_page_expired();
            exit;
        }
        // Otherwise allow short mismatches (race conditions during redirect/navigation)
    }
    
    return true;
}

/**
 * Generate a unique token for a page
 */
function generate_page_token($page) {
    return hash('sha256', $page . time() . session_id() . rand(1000, 9999));
}

/**
 * Add page token to URLs
 */
function secure_url($url) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = isset($_SESSION['current_page_token']) ? $_SESSION['current_page_token'] : '';
    $separator = (strpos($url, '?') !== false) ? '&' : '?';
    
    return $url . $separator . '_pt=' . urlencode($token);
}

/**
 * Show page expired message
 */
function show_page_expired() {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Page Expired - Palawan National School</title>
        <link rel="stylesheet" href="style.css">
        <style>
            body {
                background: linear-gradient(135deg, #d6f5d6 0%, #eaffea 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', Arial, sans-serif;
                padding: 20px;
            }
            .expired-container {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 8px 32px rgba(33, 140, 33, 0.2);
                padding: 50px 40px;
                max-width: 500px;
                text-align: center;
            }
            .expired-icon {
                font-size: 80px;
                margin-bottom: 20px;
            }
            .expired-container h1 {
                color: #218c21;
                font-size: 2em;
                margin-bottom: 16px;
            }
            .expired-container p {
                color: #176617;
                font-size: 1.1em;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            .btn-action {
                display: inline-block;
                padding: 14px 30px;
                background: #218c21;
                color: #fff;
                text-decoration: none;
                border-radius: 8px;
                font-size: 1.1em;
                font-weight: 600;
                transition: all 0.3s;
                margin: 5px;
            }
            .btn-action:hover {
                background: #176617;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(33, 140, 33, 0.3);
            }
            .btn-secondary {
                background: #7f8c8d;
            }
            .btn-secondary:hover {
                background: #5d6d6e;
            }
            .warning-text {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 12px 16px;
                margin: 20px 0;
                text-align: left;
                border-radius: 4px;
                font-size: 0.95em;
            }
        </style>
    </head>
    <body>
        <div class="expired-container">
            <div class="expired-icon">⏱️</div>
            <h1>Page Expired</h1>
            <p>This page has expired and can no longer be accessed. This happens when you use the browser's back button or try to access a previously viewed page.</p>
            <div class="warning-text">
                <strong>⚠️ Security Note:</strong> For your security, pages expire after navigation to prevent unauthorized access and data inconsistencies.
            </div>
            <div>
                <?php
                $dashboard_url = 'index.php';
                if (isset($_SESSION['role'])) {
                    switch ($_SESSION['role']) {
                        case 'student':
                            $dashboard_url = 'student_dashboard.php';
                            break;
                        case 'teacher':
                            $dashboard_url = 'teacher_dashboard.php';
                            break;
                        case 'developer':
                            $dashboard_url = 'developer_dashboard.php';
                            break;
                    }
                }
                ?>
                <a href="<?php echo $dashboard_url; ?>" class="btn-action">Go to Dashboard</a>
                <a href="logout.php" class="btn-action btn-secondary">Logout</a>
            </div>
        </div>
        
        <script>
            // Prevent going back to this expired page
            window.history.pushState(null, "", window.location.href);        
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
        </script>
    </body>
    </html>
    <?php
}

/**
 * Add JavaScript to prevent back button caching
 */
function add_back_button_prevention_script() {
    ?>
    <script>
        // Prevent page caching and back button
        (function() {
            window.history.pushState(null, "", window.location.href);        
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
            
            // Disable browser cache
            window.onload = function() {
                if (performance.navigation.type === 2) {
                    // Page accessed from back button
                    window.location.replace("<?php echo $_SERVER['PHP_SELF']; ?>");
                }
            };
        })();
    </script>
    <?php
}
?>
