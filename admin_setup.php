<?php
// admin_setup.php - One-time script to create admin_users table and insert default developer account
// Run this file once: http://localhost/puta/admin_setup.php
require_once __DIR__ . '/db.php';

$pdo = get_db();

try {
    // Create admin_users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    echo "✅ admin_users table created successfully.<br>";
    
    // Check if 'pns' user already exists
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = :username");
    $stmt->execute([':username' => 'pns']);
    
    if ($stmt->fetch()) {
        echo "ℹ️ Developer account 'pns' already exists.<br>";
    } else {
        // Insert default developer account with hashed password
        $hashedPassword = password_hash('P@sEco123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->execute([
            ':username' => 'pns',
            ':password' => $hashedPassword,
            ':role' => 'developer'
        ]);
        echo "✅ Developer account created successfully.<br>";
        echo "Username: <strong>pns</strong><br>";
        echo "Password: <strong>P@sEco123</strong><br>";
    }
    
    echo "<br><a href='index.php'>← Back to Home</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
}
?>
