<?php
// Get current page name (e.g., 'dashboard.php' or 'products.php')
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav id="sidebar">
    <div class="sidebar-header d-flex justify-content-between align-items-center">
    <h3 style="padding:5px">
       <img src="logo-web.png" width="200px" height="60px">
    </h3>
    <button class="btn btn-sm btn-outline-light d-md-none" onclick="document.getElementById('sidebar').classList.remove('active')">
        <i class="fas fa-times"></i>
    </button>
</div>
    
    <ul class="list-unstyled components">
        <li class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        </li>
        
        <li class="<?= $current_page == 'products.php' ? 'active' : '' ?>">
            <a href="products.php"><i class="fas fa-box-open me-2"></i> Products</a>
        </li>
        
        <li class="<?= $current_page == 'reports.php' ? 'active' : '' ?>">
            <a href="reports.php"><i class="fas fa-chart-line me-2"></i> Reports</a>
        </li>

        <?php if(isAdmin()): ?>
        <li class="<?= $current_page == 'users.php' ? 'active' : '' ?>">
            <a href="users.php"><i class="fas fa-users-cog me-2"></i> Users</a>
        </li>
        <?php endif; ?>
        <?php if(isAdmin()): ?>
         <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'active' : ''; ?>">
        <a href="activity_logs.php">
            <i class="fas fa-history"></i> Activity Logs
        </a>
    </li>
<?php endif; ?>
    </ul>
    <div class="text-center mt-5">
        <a href="logout.php" class="btn btn-outline-light btn-sm w-75">Logout</a>
    </div>
    <div class="text-center mt-3">
    <button id="installBtn" class="btn btn-sm btn-light w-75" style="display:none;">
        <i class="fas fa-download me-2"></i> Install App
    </button>
</div>
</nav>
<script>
let deferredPrompt;
const installBtn = document.getElementById('installBtn');

window.addEventListener('beforeinstallprompt', (e) => {
  // Prevent Chrome 67 and earlier from automatically showing the prompt
  e.preventDefault();
  // Stash the event so it can be triggered later.
  deferredPrompt = e;
  // Update UI to notify the user they can add to home screen
  installBtn.style.display = 'inline-block';

  installBtn.addEventListener('click', (e) => {
    // Hide our user interface that shows our A2HS button
    installBtn.style.display = 'none';
    // Show the prompt
    deferredPrompt.prompt();
    // Wait for the user to respond to the prompt
    deferredPrompt.userChoice.then((choiceResult) => {
      if (choiceResult.outcome === 'accepted') {
        console.log('User accepted the A2HS prompt');
      }
      deferredPrompt = null;
    });
  });
});
</script>