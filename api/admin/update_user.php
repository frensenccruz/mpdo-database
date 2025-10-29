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
    $user_id = (int)$_POST['user_id'];
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (!$fullname || !$username) {
        throw new Exception('All fields are required');
    }
    
    if (!in_array($role, ['user', 'admin'])) {
        throw new Exception('Invalid role');
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        throw new Exception('Invalid status');
    }
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        throw new Exception('User not found');
    }
    
    // Check if username is taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetch()) {
        throw new Exception('Username already exists');
    }
    
    // Prevent demoting yourself from admin
    if ($user_id == $_SESSION['user_id'] && $role !== 'admin') {
        throw new Exception('You cannot remove your own admin privileges');
    }
    
    // Update user (no email)
    $stmt = $pdo->prepare("
        UPDATE users 
        SET fullname = ?, username = ?, role = ?, status = ?
        WHERE id = ?
    ");
    $stmt->execute([$fullname, $username, $role, $status, $user_id]);
    
    logActivity('user_update', json_encode([
        'user_id' => $user_id,
        'username' => $username,
        'role' => $role,
        'status' => $status
    ]));
    
    $_SESSION['message'] = "User '$fullname' updated successfully!";
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../../admin/users.php');
exit();
?>