<?php
// developer_logout.php - Logout handler for developer dashboard
require_once __DIR__ . '/bootstrap.php';

// Clear developer session
unset($_SESSION['developer_id']);
unset($_SESSION['developer_username']);
unset($_SESSION['developer_role']);

// Destroy session if no other user is logged in
if (!isset($_SESSION['user_id'])) {
    session_destroy();
}

// Redirect to index
header('Location: index.php');
exit;

?>
