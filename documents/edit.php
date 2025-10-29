<?php
require_once '../config.php';
require_once '../api/DocumentConfig.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['id'])) {
    die('Document ID required.');
}

$doc_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->execute([$doc_id]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    die('Document not found.');
}

// Decode metadata
$metadata = json_decode($doc['metadata'], true) ?? [];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $doc_type = $doc['doc_type'];
        
        // Extract and validate data
        $new_metadata = DocumentConfig::extractData($doc_type, $_POST);
        DocumentConfig::validate($doc_type, $new_metadata);
        
        // Handle file upload
        $file_path = $doc['file_path'];
        if (!empty($_FILES['document_file']['name'])) {
            $upload_dir = '../uploads/';
            $file_tmp = $_FILES['document_file']['tmp_name'];
            $file_size = $_FILES['document_file']['size'];
            $file_ext = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
            
            if ($file_ext !== 'pdf') {
                throw new Exception('Only PDF files are allowed.');
            }
            
            if ($file_size > 10 * 1024 * 1024) {
                throw new Exception('File too large. Max 10MB.');
            }
            
            // Delete old file
            if ($doc['file_path'] && file_exists('../' . $doc['file_path'])) {
                unlink('../' . $doc['file_path']);
            }
            
            $new_name = uniqid('doc_', true) . '_' . time() . '.pdf';
            $target = $upload_dir . $new_name;
            
            if (!move_uploaded_file($file_tmp, $target)) {
                throw new Exception('File upload failed.');
            }
            
            $file_path = 'uploads/' . $new_name;
        }
        
        // Get subject from metadata
        $subject = $new_metadata['subject'] ?? null;
        
        // Update database
        $stmt = $pdo->prepare("
            UPDATE documents 
            SET subject = ?, metadata = ?, file_path = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $subject,
            json_encode($new_metadata),
            $file_path,
            $doc_id
        ]);
        
        $message = 'Document updated successfully!';
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt->execute([$doc_id]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        $metadata = json_decode($doc['metadata'], true) ?? [];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Document - MPDO</title>
    <link href="https://unpkg.com/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <?php require_once '../includes/navbar.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>
    
    <main class="content-wrapper">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-edit me-2"></i>Edit Document</h2>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <!-- Document Type (Read-only) -->
                        <div class="mb-3">
                            <label class="form-label">Document Type</label>
                            <input type="text" class="form-control" 
                                   value="<?= htmlspecialchars($doc['doc_type']) ?>" 
                                   readonly>
                            <div class="form-text">Document type cannot be changed after creation</div>
                        </div>
                        
                        <!-- File Upload -->
                        <div class="mb-3">
                            <label class="form-label">Replace File (Optional)</label>
                            <input type="file" name="document_file" class="form-control" accept=".pdf">
                            <?php if ($doc['file_path']): ?>
                                <div class="form-text">
                                    Current: <a href="<?= BASE_PATH ?>/<?= htmlspecialchars($doc['file_path']) ?>" target="_blank">
                                        <i class="fas fa-file-pdf"></i> View Current PDF
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Dynamic Fields -->
                        <div id="dynamicFields"></div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Update Document
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://unpkg.com/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Document config and current data
        const docType = <?= json_encode($doc['doc_type']) ?>;
        const docConfig = <?= json_encode(DocumentConfig::getFieldConfig($doc['doc_type'])) ?>;
        const currentData = <?= json_encode($metadata) ?>;
        
        // Proper case conversion
        function toProperCase(str) {
            return str.replace(/\w\S*/g, txt => 
                txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()
            );
        }
        
        // Generate edit fields
        function generateFields() {
            const container = document.getElementById('dynamicFields');
            
            if (!docConfig) return;
            
            docConfig.fields.forEach(field => {
                const isRequired = docConfig.required.includes(field);
                const label = docConfig.labels[field] || field;
                const options = docConfig.options?.[field] || null;
                const value = currentData[field] || '';
                
                let fieldHTML = `
                    <div class="mb-3">
                        <label class="form-label">
                            ${label} ${isRequired ? '<span class="text-danger">*</span>' : ''}
                        </label>
                `;
                
                if (options) {
                    // Dropdown field
                    fieldHTML += `
                        <select name="${field}" class="form-select" ${isRequired ? 'required' : ''}>
                            <option value="">-- Select --</option>
                            ${options.map(opt => 
                                `<option value="${opt}" ${opt === value ? 'selected' : ''}>${opt}</option>`
                            ).join('')}
                        </select>
                    `;
                } else if (field === 'subject') {
                    // Textarea for subject
                    fieldHTML += `
                        <textarea name="${field}" class="form-control" rows="3" ${isRequired ? 'required' : ''}>${value}</textarea>
                    `;
                } else {
                    // Text input
                    fieldHTML += `
                        <input type="text" name="${field}" class="form-control" 
                               value="${value}" ${isRequired ? 'required' : ''}>
                    `;
                }
                
                fieldHTML += '</div>';
                container.innerHTML += fieldHTML;
            });
            
            // Add proper case conversion
            container.querySelectorAll('input[type="text"], textarea').forEach(input => {
                if (['name', 'subject', 'department'].includes(input.name)) {
                    input.addEventListener('blur', function() {
                        if (this.value.trim() !== '') {
                            this.value = toProperCase(this.value);
                        }
                    });
                }
            });
        }
        
        // Generate fields on page load
        document.addEventListener('DOMContentLoaded', generateFields);
    </script>
</body>
</html>