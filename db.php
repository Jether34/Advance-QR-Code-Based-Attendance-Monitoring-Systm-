<?php
// db.php - simple PDO MySQL wrapper with PH timezone
//i have successfully integrate local network functions

// Ensure all PHP date()/time functions use Manila time
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Manila');
}

function get_db(){
    $host = 'localhost';
    $db   = 'attendance_qr_system';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        // Align MySQL session time zone with Manila as well
        try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $ignored) {}
        return $pdo;
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
}
