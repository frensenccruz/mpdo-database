<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

// Get search parameters
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

// Display messages
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locational Clearances - MPDO</title>
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
                <h2><i class="fas fa-map-marker-alt me-2"></i>Locational Clearances</h2>
                <div>
                    <a href="export.php?<?= http_build_query($_GET) ?>" class="btn btn-success me-2">
                        <i class="fas fa-file-excel me-1"></i> Export
                    </a>
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Clearance
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
                                   placeholder="App. No., Applicant, Project..." 
                                   value="<?= htmlspecialchars($search_query) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Right over Land</label>
                            <input type="text" name="type" class="form-control" 
                                   placeholder="e.g., Owned, Leased..." 
                                   value="<?= htmlspecialchars($search_type) ?>">
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
                    Found <strong><?= count($clearances) ?></strong> clearance(s)
                </p>
            </div>

            <!-- Clearances Table -->
            <?php if (empty($clearances)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No clearances found</h5>
                        <p class="text-muted">Try adjusting your search criteria or create a new clearance.</p>
                        <a href="create.php" class="btn btn-primary mt-3">
                            <i class="fas fa-plus me-2"></i>Create New Clearance
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>App. No.</th>
                                <th>Applicant</th>
                                <th>Project Type</th>
                                <th>Location</th>
                                <th>Land Right</th>
                                <th>Submitted By</th>
                                <th>Date</th>
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clearances as $clearance): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary">
                                        <?= htmlspecialchars($clearance['application_no']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($clearance['applicant']) ?></strong>
                                    <?php if ($clearance['corporation_name']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($clearance['corporation_name']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($clearance['project_type']) ?></td>
                                <td><small><?= htmlspecialchars(substr($clearance['area_location'], 0, 50)) ?>...</small></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= htmlspecialchars($clearance['right_over_land']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($clearance['creator_name']) ?></td>
                                <td>
                                    <small>
                                        <?= date('M j, Y', strtotime($clearance['created_at'])) ?><br>
                                        <span class="text-muted"><?= date('g:i A', strtotime($clearance['created_at'])) ?></span>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($clearance['file_path']): ?>
                                        <button class="btn btn-sm btn-outline-primary mb-1" 
                                                onclick="viewPDF('../<?= htmlspecialchars($clearance['file_path']) ?>')">
                                            <i class="fas fa-file-pdf"></i> View
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="edit.php?id=<?= $clearance['id'] ?>" class="btn btn-sm btn-outline-warning mb-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    
                                    <?php if ($_SESSION['role'] === 'admin' || $clearance['created_by'] == $_SESSION['user_id']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-btn mb-1" 
                                                data-id="<?= $clearance['id'] ?>" 
                                                data-name="<?= htmlspecialchars($clearance['applicant']) ?>">
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
                    <h5 class="modal-title">Clearance Preview</h5>
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
        // View PDF in modal
        function viewPDF(url) {
            document.getElementById('pdfFrame').src = url;
            new bootstrap.Modal(document.getElementById('pdfModal')).show();
        }

        // Delete clearance
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                
                if (confirm(`Are you sure you want to delete clearance for "${name}"?`)) {
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');

                    fetch('../api/delete_clearance.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + (data.error || 'Failed to delete'));
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error);
                    });
                }
            });
        });
    </script>
</body>
</html>