<?php
// Prevent any output before JSON
ob_start();

session_start();
require_once '../config.php';
require_once 'log_activity.php';

// Clear any previous output
ob_clean();

// Set JSON header FIRST
header('Content-Type: application/json; charset=utf-8');

// Disable error display (log errors instead)
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Check authentication
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        throw new Exception('Unauthorized', 403);
    }
    
    // Check request method and ID
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
        throw new Exception('Invalid request', 400);
    }
    
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        throw new Exception('Invalid CSRF token', 403);
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token', 403);
    }
    
    $id = (int)$_POST['id'];
    
    if ($id <= 0) {
        throw new Exception('Invalid document ID', 400);
    }
    
    // Get document info
    $stmt = $pdo->prepare("SELECT file_path, created_by, subject, metadata FROM documents WHERE id = ?");
    $stmt->execute([$id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doc) {
        throw new Exception('Document not found', 404);
    }
    
    // Check permissions
    $isOwner = ($doc['created_by'] == $_SESSION['user_id']);
    $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
    
    if (!$isOwner && !$isAdmin) {
        throw new Exception('Permission denied', 403);
    }
    
    // Delete file if exists
    if (!empty($doc['file_path'])) {
        $fullPath = '../' . $doc['file_path'];
        if (file_exists($fullPath)) {
            @unlink($fullPath); // Use @ to suppress warnings
        }
    }
    
    // Delete database record
    $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if (!$result) {
        throw new Exception('Failed to delete document from database', 500);
    }
    
    // Get document name for logging
    $metadata = json_decode($doc['metadata'] ?? '{}', true);
    $doc_name = $metadata['name'] ?? $doc['subject'] ?? 'Document #' . $id;
    
    // Log activity
    logActivity('document_delete', json_encode([
        'document_id' => $id,
        'name' => $doc_name
    ]));
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Document deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Delete document error: " . $e->getMessage());
    
    // Get HTTP status code from exception
    $statusCode = (int)$e->getCode();
    if ($statusCode < 400 || $statusCode >= 600) {
        $statusCode = 500;
    }
    
    http_response_code($statusCode);
    
    // Error response
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Flush output buffer
ob_end_flush();
?>