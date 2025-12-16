<?php
// config.php
$host = '127.0.0.1:3308';
$db   = 'inventory_db';
$user = 'root';         // Default MySQL user (Change this for production!)
$pass = '';             // Default XAMPP password is empty (Change this!)
$charset = 'utf8mb4';   // Critical for security in MySQL

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // Critical: Forces MySQL to use real prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In production, log this error to a file instead of showing it
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>