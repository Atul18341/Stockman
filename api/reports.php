<?php
require 'config.php';
require 'functions.php';

requireLogin();

// --- LOGIC: Handle CSV Export ---
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $filename = "inventory_report_" . date('Y-m-d') . ".csv";
    
    // Set headers to force download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Column Headers
    fputcsv($output, ['ID', 'Product Name', 'SKU', 'Quantity', 'Price', 'Total Value', 'Status']);
    
    // Fetch Data for CSV
    $sql = "SELECT * FROM products ORDER BY name ASC";
    if (isset($_GET['filter']) && $_GET['filter'] === 'low_stock') {
        $sql = "SELECT * FROM products WHERE quantity < 5 ORDER BY quantity ASC";
    }
    
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch()) {
        $status = ($row['quantity'] < 5) ? 'Low Stock' : 'Active';
        if ($row['quantity'] == 0) $status = 'Out of Stock';
        
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['sku'],
            $row['quantity'],
            $row['price'],
            $row['quantity'] * $row['price'], // Calculated Total Value
            $status
        ]);
    }
    fclose($output);
    exit; // Stop script here so HTML doesn't get added to the CSV
}

// --- LOGIC: Fetch Data for Display ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$title = "Full Inventory Report";

$sql = "SELECT * FROM products ORDER BY name ASC";
if ($filter === 'low_stock') {
    $sql = "SELECT * FROM products WHERE quantity < 5 ORDER BY quantity ASC";
    $title = "Low Stock Alert Report";
}

$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();

// Calculate Totals for the Footer
$total_qty = 0;
$total_val = 0;
foreach($products as $p) {
    $total_qty += $p['quantity'];
    $total_val += ($p['quantity'] * $p['price']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Stockman</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        /* Print Styles: Hides Sidebar and Buttons when printing */
        @media print {
            #sidebar, .navbar, .btn-toolbar, .no-print { display: none !important; }
            #content { width: 100% !important; margin: 0 !important; padding: 0 !important; }
            body { background: white; -webkit-print-color-adjust: exact; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
    <meta name="theme-color" content="#4e73df">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/3094/3094851.png">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('sw.js')
                .then(reg => console.log('Service Worker registered!'))
                .catch(err => console.log('Service Worker failed:', err));
        });
    }
    </script>
</head>
<body>

<div class="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="content">
        <?php include 'navbar.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 btn-toolbar">
            <div>
                <h4 class="fw-bold text-gray-800 m-0">System Reports</h4>
                <p class="text-muted small m-0">Generated on: <?= date('M d, Y h:i A') ?></p>
            </div>
            <div class="btn-group">
                <a href="reports.php?filter=all" class="btn btn-outline-primary <?= $filter=='all'?'active':'' ?>">All Stock</a>
                <a href="reports.php?filter=low_stock" class="btn btn-outline-danger <?= $filter=='low_stock'?'active':'' ?>">Low Stock</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><?= e($title) ?></h6>
                <div class="no-print">
                    <a href="reports.php?export=csv&filter=<?= $filter ?>" class="btn btn-success btn-sm me-2">
                        <i class="fas fa-file-csv me-2"></i>Export CSV
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary btn-sm">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item Name</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                            <tr>
                                <td class="fw-bold"><?= e($p['name']) ?></td>
                                <td class="text-muted small"><?= e($p['sku']) ?></td>
                                <td>$<?= number_format($p['price'], 2) ?></td>
                                <td>
                                    <?php if($p['quantity'] < 5): ?>
                                        <span class="text-danger fw-bold"><?= e($p['quantity']) ?></span>
                                    <?php else: ?>
                                        <?= e($p['quantity']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>$<?= number_format($p['price'] * $p['quantity'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">TOTALS:</td>
                                <td><?= $total_qty ?> Units</td>
                                <td>$<?= number_format($total_val, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    <?php if(empty($products)): ?>
                        <div class="text-center py-5">
                            <p class="text-muted">No records found for this report.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarCollapse').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('active');
    });
</script>
</body>
</html>