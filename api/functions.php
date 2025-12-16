<?php
// functions.php

// 1. Secure Session Setup
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// 2. CSRF Protection
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('CSRF validation failed.');
    }
}

// 3. XSS Protection
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// 4. Auth Check
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
}

// Check if current user is Admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Block access if not Admin (Middleware)
function requireAdmin() {
    if (!isAdmin()) {
        die("ACCESS DENIED: You do not have permission to perform this action.");
    }
}

// Logs all activities

function logActivity($pdo, $user_id, $action, $description) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $action, $description]);
    } catch (PDOException $e) {
        // Silently fail or log to file so user flow isn't interrupted
        error_log("Log Error: " . $e->getMessage());
    }
}
?>