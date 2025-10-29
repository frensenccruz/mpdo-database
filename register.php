<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit();
}

if (!isAdmin()) {
    die('<div class="alert alert-danger m-3">Access denied. Admins only.</div>');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username']);
        $fullname = trim($_POST['fullname']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        $status = $_POST['status'];

        if (empty($username) || empty($fullname) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Username already exists.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, fullname, password, role, status) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $fullname, $hashed_password, $role, $status]);
                    $message = 'User registered successfully!';
                }
            } catch (Exception $e) {
                error_log("Register error: " . $e->getMessage());
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User - MPDO</title>
    <link href="https://unpkg.com/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <?php require_once 'includes/navbar.php'; ?>
    <?php require_once 'includes/sidebar.php'; ?>

    <main class="content-wrapper">
        <div class="container-fluid">
            <h2><i class="fas fa-user-plus me-2"></i>Register New User</h2>
            <p class="text-muted">Create new user accounts for the system</p>

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
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">User Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                                <div class="mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="fullname" class="form-control" required>
                                    <div class="form-text">Enter the user's complete name</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control" required>
                                    <div class="form-text">Used for login - must be unique</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" 
                                               name="password" 
                                               id="password"
                                               class="form-control" 
                                               required 
                                               minlength="6">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                onclick="togglePassword()">
                                            <i class="fas fa-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Minimum 6 characters</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <select name="role" class="form-select" required>
                                        <option value="staff" selected>Staff</option>
                                        <option value="admin">Admin</option>
                                        <option value="viewer">Viewer</option>
                                    </select>
                                    <div class="form-text">
                                        <strong>Admin:</strong> Full access | 
                                        <strong>Staff:</strong> Create/edit own documents | 
                                        <strong>Viewer:</strong> View only
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="active" selected>Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <div class="form-text">Inactive users cannot log in</div>
                                </div>

                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Register User
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>User Roles</h5>
                        </div>
                        <div class="card-body">
                            <h6><i class="fas fa-user-shield text-danger"></i> Admin</h6>
                            <ul class="small mb-3">
                                <li>Full system access</li>
                                <li>Create/edit/delete all documents</li>
                                <li>Register new users</li>
                                <li>View audit logs</li>
                                <li>Export data</li>
                            </ul>

                            <h6><i class="fas fa-user-edit text-primary"></i> Staff</h6>
                            <ul class="small mb-3">
                                <li>Create documents</li>
                                <li>Edit/delete own documents</li>
                                <li>View all documents</li>
                                <li>Export data</li>
                            </ul>

                            <h6><i class="fas fa-user text-secondary"></i> Viewer</h6>
                            <ul class="small mb-0">
                                <li>View documents only</li>
                                <li>Search documents</li>
                                <li>No create/edit/delete access</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Proper case for fullname
        function toProperCase(str) {
            return str.replace(/\w\S*/g, function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            });
        }

        document.querySelector('input[name="fullname"]').addEventListener('blur', function() {
            if (this.value.trim() !== '') {
                this.value = toProperCase(this.value);
            }
        });

        // Toggle password visibility
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
