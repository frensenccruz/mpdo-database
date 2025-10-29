<?php
session_start();
require_once '../config.php';
require_once 'log_activity.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid request']));
}

// CSRF Protection
if (!validateCSRF($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit(json_encode(['error' => 'Invalid CSRF token']));
}

try {
    $id = (int)$_POST['id'];

    // Verify ownership or admin rights
    $stmt = $pdo->prepare("SELECT file_path, created_by, applicant FROM clearances WHERE id = ?");
    $stmt->execute([$id]);
    $clearance = $stmt->fetch();

    if (!$clearance) {
        http_response_code(404);
        exit(json_encode(['error' => 'Clearance not found']));
    }

    // Check permissions
    if ($clearance['created_by'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit(json_encode(['error' => 'Permission denied']));
    }

    // Delete file
    if ($clearance['file_path'] && file_exists('../' . $clearance['file_path'])) {
        unlink('../' . $clearance['file_path']);
    }

    // Delete record
    $stmt = $pdo->prepare("DELETE FROM clearances WHERE id = ?");
    $stmt->execute([$id]);

    logActivity('clearance_delete', json_encode([
        'clearance_id' => $id,
        'applicant' => $clearance['applicant']
    ]));

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Delete clearance error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete clearance']);
}
?>