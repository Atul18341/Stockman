<?php
$host = 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$port = '4000'; // Usually 4000
$user = 'A5Lt1VFmgS7zw6x.root';
$pass = '0FbFs4UCySG4r76J';
$dbname = 'test';

// DSN (Data Source Name) for MySQL compatible TiDB
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

// Connection options to enforce SSL/TLS, which is required for TiDB Cloud public connections.
// Use VERIFY_IDENTITY for robust security or a simpler mode if needed.
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_SSL_CA => './isrgrootx1.pem', // Optional path for dedicated clusters
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false // Ensure server cert is verified
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', 3600);
    session_set_cookie_params(3600);
    // -------------------------------------------------------------
    
    // Start Session globally
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Generate CSRF Token if not exists
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


?>
