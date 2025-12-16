<?php
require 'config.php';
require 'functions.php';

requireLogin();

// --- 1. Fetch Stats ---
$totalValStmt = $pdo->query("SELECT SUM(price * quantity) FROM products");
$total_value = $totalValStmt->fetchColumn() ?: 0;

$totalCountStmt = $pdo->query("SELECT SUM(quantity) FROM products");
$total_items = $totalCountStmt->fetchColumn() ?: 0;

$lowStockStmt = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity < 5");
$low_stock_count = $lowStockStmt->fetchColumn();

// --- 2. STOCKMAN LOGIC (The Brain) ---
// Determine what the manager should say based on data
$manager_msg = "";
$manager_mood = "happy"; // happy, warning, or danger
if ($low_stock_count > 5) {
    $manager_msg = "⚠️ High Alert! We have <b>$low_stock_count items</b> critically low. I recommend restocking immediately to avoid lost sales.";
    $manager_mood = "danger";
} elseif ($low_stock_count > 0) {
    $manager_msg = "🔔 Heads up! <b>$low_stock_count items</b> are running low. Check the 'Low Stock' report when you have a moment.";
    $manager_mood = "warning";
} elseif ($total_items == 0) {
    $manager_msg = "👋 Welcome to Stockman! Your warehouse is empty. Click <b>'Add Product'</b> to get started.";
    $manager_mood = "neutral";
} else {
    $manager_msg = "✅ Great job! Your inventory is healthy. You are currently managing <b>$" . number_format($total_value) . "</b> worth of assets.";
    $manager_mood = "happy";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Stockman</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
</head>
<body>
<link href="style.css?v=<?= time(); ?>" rel="stylesheet">
<div class="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="content">
        <?php include 'navbar.php'; ?>

        <div class="card border-0 shadow-sm mb-4 manager-card manager-<?= $manager_mood ?>">
            <div class="card-body p-4">
                <div class="d-flex align-items-start">
                    <div class="manager-icon me-4 d-none d-sm-block">
                        <img src="thumbnail-md.png" >
                    </div>
                    
                    <div>
                        <h5 class="fw-bold mb-1">
                            Stockman Insights 
                            <span class="badge bg-white text-dark border ms-2" style="font-size:0.7em">Apka apna stock manager</span>
                        </h5>
                        <p class="mb-0 mt-2 fs-5" style="line-height: 1.4; opacity: 0.9;">
                            "<?= $manager_msg ?>"
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <h5 class="mb-3 text-muted fw-bold text-uppercase small">Key Metrics</h5>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card stat-card bg-white text-dark h-100 border-start border-4 border-primary">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold">Total Value</h6>
                            <h2 class="mb-0 fw-bold">$<?= number_format($total_value, 2) ?></h2>
                        </div>
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card bg-white text-dark h-100 border-start border-4 border-success">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold">Items in Stock</h6>
                            <h2 class="mb-0 fw-bold text-success"><?= $total_items ?></h2>
                        </div>
                        <div class="icon-circle bg-success bg-opacity-10 text-success">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card bg-white text-dark h-100 border-start border-4 border-warning">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold">Low Stock</h6>
                            <h2 class="mb-0 fw-bold text-warning"><?= $low_stock_count ?></h2>
                        </div>
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                 <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Quick Actions</h6>
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="products.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add Stock</a>
                            <a href="reports.php" class="btn btn-light border"><i class="fas fa-print me-2"></i> Print Report</a>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="activity_logs.php" class="btn btn-dark">
                                <i class="fas fa-history me-2"></i> View Logs
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                 </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="notifications.js"></script> <script>
    document.getElementById('sidebarCollapse').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('active');
    });
</script>
</body>
</html>