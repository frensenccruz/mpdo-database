<?php
session_start();
require_once '../../config.php';
require_once '../log_activity.php';

// Check admin access
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = 'Unauthorized access';
    header('Location: ../../dashboard.php');
    exit();
}

// CSRF Protection
if (!validateCSRF($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid CSRF token';
    header('Location: ../../admin/users.php');
    exit();
}

try {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (!$fullname || !$username || !$password) {
        throw new Exception('All fields are required');
    }
    
    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters');
    }
    
    if (!in_array($role, ['user', 'admin'])) {
        throw new Exception('Invalid role');
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        throw new Exception('Invalid status');
    }
    
    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        throw new Exception('Username already exists');
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user (no email column)
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, fullname, role, status, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$username, $hashed_password, $fullname, $role, $status]);
    
    $user_id = $pdo->lastInsertId();
    
    logActivity('user_create', json_encode([
        'user_id' => $user_id,
        'username' => $username,
        'role' => $role
    ]));
    
    $_SESSION['message'] = "User '$fullname' created successfully!";
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../../admin/users.php');
exit();
?>