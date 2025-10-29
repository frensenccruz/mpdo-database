<?php
session_start();
require_once '../config.php';
require_once 'log_activity.php';
require_once 'DocumentConfig.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// CSRF Protection
if (!validateCSRF($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

try {
    $doc_type = trim($_POST['doc_type'] ?? '');
    
    if (!$doc_type) {
        throw new Exception('Document type is required.');
    }
    
    // Validate document type
    if (!in_array($doc_type, DocumentConfig::getTypes())) {
        throw new Exception('Invalid document type.');
    }
    
    // Extract and validate data based on document type
    $metadata = DocumentConfig::extractData($doc_type, $_POST);
    DocumentConfig::validate($doc_type, $metadata);
    
    // Handle file upload
    $file_path = null;
    if (!empty($_FILES['document_file']['name'])) {
        $upload_dir = '../uploads/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = basename($_FILES['document_file']['name']);
        $file_tmp = $_FILES['document_file']['tmp_name'];
        $file_size = $_FILES['document_file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file
        if ($file_ext !== 'pdf') {
            throw new Exception('Only PDF files are allowed.');
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);
        
        if ($detected_type !== 'application/pdf') {
            throw new Exception('Invalid file type detected. Only PDF allowed.');
        }
        
        if ($file_size > 10 * 1024 * 1024) {
            throw new Exception('File too large. Maximum size is 10MB.');
        }
        
        $new_name = uniqid('doc_', true) . '_' . time() . '.pdf';
        $target = $upload_dir . $new_name;
        
        if (!move_uploaded_file($file_tmp, $target)) {
            throw new Exception('File upload failed.');
        }
        
        $file_path = 'uploads/' . $new_name;
    } else {
        throw new Exception('File upload is required.');
    }
    
    // Get subject from metadata
    $subject = $metadata['subject'] ?? null;
    
    // Insert into database with JSON metadata
    $stmt = $pdo->prepare("
        INSERT INTO documents (doc_type, subject, metadata, file_path, created_by)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $doc_type,
        $subject,
        json_encode($metadata),
        $file_path,
        $_SESSION['user_id']
    ]);
    
    $document_id = $pdo->lastInsertId();
    
    logActivity('document_upload', json_encode([
        'document_id' => $document_id,
        'type' => $doc_type,
        'file' => $file_path
    ]));
    
    $_SESSION['message'] = 'Document submitted successfully!';
    header('Location: ../documents/index.php');
    exit();
    
} catch (Exception $e) {
    // Clean up uploaded file on error
    if (isset($target) && file_exists($target)) {
        unlink($target);
    }
    
    $_SESSION['error'] = $e->getMessage();
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../documents/create.php'));
    exit();
}
?>