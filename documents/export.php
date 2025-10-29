<?php
require_once '../config.php';
require_once '../api/DocumentConfig.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

// Get same filters as index.php
$search_type = $_GET['type'] ?? '';
$search_query = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build SQL query (same as index.php)
$sql = "SELECT d.*, u.fullname as creator_name FROM documents d
        JOIN users u ON d.created_by = u.id WHERE 1=1";
$params = [];

if ($search_type) {
    $sql .= " AND d.doc_type = ?";
    $params[] = $search_type;
}

if ($search_query) {
    $sql .= " AND (d.subject LIKE ? OR JSON_SEARCH(d.metadata, 'one', ?, NULL, '$.*') IS NOT NULL)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($date_from) {
    $sql .= " AND DATE(d.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $sql .= " AND DATE(d.created_at) <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll();

// Set headers for CSV download
$filename = "MPDO_Documents_" . date('Y-m-d_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write headers
fputcsv($output, [
    'ID',
    'Document Type',
    'Name',
    'Subject',
    'Category/Type',
    'Number',
    'Department',
    'Direction',
    'Submitted By',
    'Date Submitted'
]);

// Write data
foreach ($documents as $doc) {
    // Decode metadata
    $metadata = json_decode($doc['metadata'], true) ?? [];
    
    fputcsv($output, [
        $doc['id'],
        $doc['doc_type'],
        $metadata['name'] ?? '',
        $metadata['subject'] ?? $doc['subject'] ?? '',
        $metadata['category'] ?? '',
        $metadata['number'] ?? '',
        $metadata['department'] ?? '',
        $metadata['direction'] ?? '',
        $doc['creator_name'],
        date('Y-m-d H:i:s', strtotime($doc['created_at']))
    ]);
}

fclose($output);
exit();