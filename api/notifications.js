// notifications.js

// 1. Request Permission for Native Notifications
function requestNotificationPermission() {
    if ("Notification" in window && Notification.permission !== "granted") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                console.log("Notification permission granted.");
                checkForLowStock(); // Check immediately
            }
        });
    }
}

// 2. Function to Update UI and Trigger Native Alert
async function checkForLowStock() {
    try {
        const response = await fetch('api_alerts.php');
        const data = await response.json();

        // A. Update the Navbar Bell Badge
        const badge = document.getElementById('notification-badge');
        const bellIcon = document.getElementById('bell-icon');
        const listContainer = document.getElementById('notification-list');

        if (data.count > 0) {
            // Show Badge
            badge.innerText = data.count > 99 ? '99+' : data.count;
            badge.style.display = 'inline-block';
            
            // Animate Bell
            bellIcon.classList.add('bell-shake'); // We will add CSS for this

            // Populate Dropdown List
            let html = `<li class="dropdown-header text-uppercase small fw-bold text-muted">Low Stock Alerts</li>`;
            data.items.slice(0, 5).forEach(item => {
                html += `
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="products.php">
                        <div class="me-3 text-warning"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                            <span class="fw-bold d-block text-dark">${item.name}</span>
                            <small class="text-danger">Only ${item.quantity} left</small>
                        </div>
                    </a>
                </li>`;
            });
            
            if (data.count > 5) {
                html += `<li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-center small" href="reports.php?filter=low_stock">View All</a></li>`;
            }
            listContainer.innerHTML = html;

            // B. Trigger Native PWA Notification (System Alert)
            if ("Notification" in window && Notification.permission === "granted") {
                // Only notify if we haven't notified recently (to avoid spamming)
                const lastNotified = localStorage.getItem('last_alert_timestamp');
                const now = Date.now();
                
                // Notify once every hour (3600000 ms) or if never notified
                if (!lastNotified || now - lastNotified > 3600000) {
                    new Notification("Low Stock Alert!", {
                        body: `${data.count} items are running low. Check inventory now.`,
                        icon: "https://cdn-icons-png.flaticon.com/512/3094/3094851.png", // Use your PWA icon
                        vibrate: [200, 100, 200]
                    });
                    localStorage.setItem('last_alert_timestamp', now);
                }
            }

        } else {
            // Clear UI if no alerts
            badge.style.display = 'none';
            bellIcon.classList.remove('bell-shake');
            listContainer.innerHTML = `<li class="p-3 text-center text-muted small"><i class="fas fa-check-circle text-success mb-2"></i><br>All stock levels normal.</li>`;
        }

    } catch (error) {
        console.error("Error fetching alerts:", error);
    }
}

// 3. Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Check immediately on load
    checkForLowStock();
    
    // Check every 5 minutes (300,000 ms)
    setInterval(checkForLowStock, 300000);
    
    // Request permission if user clicks anywhere (browsers block auto-requests)
    document.addEventListener('click', requestNotificationPermission, { once: true });
});