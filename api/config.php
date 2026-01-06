<?php
// config.php


$dsn = "mysql://A5Lt1VFmgS7zw6x.root:0FbFs4UCySG4r76J@gateway01.ap-southeast-1.prod.aws.tidbcloud.com:4000/test";
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
