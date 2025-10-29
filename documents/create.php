<?php
require_once '../config.php';
require_once '../api/DocumentConfig.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Document - MPDO</title>
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
                <h2><i class="fas fa-plus-circle me-2"></i>Submit New Document</h2>
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
                    <form id="documentForm" action="../api/save_document.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <!-- Document Type -->
                        <div class="mb-3">
                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                            <select name="doc_type" id="docType" class="form-select" required>
                                <option value="">-- Select Document Type --</option>
                                <?php foreach (DocumentConfig::getTypes() as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- File Upload -->
                        <div class="mb-3">
                            <label class="form-label">Attach File (PDF only) <span class="text-danger">*</span></label>
                            <input type="file" name="document_file" id="documentFile" class="form-control" accept=".pdf" required>
                            <div class="form-text">Only PDF files allowed. Maximum size: 10MB.</div>
                        </div>
                        
                        <!-- Dynamic Fields Container -->
                        <div id="dynamicFields"></div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Submit Document
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://unpkg.com/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Document type configurations (from PHP)
        const docConfigs = <?= json_encode(array_map(function($type) {
            return DocumentConfig::getFieldConfig($type);
        }, array_combine(DocumentConfig::getTypes(), DocumentConfig::getTypes()))) ?>;
        
        // Proper case conversion
        function toProperCase(str) {
            return str.replace(/\w\S*/g, txt => 
                txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()
            );
        }
        
        // Generate form fields dynamically
        function generateFields(docType) {
            const container = document.getElementById('dynamicFields');
            container.innerHTML = '';
            
            if (!docType || !docConfigs[docType]) return;
            
            const config = docConfigs[docType];
            
            config.fields.forEach(field => {
                const isRequired = config.required.includes(field);
                const label = config.labels[field] || field;
                const options = config.options?.[field] || null;
                
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
                            ${options.map(opt => `<option value="${opt}">${opt}</option>`).join('')}
                        </select>
                    `;
                } else if (field === 'subject') {
                    // Textarea for subject
                    fieldHTML += `
                        <textarea name="${field}" class="form-control" rows="3" ${isRequired ? 'required' : ''}></textarea>
                    `;
                } else {
                    // Text input
                    fieldHTML += `
                        <input type="text" name="${field}" class="form-control" ${isRequired ? 'required' : ''}>
                    `;
                }
                
                fieldHTML += '</div>';
                container.innerHTML += fieldHTML;
            });
            
            // Add proper case conversion to text inputs
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
        
        // Document type change handler
        document.getElementById('docType').addEventListener('change', function() {
            generateFields(this.value);
        });
        
        // Form validation
        document.getElementById('documentForm').addEventListener('submit', function(e) {
            const type = document.getElementById('docType').value;
            const file = document.getElementById('documentFile').files[0];
            
            if (!type) {
                e.preventDefault();
                alert('Please select a document type.');
                return false;
            }
            
            if (!file) {
                e.preventDefault();
                alert('Please attach a PDF file.');
                return false;
            }
        });
    </script>
</body>
</html>