<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

if (!isAdmin()) {
    die('<div class="alert alert-danger m-3">Access denied. Admins only.</div>');
}

// Get logs
try {
    $stmt = $pdo->query("
        SELECT a.*, u.username, u.fullname 
        FROM audit_logs a
        JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 100
    ");
    $logs = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Audit log error: " . $e->getMessage());
    $logs = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - MPDO</title>
    <link href="https://unpkg.com/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <?php require_once '../includes/navbar.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>

    <main class="content-wrapper">
        <div class="container-fluid">
            <h2><i class="fas fa-history me-2"></i>Audit Log</h2>
            <p class="text-muted">Track all user activities and system events (Last 100 entries)</p>

            <?php if (empty($logs)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No audit logs yet</h5>
                        <p class="text-muted">Activity logs will appear here as users interact with the system.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                        <th>IP Address</th>
                                        <th style="width: 180px;">Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $index => $log): ?>
                                    <tr>
                                        <td class="text-muted"><?= $index + 1 ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-2" style="width: 32px; height: 32px; font-size: 12px;">
                                                    <?= strtoupper(substr($log['fullname'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($log['fullname']) ?></div>
                                                    <small class="text-muted">@<?= htmlspecialchars($log['username']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $action_icons = [
                                                'user_login' => '<i class="fas fa-sign-in-alt text-success"></i> User Login',
                                                'password_change' => '<i class="fas fa-key text-warning"></i> Password Changed',
                                                'document_upload' => '<i class="fas fa-upload text-primary"></i> Document Uploaded',
                                                'document_delete' => '<i class="fas fa-trash text-danger"></i> Document Deleted'
                                            ];
                                            echo $action_icons[$log['action']] ?? '<i class="fas fa-circle"></i> ' . htmlspecialchars($log['action']);
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($log['details'] === null || $log['details'] === '') {
                                                echo '<span class="text-muted">—</span>';
                                            } else {
                                                $details = json_decode($log['details'], true);
                                                if (is_array($details)) {
                                                    echo '<small>';
                                                    foreach ($details as $key => $value) {
                                                        echo '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '<br>';
                                                    }
                                                    echo '</small>';
                                                } else {
                                                    echo '<small>' . htmlspecialchars($log['details']) . '</small>';
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($log['ip_address']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div><?= date('M j, Y', strtotime($log['created_at'])) ?></div>
                                            <small class="text-muted"><?= date('g:i:s A', strtotime($log['created_at'])) ?></small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Showing the last 100 activities. Older logs are archived in the database.
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://unpkg.com/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>