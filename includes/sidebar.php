<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Determine the correct base path based on current directory
if ($current_dir === 'documents' || $current_dir === 'reports' || $current_dir === 'clearance') {
    $base = '..';
} else {
    $base = '.';
}
?>

<aside class="sidebar">
    <div class="sidebar-menu">
        <a href="<?= $base ?>/dashboard.php" 
           class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        <div class="sidebar-divider"></div>

        <!-- Documents Section -->
        <div class="sidebar-toggle <?= $current_dir === 'documents' ? 'active' : '' ?>" 
             onclick="toggleSubmenu(this)">
            <div>
                <i class="fas fa-file-alt"></i>
                <span>Documents</span>
            </div>
            <i class="fas fa-chevron-right toggle-icon"></i>
        </div>
        <div class="sidebar-submenu <?= $current_dir === 'documents' ? 'active' : '' ?>">
            <a href="<?= $base ?>/documents/index.php" 
               class="<?= $current_page === 'index.php' && $current_dir === 'documents' ? 'active' : '' ?>">
                <i class="fas fa-list"></i>
                <span>View All</span>
            </a>
            <a href="<?= $base ?>/documents/create.php" 
               class="<?= $current_page === 'create.php' && $current_dir === 'documents' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Submit New</span>
            </a>
        </div>

        <!-- Locational Clearance Section -->
        <div class="sidebar-toggle <?= $current_dir === 'clearance' ? 'active' : '' ?>" 
             onclick="toggleSubmenu(this)">
            <div>
                <i class="fas fa-map-marker-alt"></i>
                <span>Locational Clearance</span>
            </div>
            <i class="fas fa-chevron-right toggle-icon"></i>
        </div>
        <div class="sidebar-submenu <?= $current_dir === 'clearance' ? 'active' : '' ?>">
            <a href="<?= $base ?>/clearance/index.php" 
               class="<?= $current_page === 'index.php' && $current_dir === 'clearance' ? 'active' : '' ?>">
                <i class="fas fa-list"></i>
                <span>View All</span>
            </a>
            <a href="<?= $base ?>/clearance/create.php" 
               class="<?= $current_page === 'create.php' && $current_dir === 'clearance' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Submit New</span>
            </a>
        </div>

        <div class="sidebar-divider"></div>

        <!-- Reports Section (Admin Only) -->
        <?php if (isAdmin()): ?>
        <div class="sidebar-toggle <?= $current_dir === 'reports' ? 'active' : '' ?>" 
             onclick="toggleSubmenu(this)">
            <div>
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </div>
            <i class="fas fa-chevron-right toggle-icon"></i>
        </div>
        <div class="sidebar-submenu <?= $current_dir === 'reports' ? 'active' : '' ?>">
            <a href="<?= $base ?>/reports/audit.php" 
               class="<?= $current_page === 'audit.php' ? 'active' : '' ?>">
                <i class="fas fa-history"></i>
                <span>Audit Log</span>
            </a>
        </div>

        <div class="sidebar-divider"></div>
        <?php endif; ?>

        <!-- Settings Section -->
        <a href="<?= $base ?>/change_password.php" 
           class="<?= $current_page === 'change_password.php' ? 'active' : '' ?>">
            <i class="fas fa-lock"></i>
            <span>Change Password</span>
        </a>

        <?php if (isAdmin()): ?>
        <a href="<?= $base ?>/register.php" 
           class="<?= $current_page === 'register.php' ? 'active' : '' ?>">
            <i class="fas fa-user-plus"></i>
            <span>Register User</span>
        </a>
        <?php endif; ?>

        <div class="sidebar-divider"></div>

        <a href="<?= $base ?>/logout.php" class="text-danger">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<script>
    function toggleSubmenu(element) {
        element.classList.toggle('active');
        const submenu = element.nextElementSibling;
        submenu.classList.toggle('active');
    }

    // Auto-expand active section on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.sidebar-toggle.active').forEach(toggle => {
            toggle.classList.add('active');
        });
    });
</script>