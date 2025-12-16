<?php
require 'config.php';
require 'functions.php'; // Ensure logActivity function is loaded

requireLogin();

// RESTRICT ACCESS: Only Admin should see logs
if ($_SESSION['role'] !== 'admin') {
    die("Access Denied: Admins only.");
}

// Fetch Logs with Usernames
$sql = "SELECT logs.*, users.username 
        FROM activity_logs AS logs 
        JOIN users ON logs.user_id = users.id 
        ORDER BY logs.created_at DESC 
        LIMIT 50"; // Limit to last 50 actions for performance
$stmt = $pdo->query($sql);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs - Stockman</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css?v=<?= time(); ?>" rel="stylesheet">
</head>
<body>

<div class="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="content">
        <?php include 'navbar.php'; ?>

        <div class="container-fluid">
            <h3 class="mb-4 text-gray-800"><i class="fas fa-history me-2"></i> Activity Logs</h3>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Recent System Activity</h6>
                    <span class="badge bg-secondary">Last 50 Actions</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($logs) > 0): ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td class="small text-muted" style="white-space:nowrap;">
                                                <?= date('M d, H:i', strtotime($log['created_at'])) ?>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">
                                                    <?= htmlspecialchars($log['username']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                // Style badges based on action type
                                                $badgeColor = 'secondary';
                                                if ($log['action_type'] == 'ADD_ITEM') $badgeColor = 'success';
                                                if ($log['action_type'] == 'UPDATE_STOCK') $badgeColor = 'warning text-dark';
                                                if ($log['action_type'] == 'DELETE_ITEM') $badgeColor = 'danger';
                                                if ($log['action_type'] == 'LOGIN') $badgeColor = 'primary';
                                                ?>
                                                <span class="badge bg-<?= $badgeColor ?>">
                                                    <?= $log['action_type'] ?>
                                                </span>
                                            </td>
                                            <td class="text-secondary">
                                                <?= htmlspecialchars($log['description']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No activity recorded yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="notifications.js"></script>
<script>
    document.getElementById('sidebarCollapse').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('active');
    });
</script>
</body>
</html>