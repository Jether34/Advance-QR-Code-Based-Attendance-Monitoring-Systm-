-- Create admin_users table for developer/admin access
-- Run this SQL in your MySQL console or via phpMyAdmin

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default developer account
-- Username: pns
-- Password: P@sEco123 (hashed using PASSWORD_DEFAULT)
INSERT INTO admin_users (username, password, role) VALUES
('pns', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'developer');
-- Note: The hash above is a placeholder. The actual hash will be generated when you run the PHP insert script.

-- To generate the proper hash, run this PHP snippet or use the admin_setup.php file:
-- password_hash('P@sEco123', PASSWORD_DEFAULT);
