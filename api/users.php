<?php
require 'config.php';
require 'functions.php';

// 1. SECURITY: Only Admins can access this page
requireLogin();
requireAdmin();

$success_msg = '';
$error_msg = '';

// --- LOGIC: Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token']);

    // Handle Add User
    if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role     = $_POST['role'];

        if ($username && $password) {
            // Check if username taken
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error_msg = "Error: Username '$username' is already taken.";
            } else {
                // Hash the password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $role]);
                $success_msg = "User '$username' created successfully.";
            }
        }
    }

    // Handle Delete User
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $id = (int)$_POST['user_id'];
        
        // Prevent deleting yourself
        if ($id == $_SESSION['user_id']) {
            $error_msg = "You cannot delete your own account!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $success_msg = "User deleted.";
        }
    }
}

// --- FETCH DATA ---
$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - InventoryOS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
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
                <div>
                    <h5 class="fw-bold text-dark m-0">System Users</h5>
                    <small class="text-muted">Manage access and roles</small>
                </div>
                <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-2"></i> Add New User
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Joined Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr class="<?= $u['id'] == $_SESSION['user_id'] ? 'table-active' : '' ?>">
                            <td class="text-muted">#<?= e($u['id']) ?></td>
                            <td class="fw-bold">
                                <?= e($u['username']) ?>
                                <?php if($u['id'] == $_SESSION['user_id']) echo '<span class="badge bg-info text-dark ms-2">You</span>'; ?>
                            </td>
                            <td>
                                <?php if($u['role'] === 'admin'): ?>
                                    <span class="badge bg-primary">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Staff</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td class="text-end">
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= e($u['id']) ?>">
                                        <button class="btn btn-circle btn-outline-danger" onclick="return confirm('Permanently delete user <?= e($u['username']) ?>?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-circle btn-light text-muted" disabled><i class="fas fa-ban"></i></button>
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

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Create New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">USERNAME</label>
                        <input type="text" name="username" class="form-control form-control-lg bg-light border-0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">PASSWORD</label>
                        <input type="password" name="password" class="form-control form-control-lg bg-light border-0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">ROLE</label>
                        <select name="role" class="form-select form-select-lg bg-light border-0">
                            <option value="staff">Staff (View & Add Only)</option>
                            <option value="admin">Admin (Full Control)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar Toggle Script
    document.getElementById('sidebarCollapse').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('active');
    });
</script>
</body>
</html>