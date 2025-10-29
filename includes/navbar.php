<?php
// Determine correct path based on current directory
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
if ($current_dir === 'documents' || $current_dir === 'reports') {
    $base = '..';
} else {
    $base = '.';
}
?>
<nav class="navbar">
    <div class="d-flex align-items-center gap-3">
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="navbar-brand">
            <?php if (file_exists(__DIR__ . '/../assets/limay.png')): ?>
                <img src="<?= $base ?>/assets/limay.png" 
                     alt="MPDO Logo" 
                     class="logo"
                     onerror="this.style.display='none'">
            <?php else: ?>
                <div class="logo rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" 
                     style="background: #800000; width: 40px; height: 40px;">
                    M
                </div>
            <?php endif; ?>
            <span class="navbar-text">MPDO Database System</span>
        </div>
    </div>
    
    <div class="user-info">
        <div class="text-end d-none d-md-block">
            <div class="fw-bold"><?= htmlspecialchars($_SESSION['fullname'] ?? 'User') ?></div>
            <small class="text-muted"><?= ucfirst($_SESSION['role'] ?? 'User') ?></small>
        </div>
        <div class="user-avatar">
            <?= strtoupper(substr($_SESSION['fullname'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="dropdown">
            <button class="btn btn-link text-white text-decoration-none dropdown-toggle" 
                    type="button" 
                    data-bs-toggle="dropdown">
                <i class="fas fa-cog"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="<?= $base ?>/change_password.php">
                        <i class="fas fa-lock me-2"></i>Change Password
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= $base ?>/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
    // Mobile menu toggle
    document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('mobile-active');
        document.getElementById('sidebarOverlay').classList.toggle('active');
    });

    document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.remove('mobile-active');
        this.classList.remove('active');
    });
</script>