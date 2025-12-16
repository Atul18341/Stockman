<?php
require 'config.php';
require 'functions.php';

// ... (Keep existing PHP Login Logic here exactly as before) ...
// ... Copy from previous conversation Step 1 of "Professional Look" ...

// Logic Summary for brevity:
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token']);
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stockman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css?v=<?= time(); ?>" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
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
    <div class="login-bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10 col-lg-12 col-md-9">
                    <div class="card login-card">
                        <div class="row g-0">
                            <div class="col-lg-6 d-none d-lg-block">
                                <img src="logo-app.png">
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-2 fw-bold" style="letter-spacing: 1px;">STOCKMAN</h1>
                                        <p class="text-muted mb-4 fst-italic">Apka apna stock manager</p>
                                    </div>
                                    
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger"><?= e($error) ?></div>
                                    <?php endif; ?>

                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
                                        <div class="mb-3">
                                            <input type="text" name="username" class="form-control form-control-lg rounded-pill fs-6" placeholder="Username" required>
                                        </div>
                                        <div class="mb-4">
                                            <input type="password" name="password" class="form-control form-control-lg rounded-pill fs-6" placeholder="Password" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold" style="background: #4e73df; border:none;">
                                            Login
                                        </button>
                                    </form>
                                    <hr>
                                    <div class="text-center small text-muted">
                                        &copy; 2024 LYSS Technology
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>