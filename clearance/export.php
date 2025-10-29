<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

// Get same filters as index.php
$search_query = $_GET['search'] ?? '';
$search_type = $_GET['type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build SQL query
$sql = "SELECT c.*, u.fullname as creator_name FROM clearances c 
        JOIN users u ON c.created_by = u.id WHERE 1=1";
$params = [];

if ($search_query) {
    $sql .= " AND (c.application_no LIKE ? OR c.applicant LIKE ? OR c.project_type LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($search_type) {
    $sql .= " AND c.right_over_land = ?";
    $params[] = $search_type;
}

if ($date_from) {
    $sql .= " AND DATE(c.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $sql .= " AND DATE(c.created_at) <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clearances = $stmt->fetchAll();

// Set headers for Excel download
$filename = "Locational_Clearances_" . date('Y-m-d_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write headers
fputcsv($output, [
    'ID',
    'Application No.',
    'Applicant',
    'Address',
    'Corporation Name',
    'Corporation Address',
    'Project Type',
    'Area & Location',
    'Right over Land',
    'Submitted By',
    'Date Submitted'
]);

// Write data
foreach ($clearances as $clearance) {
    fputcsv($output, [
        $clearance['id'],
        $clearance['application_no'],
        $clearance['applicant'],
        $clearance['address'],
        $clearance['corporation_name'],
        $clearance['corporation_address'],
        $clearance['project_type'],
        $clearance['area_location'],
        $clearance['right_over_land'],
        $clearance['creator_name'],
        date('Y-m-d H:i:s', strtotime($clearance['created_at']))
    ]);
}

fclose($output);
exit();
?>