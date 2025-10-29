<?php
// Get current page and directory
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Determine the base path relative to current location
// This works for files in root, documents/, clearance/, admin/, reports/ folders
if ($current_dir === 'mpdo-database' || $current_page === 'dashboard.php' || $current_page === 'change_password.php' || $current_page === 'register_user.php') {
    // We're in root
    $base = '.';
} else {
    // We're in a subdirectory
    $base = '..';
}
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-menu">
        <a href="<?= $base ?>/dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <div class="sidebar-divider"></div>
        
        <a href="<?= $base ?>/documents/index.php" class="<?= $current_dir === 'documents' && $current_page === 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i>
            <span>Documents</span>
        </a>
        
        <a href="<?= $base ?>/clearance/index.php" class="<?= $current_dir === 'clearance' ? 'active' : '' ?>">
            <i class="fas fa-map-marker-alt"></i>
            <span>Locational Clearance</span>
        </a>
        
        <?php if (isAdmin()): ?>
            <div class="sidebar-divider"></div>
                <a href="<?= $base ?>/reports/audit.php" class="<?= $current_dir === 'reports' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
        <?php endif; ?>
        
        <?php if (isAdmin()): ?>           
            <a href="<?= $base ?>/admin/users.php" class="<?= $current_dir === 'admin' && $current_page === 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users-cog"></i>
                <span>User Management</span>
            </a>
        <?php endif; ?>
        
        <div class="sidebar-divider"></div>
        
        <a href="<?= $base ?>/change_password.php" class="<?= $current_page === 'change_password.php' ? 'active' : '' ?>">
            <i class="fas fa-lock"></i>
            <span>Change Password</span>
        </a>
        
        <?php if (isAdmin()): ?>
            <a href="<?= $base ?>/register.php" class="<?= $current_page === 'register.php' ? 'active' : '' ?>">
                <i class="fas fa-user-plus"></i>
                <span>Register User</span>
            </a>
        <?php endif; ?>
        
        <div class="sidebar-divider"></div>
        
        <a href="<?= $base ?>/logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
// Mobile menu toggle
const mobileToggle = document.querySelector('.mobile-menu-toggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');

if (mobileToggle) {
    mobileToggle.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-active');
        overlay.classList.toggle('active');
    });
}

if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-active');
        overlay.classList.remove('active');
    });
}
</script>