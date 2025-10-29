<?php
require_once 'config.php';
require_once 'api/log_activity.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $input_username = trim($_POST['username']);
        $input_password = $_POST['password'];

        try {
            $stmt = $pdo->prepare("SELECT id, username, fullname, password, role, status FROM users WHERE username = ?");
            $stmt->execute([$input_username]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'Invalid username or password.';
            } elseif ($user['status'] !== 'active') {
                $error = 'Your account is inactive. Please contact administrator.';
            } elseif (!password_verify($input_password, $user['password'])) {
                $error = 'Invalid username or password.';
            } else {
                // Successful login
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];

                logActivity('user_login');

                header('Location: dashboard.php');
                exit();
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPDO Database System - Login</title>
    <link href="https://unpkg.com/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="text-center mb-4">
            <img src="assets/limay.png" 
                 alt="MPDO Logo" 
                 class="logo rounded-circle mb-3"
                 onerror="this.onerror=null; this.src='https://via.placeholder.com/80/8B0000/FFFFFF?text=MPDO'">
            <div class="system-title">
                <h3 class="mb-1">MPDO Database System</h3>
                <p class="text-muted small">Municipal Planning & Development Office</p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="fas fa-user me-2"></i>Username
                </label>
                <input type="text" 
                       class="form-control" 
                       id="username" 
                       name="username" 
                       required 
                       autofocus
                       autocomplete="username">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-2"></i>Password
                </label>
                <div class="input-group">
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           required
                           autocomplete="current-password">
                    <button class="btn btn-outline-secondary" 
                            type="button" 
                            onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="fas fa-shield-alt me-1"></i>
                Secure Login • Version 2.0
            </small>
        </div>
    </div>

    <script src="https://unpkg.com/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>