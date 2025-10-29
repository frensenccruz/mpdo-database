<?php
function logActivity($action, $details = null) {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) return;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    try {
        // If details is null, use empty string
        $details_str = $details !== null ? $details : '';
        
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, details, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $action, $details_str, $ip]);
    } catch (Exception $e) {
        error_log("Audit log failed: " . $e->getMessage());
    }
}
?>