<?php
// navbar.php

// 1. Fetch Low Stock Items (Limit to 5 for the dropdown preview)
$notifyStmt = $pdo->query("SELECT id, name, sku, quantity FROM products WHERE quantity < 5 ORDER BY quantity ASC LIMIT 5");
$low_stock_items = $notifyStmt->fetchAll();

// 2. Count total alerts (in case there are more than 5)
$countStmt = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity < 5");
$alert_count = $countStmt->fetchColumn();
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4">
    <div class="container-fluid">
        <button type="button" id="sidebarCollapse" class="btn btn-light text-primary">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand ms-3 fw-bold text-primary d-md-none" href="#">Stockman</a>
        <div class="ms-auto d-flex align-items-center">
            
            <div class="dropdown me-3">
                <a href="#" class="text-secondary position-relative" id="notificationDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fa-lg" id="bell-icon"></i>
                    
                    <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">
                        0
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" id="notification-list" style="width: 300px;">
                    <li class="p-3 text-center text-muted small">Loading alerts...</li>
                </ul>
            </div>

            <div class="d-flex align-items-center">
                <div class="me-2 text-end d-none d-sm-block">
                    <span class="d-block fw-bold text-dark small"><?= e($_SESSION['username']) ?></span>
                </div>
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 35px; height: 35px;">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                </div>
            </div>
        </div>
    </div>
</nav>

<script src="notifications.js"></script>