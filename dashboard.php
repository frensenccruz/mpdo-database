<?php
require_once 'config.php';

// Get statistics
$stats = [];
try {
    // Documents stats
    $stats['total_docs'] = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
    $stats['docs_this_month'] = $pdo->query("
        SELECT COUNT(*) FROM documents 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ")->fetchColumn();
    $stats['docs_this_week'] = $pdo->query("
        SELECT COUNT(*) FROM documents 
        WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURRENT_DATE(), 1)
    ")->fetchColumn();
    
    // Clearances stats
    $stats['total_clearances'] = $pdo->query("SELECT COUNT(*) FROM clearances")->fetchColumn();
    $stats['clearances_this_month'] = $pdo->query("
        SELECT COUNT(*) FROM clearances 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ")->fetchColumn();
    $stats['clearances_this_week'] = $pdo->query("
        SELECT COUNT(*) FROM clearances 
        WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURRENT_DATE(), 1)
    ")->fetchColumn();
    
    // Users
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
    
    // Recent documents
    $recent_docs = $pdo->query("
        SELECT d.*, u.fullname 
        FROM documents d 
        JOIN users u ON d.created_by = u.id 
        ORDER BY d.created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    // Recent clearances
    $recent_clearances = $pdo->query("
        SELECT c.*, u.fullname 
        FROM clearances c 
        JOIN users u ON c.created_by = u.id 
        ORDER BY c.created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    // Document type breakdown
    $doc_types = $pdo->query("
        SELECT doc_type, COUNT(*) as count 
        FROM documents 
        GROUP BY doc_type 
        ORDER BY count DESC
    ")->fetchAll();
    
    // Clearance project types
    $project_types = $pdo->query("
        SELECT project_type, COUNT(*) as count 
        FROM clearances 
        GROUP BY project_type 
        ORDER BY count DESC 
        LIMIT 5
    ")->fetchAll();
    
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPDO Database System - Dashboard</title>
    <link href="https://unpkg.com/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <?php require_once 'includes/navbar.php'; ?>
    <?php require_once 'includes/sidebar.php'; ?>
    
    <main class="content-wrapper">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                    <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['fullname']) ?>!</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">
                        <i class="far fa-clock me-1"></i>
                        <?= date('l, F j, Y - g:i A') ?>
                    </small>
                </div>
            </div>
            
            <!-- Documents Statistics -->
            <h5 class="mb-3"><i class="fas fa-file-alt me-2"></i>Documents</h5>
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?= number_format($stats['total_docs'] ?? 0) ?></h3>
                                <p>Total Documents</p>
                            </div>
                            <i class="fas fa-file-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?= number_format($stats['docs_this_month'] ?? 0) ?></h3>
                                <p>This Month</p>
                            </div>
                            <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card bg-warning text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?= number_format($stats['docs_this_week'] ?? 0) ?></h3>
                                <p>This Week</p>
                            </div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card bg-info text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?= number_format($stats['total_users'] ?? 0) ?></h3>
                                <p>Active Users</p>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Clearances Statistics -->
            <h5 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i>Locational Clearances</h5>
            <div class="row mb-4">
                <div class="col-md-4 col-sm-6">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?= number_format($stats['total_clearances'] ?? 0) ?></h3>
                                <p>Total Clearances</p>
                            </div>
                            <i class="fas fa-map-marker-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?= number_format($stats['clearances_this_month'] ?? 0) ?></h3>
                                <p>This Month</p>
                            </div>
                            <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?= number_format($stats['clearances_this_week'] ?? 0) ?></h3>
                                <p>This Week</p>
                            </div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Documents -->
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Documents</h5>
                                <a href="documents/index.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($recent_docs)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No documents yet
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Type</th>
                                                <th>Details</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_docs as $doc): ?>
                                                <?php $metadata = json_decode($doc['metadata'] ?? '{}', true); ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge doc-type-<?= strtolower(str_replace('%', '', $doc['doc_type'])) ?>">
                                                            <?= htmlspecialchars($doc['doc_type']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <?php 
                                                            $name = $metadata['name'] ?? $doc['subject'] ?? 'N/A';
                                                            echo htmlspecialchars(substr($name, 0, 40));
                                                            echo strlen($name) > 40 ? '...' : '';
                                                            ?>
                                                        </small>
                                                    </td>
                                                    <td><small><?= date('M j, g:i A', strtotime($doc['created_at'])) ?></small></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Clearances -->
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Recent Clearances</h5>
                                <a href="clearance/index.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($recent_clearances)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No clearances yet
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>App. No.</th>
                                                <th>Applicant</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_clearances as $clearance): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            <?= htmlspecialchars($clearance['application_no']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <?= htmlspecialchars(substr($clearance['applicant'], 0, 30)) ?>
                                                            <?= strlen($clearance['applicant']) > 30 ? '...' : '' ?>
                                                        </small>
                                                    </td>
                                                    <td><small><?= date('M j, g:i A', strtotime($clearance['created_at'])) ?></small></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Document Types Breakdown -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Document Types</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($doc_types)): ?>
                                <p class="text-center text-muted">No data available</p>
                            <?php else: ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($doc_types as $type): ?>
                                        <li class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span><?= htmlspecialchars($type['doc_type']) ?></span>
                                                <strong><?= $type['count'] ?></strong>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-primary" 
                                                     style="width: <?= ($type['count'] / $stats['total_docs']) * 100 ?>%">
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Project Types & Quick Actions -->
                <div class="col-lg-6">
                    <div class="card mb-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Top Project Types</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($project_types)): ?>
                                <p class="text-center text-muted">No data available</p>
                            <?php else: ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($project_types as $type): ?>
                                        <li class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span><?= htmlspecialchars($type['project_type']) ?></span>
                                                <strong><?= $type['count'] ?></strong>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" 
                                                     style="width: <?= ($type['count'] / $stats['total_clearances']) * 100 ?>%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="documents/create.php" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-plus me-2"></i>New Document
                            </a>
                            <a href="clearance/create.php" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>New Clearance
                            </a>
                            <a href="documents/index.php" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-search me-2"></i>Search Documents
                            </a>
                            <?php if (isAdmin()): ?>
                                <a href="reports/audit.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-history me-2"></i>View Audit Log
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://unpkg.com/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>