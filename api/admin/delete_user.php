<?php
ob_start();
session_start();
require_once '../../config.php';
require_once '../log_activity.php';

ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    // Check admin access
    if (!isLoggedIn() || !isAdmin()) {
        throw new Exception('Unauthorized access', 403);
    }
    
    // CSRF Protection
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token', 403);
    }
    
    $user_id = (int)$_POST['user_id'];
    
    // Prevent deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        throw new Exception('You cannot delete your own account', 400);
    }
    
    // Get user info
    $stmt = $pdo->prepare("SELECT username, fullname FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User not found', 404);
    }
    
    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    logActivity('user_delete', json_encode([
        'user_id' => $user_id,
        'username' => $user['username'],
        'fullname' => $user['fullname']
    ]));
    
    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully'
    ]);
    
} catch (Exception $e) {
    $statusCode = (int)$e->getCode();
    if ($statusCode < 400 || $statusCode >= 600) {
        $statusCode = 500;
    }
    
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

ob_end_flush();
?>