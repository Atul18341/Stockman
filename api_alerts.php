<?php
// api_alerts.php
require 'config.php';

header('Content-Type: application/json');

// Check for valid session (Security)
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Fetch Low Stock Items (< 5 quantity)
try {
    $stmt = $pdo->query("SELECT id, name, quantity FROM products WHERE quantity < 5");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'count' => count($items),
        'items' => $items
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>