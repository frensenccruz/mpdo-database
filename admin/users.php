<?php
require_once '../config.php';
require_once '../api/log_activity.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../dashboard.php');
    exit();
}

// Handle messages
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Get all users (removed email since it doesn't exist)
$stmt = $pdo->query("
    SELECT id, username, fullname, role, status, created_at, last_login 
    FROM users 
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - MPDO</title>
    <link href="https://unpkg.com/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        .status-badge {
            padding: 0.35em 0.65em;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        .status-active { background: #10b981; color: white; }
        .status-inactive { background: #6b7280; color: white; }
        .role-badge {
            padding: 0.35em 0.65em;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        .role-admin { background: #ef4444; color: white; }
        .role-user { background: #3b82f6; color: white; }
    </style>
</head>
<body class="dashboard-body">
    <?php require_once '../includes/navbar.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>
    
    <main class="content-wrapper">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-users-cog me-2"></i>User Management</h2>
                    <p class="text-muted mb-0">Manage system users and permissions</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-2"></i>Add New User
                </button>
            </div>
            
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
            
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Total Users</p>
                                    <h3><?= count($users) ?></h3>
                                </div>
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Active Users</p>
                                    <h3><?= count(array_filter($users, fn($u) => $u['status'] === 'active')) ?></h3>
                                </div>
                                <i class="fas fa-user-check fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Administrators</p>
                                    <h3><?= count(array_filter($users, fn($u) => $u['role'] === 'admin')) ?></h3>
                                </div>
                                <i class="fas fa-user-shield fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Inactive Users</p>
                                    <h3><?= count(array_filter($users, fn($u) => $u['status'] === 'inactive')) ?></h3>
                                </div>
                                <i class="fas fa-user-times fa-2x text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Users</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Last Login</th>
                                    <th style="width: 250px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-2">
                                                    <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($user['fullname']) ?></strong>
                                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                        <span class="badge bg-info ms-1">You</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td>
                                            <span class="role-badge role-<?= $user['role'] ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $user['status'] ?>">
                                                <?= ucfirst($user['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= date('M j, Y', strtotime($user['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <small>
                                                <?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?>
                                            </small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning edit-user-btn" 
                                                    data-user='<?= json_encode($user) ?>'>
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary change-password-btn" 
                                                    data-id="<?= $user['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($user['fullname']) ?>">
                                                <i class="fas fa-key"></i> Password
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button class="btn btn-sm btn-outline-danger delete-user-btn" 
                                                        data-id="<?= $user['id'] ?>" 
                                                        data-name="<?= htmlspecialchars($user['fullname']) ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../api/admin/add_user.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" minlength="8" required>
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="user">User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../api/admin/update_user.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" id="edit_fullname" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <option value="user">User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i>Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../api/admin/change_password.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="user_id" id="password_user_id">
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Changing password for: <strong id="password_user_name"></strong>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" minlength="8" required>
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit user
        document.querySelectorAll('.edit-user-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const user = JSON.parse(this.dataset.user);
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_fullname').value = user.fullname;
                document.getElementById('edit_username').value = user.username;
                document.getElementById('edit_role').value = user.role;
                document.getElementById('edit_status').value = user.status;
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            });
        });
        
        // Change password
        document.querySelectorAll('.change-password-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('password_user_id').value = this.dataset.id;
                document.getElementById('password_user_name').textContent = this.dataset.name;
                new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
            });
        });
        
        // Delete user
        document.querySelectorAll('.delete-user-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                
                if (confirm(`Are you sure you want to delete user "${name}"?\n\nThis action cannot be undone!`)) {
                    const formData = new FormData();
                    formData.append('user_id', id);
                    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
                    
                    fetch('../api/admin/delete_user.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(text => {
                        try {
                            const data = JSON.parse(text);
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + data.error);
                            }
                        } catch(e) {
                            console.error('Response:', text);
                            alert('Error deleting user');
                        }
                    })
                    .catch(error => {
                        alert('Error deleting user');
                        console.error(error);
                    });
                }
            });
        });
    </script>
</body>
</html>