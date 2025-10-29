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
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (!$new_password || !$confirm_password) {
        throw new Exception('All fields are required');
    }
    
    if ($new_password !== $confirm_password) {
        throw new Exception('Passwords do not match');
    }
    
    if (strlen($new_password) < 8) {
        throw new Exception('Password must be at least 8 characters');
    }
    
    // Get user info
    $stmt = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Hash and update password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $user_id]);
    
    logActivity('user_password_change', json_encode([
        'user_id' => $user_id,
        'changed_by' => 'admin'
    ]));
    
    $_SESSION['message'] = "Password changed successfully for '{$user['fullname']}'!";
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../../admin/users.php');
exit();
?>