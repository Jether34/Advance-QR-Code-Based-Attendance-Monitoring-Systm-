<?php
// db.php - PDO MySQL connection using environment variables (.env)

function get_db(){
    $host    = getenv('DB_HOST')    ?: 'localhost';
    $db      = getenv('DB_NAME')    ?: 'attendance_qr_system';
    $user    = getenv('DB_USER')    ?: 'root';
    $passEnv = getenv('DB_PASS');
    $pass    = $passEnv !== false ? $passEnv : '';
    $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
}
