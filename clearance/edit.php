<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['id'])) {
    die('Clearance ID required.');
}

$clearance_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM clearances WHERE id = ?");
$stmt->execute([$clearance_id]);
$clearance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$clearance) {
    die('Clearance not found.');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        try {
            $application_no = trim($_POST['application_no'] ?? '');
            $applicant = trim($_POST['applicant'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $corporation_name = trim($_POST['corporation_name'] ?? '');
            $corporation_address = trim($_POST['corporation_address'] ?? '');
            $project_type = trim($_POST['project_type'] ?? '');
            $area_location = trim($_POST['area_location'] ?? '');
            $right_over_land = trim($_POST['right_over_land'] ?? '');

            if (!$application_no || !$applicant || !$address || !$project_type || !$area_location || !$right_over_land) {
                throw new Exception('All required fields must be filled.');
            }

            // Handle file upload
            $file_path = $clearance['file_path'];
            if (!empty($_FILES['clearance_file']['name'])) {
                $upload_dir = '../uploads/clearances/';
                $file_name = basename($_FILES['clearance_file']['name']);
                $file_tmp = $_FILES['clearance_file']['tmp_name'];
                $file_size = $_FILES['clearance_file']['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if ($file_ext !== 'pdf') {
                    throw new Exception('Only PDF files are allowed.');
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detected_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);

                if ($detected_type !== 'application/pdf') {
                    throw new Exception('Invalid file type detected.');
                }

                if ($file_size > 10 * 1024 * 1024) {
                    throw new Exception('File too large. Max 10MB.');
                }

                // Delete old file
                if ($clearance['file_path'] && file_exists('../' . $clearance['file_path'])) {
                    unlink('../' . $clearance['file_path']);
                }

                $new_name = uniqid('clearance_', true) . '_' . time() . '.pdf';
                $target = $upload_dir . $new_name;

                if (!move_uploaded_file($file_tmp, $target)) {
                    throw new Exception('File upload failed.');
                }

                $file_path = 'uploads/clearances/' . $new_name;
            }

            // Update database
            $stmt = $pdo->prepare("
                UPDATE clearances 
                SET application_no = ?, applicant = ?, address = ?, 
                    corporation_name = ?, corporation_address = ?,
                    project_type = ?, area_location = ?, right_over_land = ?, file_path = ?
                WHERE id = ?
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
                $clearance_id
            ]);

            $message = 'Clearance updated successfully!';
            
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM clearances WHERE id = ?");
            $stmt->execute([$clearance_id]);
            $clearance = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Clearance - MPDO</title>
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
                <h2><i class="fas fa-edit me-2"></i>Edit Locational Clearance</h2>
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

                        <h5 class="mb-3">Applicant Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Application No. <span class="text-danger">*</span></label>
                                <input type="text" name="application_no" class="form-control" 
                                       value="<?= htmlspecialchars($clearance['application_no']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Applicant <span class="text-danger">*</span></label>
                                <input type="text" name="applicant" class="form-control" 
                                       value="<?= htmlspecialchars($clearance['applicant']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" name="address" class="form-control" 
                                   value="<?= htmlspecialchars($clearance['address']) ?>" required>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Corporation Information (Optional)</h5>

                        <div class="mb-3">
                            <label class="form-label">Name of Corporation</label>
                            <input type="text" name="corporation_name" class="form-control" 
                                   value="<?= htmlspecialchars($clearance['corporation_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Corporation Address</label>
                            <input type="text" name="corporation_address" class="form-control" 
                                   value="<?= htmlspecialchars($clearance['corporation_address']) ?>">
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Project Details</h5>

                        <div class="mb-3">
                            <label class="form-label">Type of Project <span class="text-danger">*</span></label>
                            <input type="text" name="project_type" class="form-control" 
                                   value="<?= htmlspecialchars($clearance['project_type']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Area and Location <span class="text-danger">*</span></label>
                            <textarea name="area_location" class="form-control" rows="2" required><?= htmlspecialchars($clearance['area_location']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Right over Land <span class="text-danger">*</span></label>
                            <input type="text" name="right_over_land" class="form-control" 
                                   value="<?= htmlspecialchars($clearance['right_over_land']) ?>" required>
                            <div class="form-text">e.g., Owned, Leased, Rented, Government, etc.</div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Attachment</h5>

                        <div class="mb-3">
                            <label class="form-label">Attach File (PDF only) <span class="text-muted">(Optional - leave empty to keep current file)</span></label>
                            <input type="file" name="clearance_file" class="form-control" accept=".pdf">
                            <?php if ($clearance['file_path']): ?>
                                <div class="form-text">
                                    Current: <a href="../<?= htmlspecialchars($clearance['file_path']) ?>" target="_blank">View PDF</a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>Update Clearance
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Proper case conversion
        function toProperCase(str) {
            return str.replace(/\w\S*/g, function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            });
        }

        // Apply proper case on blur for ALL text input fields
        document.addEventListener('focusout', function(e) {
            const target = e.target;
            
            // Apply to all text inputs and textareas (except application_no)
            if ((target.type === 'text' || target.tagName === 'TEXTAREA') && 
                target.name !== 'application_no' && 
                target.value.trim() !== '') {
                target.value = toProperCase(target.value);
            }
        });
    </script>
</body>
</html>