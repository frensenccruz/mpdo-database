<?php
session_start();
require_once '../config.php';
require_once 'log_activity.php';

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
    // Get form data
    $application_no = trim($_POST['application_no'] ?? '');
    $applicant = trim($_POST['applicant'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $corporation_name = trim($_POST['corporation_name'] ?? '');
    $corporation_address = trim($_POST['corporation_address'] ?? '');
    $project_type = trim($_POST['project_type'] ?? '');
    $area_location = trim($_POST['area_location'] ?? '');
    $right_over_land = trim($_POST['right_over_land'] ?? '');
    $created_by = (int)$_SESSION['user_id'];

    // Validate required fields
    if (!$application_no || !$applicant || !$address || !$project_type || !$area_location || !$right_over_land) {
        throw new Exception('All required fields must be filled.');
    }

    $file_path = null;
    if (!empty($_FILES['clearance_file']['name'])) {
        $upload_dir = '../uploads/clearances/';
        
        // Create directory if doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = basename($_FILES['clearance_file']['name']);
        $file_tmp = $_FILES['clearance_file']['tmp_name'];
        $file_size = $_FILES['clearance_file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file extension
        if ($file_ext !== 'pdf') {
            throw new Exception('Only PDF files are allowed.');
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if ($detected_type !== 'application/pdf') {
            throw new Exception('Invalid file type detected. Only PDF allowed.');
        }

        // Validate file size
        if ($file_size > 10 * 1024 * 1024) {
            throw new Exception('File too large. Maximum size is 10MB.');
        }

        // Generate unique filename
        $new_name = uniqid('clearance_', true) . '_' . time() . '.pdf';
        $target = $upload_dir . $new_name;

        if (!move_uploaded_file($file_tmp, $target)) {
            throw new Exception('File upload failed.');
        }

        $file_path = 'uploads/clearances/' . $new_name;
    } else {
        throw new Exception('File upload is required.');
    }

    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO clearances (
            application_no, applicant, address, corporation_name, corporation_address,
            project_type, area_location, right_over_land, file_path, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $application_no,
        $applicant,
        $address,
        $corporation_name ?: null,
        $corporation_address ?: null,
        $project_type,
        $area_location,
        $right_over_land,
        $file_path,
        $created_by
    ]);

    $clearance_id = $pdo->lastInsertId();

    logActivity('clearance_create', json_encode([
        'clearance_id' => $clearance_id,
        'application_no' => $application_no,
        'applicant' => $applicant
    ]));

    $_SESSION['message'] = 'Locational clearance submitted successfully!';
    header('Location: ../clearance/index.php');
    exit();

} catch (Exception $e) {
    // Clean up uploaded file on error
    if (isset($target) && file_exists($target)) {
        unlink($target);
    }
    
    $_SESSION['error'] = $e->getMessage();
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../clearance/create.php'));
    exit();
}
?>