<?php
require_once 'config.php';
require_once 'api/log_activity.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!password_verify($current, $user['password'])) {
                $error = 'Current password is incorrect.';
            } elseif ($new !== $confirm) {
                $error = 'New passwords do not match.';
            } elseif (strlen($new) < 8) {
                $error = 'New password must be at least 8 characters long.';
            } elseif (!preg_match('/[A-Z]/', $new)) {
                $error = 'Password must contain at least one uppercase letter.';
            } elseif (!preg_match('/[a-z]/', $new)) {
                $error = 'Password must contain at least one lowercase letter.';
            } elseif (!preg_match('/[0-9]/', $new)) {
                $error = 'Password must contain at least one number.';
            } else {
                $hashed = password_hash($new, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $_SESSION['user_id']]);

                logActivity('password_change');
                $message = 'Password updated successfully!';
            }
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - MPDO</title>
    <link href="https://unpkg.com/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <?php require_once 'includes/navbar.php'; ?>
    <?php require_once 'includes/sidebar.php'; ?>

    <main class="content-wrapper">
        <div class="container-fluid">
            <h2><i class="fas fa-lock me-2"></i>Change Password</h2>
            <p class="text-muted">Keep your account secure by using a strong password</p>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" id="passwordForm">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               name="current_password" 
                                               id="currentPassword"
                                               class="form-control" 
                                               required>
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                onclick="togglePasswordVisibility('currentPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               name="new_password" 
                                               id="newPassword" 
                                               class="form-control" 
                                               minlength="8" 
                                               required>
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                onclick="togglePasswordVisibility('newPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="progress mt-2" style="height: 8px; display: none;" id="strengthBar">
                                        <div class="progress-bar" id="strengthFill" role="progressbar"></div>
                                    </div>
                                    <div class="form-text" id="strengthText"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               name="confirm_password" 
                                               id="confirmPassword"
                                               class="form-control" 
                                               required>
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                onclick="togglePasswordVisibility('confirmPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text" id="matchText"></div>
                                </div>

                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-2"></i>Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Password Requirements</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2" id="req-length">
                                    <i class="fas fa-circle-notch text-muted"></i>
                                    At least 8 characters long
                                </li>
                                <li class="mb-2" id="req-uppercase">
                                    <i class="fas fa-circle-notch text-muted"></i>
                                    Contains uppercase letter (A-Z)
                                </li>
                                <li class="mb-2" id="req-lowercase">
                                    <i class="fas fa-circle-notch text-muted"></i>
                                    Contains lowercase letter (a-z)
                                </li>
                                <li class="mb-2" id="req-number">
                                    <i class="fas fa-circle-notch text-muted"></i>
                                    Contains number (0-9)
                                </li>
                                <li id="req-special">
                                    <i class="fas fa-circle-notch text-muted"></i>
                                    Contains special character (recommended)
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Security Tips:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Don't reuse passwords from other sites</li>
                            <li>Don't share your password with anyone</li>
                            <li>Change your password regularly</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordVisibility(fieldId, button) {
            const field = document.getElementById(fieldId);
            const icon = button.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function updateRequirement(id, met) {
            const elem = document.getElementById(id);
            const icon = elem.querySelector('i');
            
            if (met) {
                icon.className = 'fas fa-check-circle text-success';
            } else {
                icon.className = 'fas fa-circle-notch text-muted';
            }
        }

        function checkPasswordStrength(password) {
            let strength = 0;
            
            // Check requirements
            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[^A-Za-z0-9]/.test(password);
            
            // Update requirement indicators
            updateRequirement('req-length', hasLength);
            updateRequirement('req-uppercase', hasUppercase);
            updateRequirement('req-lowercase', hasLowercase);
            updateRequirement('req-number', hasNumber);
            updateRequirement('req-special', hasSpecial);
            
            // Calculate strength
            if (hasLength) strength += 20;
            if (hasUppercase) strength += 20;
            if (hasLowercase) strength += 20;
            if (hasNumber) strength += 20;
            if (hasSpecial) strength += 20;
            
            // Determine color and text
            let color = '#dc3545';
            let text = 'Weak';
            
            if (strength >= 80) {
                color = '#28a745';
                text = 'Strong';
            } else if (strength >= 60) {
                color = '#ffc107';
                text = 'Medium';
            }
            
            // Update progress bar
            document.getElementById('strengthFill').style.width = strength + '%';
            document.getElementById('strengthFill').style.backgroundColor = color;
            document.getElementById('strengthText').textContent = 'Password strength: ' + text;
            document.getElementById('strengthBar').style.display = 'block';
        }

        function checkPasswordMatch() {
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            const matchText = document.getElementById('matchText');
            
            if (confirmPass.length === 0) {
                matchText.textContent = '';
                return;
            }
            
            if (newPass === confirmPass) {
                matchText.textContent = '✓ Passwords match';
                matchText.className = 'form-text text-success';
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.className = 'form-text text-danger';
            }
        }

        // Event listeners
        document.getElementById('newPassword').addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });

        document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);

        // Form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            
            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</body>
</html>