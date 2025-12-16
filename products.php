<?php
require 'config.php';
require 'functions.php';

requireLogin();

$success_msg = '';
$error_msg = '';

// --- LOGIC: Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token']);
    
    // 1. ADD Product
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['name']);
        $sku = trim($_POST['sku']);
        $qty = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];

        if ($name && $sku) {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (name, sku, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $sku, $qty, $price]);
                $success_msg = "Product added successfully!";
                logActivity($pdo, $_SESSION['user_id'], 'ADD_ITEM', "Added new product: " . $_POST['name']);
            } catch (PDOException $e) {
                $error_msg = "Error: SKU '$sku' already exists.";
            }
        }
    }

    // 2. UPDATE Product (NEW)
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $sku = trim($_POST['sku']);
        $qty = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];

        if ($name && $sku) {
            try {
                $stmt = $pdo->prepare("UPDATE products SET name=?, sku=?, quantity=?, price=? WHERE id=?");
                $stmt->execute([$name, $sku, $qty, $price, $id]);
                $success_msg = "Product updated successfully!";
                logActivity($pdo, $_SESSION['user_id'], 'UPDATE_STOCK', "Updated {$_POST['id']}-{$_POST['name']} (Qty: {$_POST['quantity']})");
            } catch (PDOException $e) {
                $error_msg = "Error: Cannot update. SKU '$sku' might already exist.";
            }
        }
    }

    // 3. DELETE Product
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        requireAdmin();
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $success_msg = "Product deleted.";
        logActivity($pdo, $_SESSION['user_id'], 'DELETE_ITEM', "Deleted product: $prodName");
    }
}

// Fetch All Products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - Stockman</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
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

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= e($success_msg) ?> <button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= e($error_msg) ?> <button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="custom-table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark m-0">All Products</h5>
                <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i> Add Product
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td class="fw-bold"><?= e($p['name']) ?></td>
                            <td class="text-muted small"><?= e($p['sku']) ?></td>
                            <td>
                                <?php if($p['quantity'] < 5): ?>
                                    <span class="badge bg-warning text-dark">Low: <?= e($p['quantity']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= e($p['quantity']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>$<?= number_format($p['price'], 2) ?></td>
                            <td class="text-end">
                                <button class="btn btn-circle btn-outline-warning edit-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal"
                                        data-id="<?= e($p['id']) ?>"
                                        data-name="<?= e($p['name']) ?>"
                                        data-sku="<?= e($p['sku']) ?>"
                                        data-qty="<?= e($p['quantity']) ?>"
                                        data-price="<?= e($p['price']) ?>">
                                    <i class="fas fa-pen"></i>
                                </button>

                                <?php if (isAdmin()): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                                        <button class="btn btn-circle btn-outline-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Add Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">NAME</label>
                        <input type="text" name="name" class="form-control bg-light border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">SKU</label>
                        <input type="text" name="sku" class="form-control bg-light border-0" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small fw-bold">QTY</label>
                            <input type="number" name="quantity" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small fw-bold">PRICE</label>
                            <input type="number" step="0.01" name="price" class="form-control bg-light border-0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">NAME</label>
                        <input type="text" name="name" id="edit_name" class="form-control bg-light border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">SKU</label>
                        <input type="text" name="sku" id="edit_sku" class="form-control bg-light border-0" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small fw-bold">QTY</label>
                            <input type="number" name="quantity" id="edit_qty" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small fw-bold">PRICE</label>
                            <input type="number" step="0.01" name="price" id="edit_price" class="form-control bg-light border-0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="submit" class="btn btn-warning px-4">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar Toggle
    document.getElementById('sidebarCollapse').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('active');
    });

    // Populate Edit Modal Logic
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', event => {
            // Button that triggered the modal
            const button = event.relatedTarget;
            
            // Extract info from data-* attributes
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const sku = button.getAttribute('data-sku');
            const qty = button.getAttribute('data-qty');
            const price = button.getAttribute('data-price');

            // Update the modal's content
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_sku').value = sku;
            document.getElementById('edit_qty').value = qty;
            document.getElementById('edit_price').value = price;
        });
    }
</script>
</body>
</html>