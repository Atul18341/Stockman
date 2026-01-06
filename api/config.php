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
   // PDO::MYSQL_ATTR_SSL_CA => '/path/to/your/ca.pem', // Optional path for dedicated clusters
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false // Ensure server cert is verified
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected successfully to TiDB Cloud!";

    // Example query
    $stmt = $pdo->query('SELECT VERSION() AS tidb_version');
    $row = $stmt->fetch();
    echo "<br>TiDB Version: " . $row['tidb_version'];

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Close connection (optional for PHP scripts that end automatically)
$pdo = null;
?>
