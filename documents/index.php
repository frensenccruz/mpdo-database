<?php
require_once '../config.php';
require_once '../api/DocumentConfig.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

// Get search parameters
$search_type = $_GET['type'] ?? '';
$search_query = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build SQL query
$sql = "SELECT d.*, u.fullname as creator_name 
        FROM documents d
        JOIN users u ON d.created_by = u.id 
        WHERE 1=1";
$params = [];

if ($search_type) {
    $sql .= " AND d.doc_type = ?";
    $params[] = $search_type;
}

if ($search_query) {
    // Search in subject column and JSON metadata
    $sql .= " AND (d.subject LIKE ? OR d.metadata LIKE ?)";
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

// Display messages
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Helper function to format document details from JSON metadata
function formatDocDetails($doc) {
    $details = [];
    
    // Decode JSON metadata
    $metadata = json_decode($doc['metadata'] ?? '{}', true);
    
    if (!$metadata || empty($metadata)) {
        return '<em class="text-muted">No details available</em>';
    }
    
    // Get config for this document type
    $config = DocumentConfig::getFieldConfig($doc['doc_type']);
    
    if ($config) {
        foreach ($config['fields'] as $field) {
            $value = $metadata[$field] ?? '';
            if (!empty($value)) {
                $label = $config['labels'][$field] ?? ucfirst($field);
                $displayValue = is_string($value) && strlen($value) > 80 
                    ? substr($value, 0, 80) . '...' 
                    : $value;
                $details[] = "<strong>$label:</strong> " . htmlspecialchars($displayValue);
            }
        }
    }
    
    return !empty($details) ? implode('<br>', $details) : '<em class="text-muted">No details</em>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents - MPDO</title>
    <link href="https://unpkg.com/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <?php require_once '../includes/navbar.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>
    
    <main class="content-wrapper">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h2><i class="fas fa-file-alt me-2"></i>Document Records</h2>
                <div>
                    <a href="export.php?<?= http_build_query($_GET) ?>" class="btn btn-success me-2">
                        <i class="fas fa-file-excel me-1"></i> Export
                    </a>
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Document
                    </a>
                </div>
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
            
            <!-- Search & Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Search any field..."
                                   value="<?= htmlspecialchars($search_query) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Document Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <?php foreach (DocumentConfig::getTypes() as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>" 
                                            <?= $search_type === $type ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control"
                                   value="<?= htmlspecialchars($date_from) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control"
                                   value="<?= htmlspecialchars($date_to) ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Results Count -->
            <div class="mb-3">
                <p class="text-muted">
                    Found <strong><?= count($documents) ?></strong> document(s)
                </p>
            </div>
            
            <!-- Documents Table -->
            <?php if (empty($documents)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">No documents found</h5>
                        <p class="text-muted">Try adjusting your search criteria or create a new document.</p>
                        <a href="create.php" class="btn btn-primary mt-3">
                            <i class="fas fa-plus me-2"></i>Create New Document
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Details</th>
                                <th>Submitted By</th>
                                <th>Date</th>
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td>
                                        <span class="badge doc-type-<?= strtolower(str_replace('%', '', $doc['doc_type'])) ?>">
                                            <?= htmlspecialchars($doc['doc_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= formatDocDetails($doc) ?></td>
                                    <td><?= htmlspecialchars($doc['creator_name']) ?></td>
                                    <td>
                                        <small>
                                            <?= date('M j, Y', strtotime($doc['created_at'])) ?><br>
                                            <span class="text-muted"><?= date('g:i A', strtotime($doc['created_at'])) ?></span>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($doc['file_path']): ?>
                                            <button class="btn btn-sm btn-outline-primary mb-1"
                                                    onclick="viewPDF('../<?= htmlspecialchars($doc['file_path']) ?>')">
                                                <i class="fas fa-file-pdf"></i> View
                                            </button>
                                        <?php endif; ?>
                                        
                                        <a href="edit.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-warning mb-1">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        
                                        <?php if ($_SESSION['role'] === 'admin' || $doc['created_by'] == $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn mb-1"
                                                    data-id="<?= $doc['id'] ?>"
                                                    data-name="<?= htmlspecialchars($doc['subject'] ?? 'this document') ?>">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- PDF Preview Modal -->
    <div class="modal fade" id="pdfModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Document Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="pdfFrame" style="width:100%; height:600px; border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewPDF(url) {
            document.getElementById('pdfFrame').src = url;
            new bootstrap.Modal(document.getElementById('pdfModal')).show();
        }
        
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const btn = this;
                
                if (confirm(`Are you sure you want to delete "${name}"?`)) {
                    // Disable button and show loading
                    btn.disabled = true;
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                    
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
                    
                    fetch('../api/delete_document.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // Get the text first to check if it's valid JSON
                        return response.text().then(text => {
                            try {
                                const data = JSON.parse(text);
                                return data;
                            } catch (e) {
                                console.error('Invalid JSON response:', text);
                                throw new Error('Server returned invalid response. Check console for details.');
                            }
                        });
                    })
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            btn.innerHTML = '<i class="fas fa-check"></i> Deleted!';
                            btn.classList.remove('btn-outline-danger');
                            btn.classList.add('btn-success');
                            
                            // Reload after short delay
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        } else {
                            throw new Error(data.error || 'Failed to delete document');
                        }
                    })
                    .catch(error => {
                        console.error('Delete error:', error);
                        alert('Error: ' + error.message);
                        
                        // Re-enable button
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    });
                }
            });
        });
    </script>
</body>
</html>