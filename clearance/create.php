<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

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
    <title>New Locational Clearance - MPDO</title>
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
                <h2><i class="fas fa-map-marker-alt me-2"></i>Submit New Locational Clearance</h2>
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
                    <form action="../api/save_clearance.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <h5 class="mb-3">Applicant Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Application No. <span class="text-danger">*</span></label>
                                <input type="text" name="application_no" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Applicant <span class="text-danger">*</span></label>
                                <input type="text" name="applicant" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" name="address" class="form-control" required>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Corporation Information (Optional)</h5>

                        <div class="mb-3">
                            <label class="form-label">Name of Corporation</label>
                            <input type="text" name="corporation_name" class="form-control">
                            <div class="form-text">Leave blank if not applicable</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Corporation Address</label>
                            <input type="text" name="corporation_address" class="form-control">
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Project Details</h5>

                        <div class="mb-3">
                            <label class="form-label">Type of Project <span class="text-danger">*</span></label>
                            <input type="text" name="project_type" class="form-control" required>
                            <div class="form-text">e.g., Residential, Commercial, Industrial</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Area and Location <span class="text-danger">*</span></label>
                            <textarea name="area_location" class="form-control" rows="2" required></textarea>
                            <div class="form-text">Specify the area size and exact location</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Right over Land <span class="text-danger">*</span></label>
                            <input type="text" name="right_over_land" class="form-control" required>
                            <div class="form-text">e.g., Owned, Leased, Rented, Government, etc.</div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Attachment</h5>

                        <div class="mb-3">
                            <label class="form-label">Attach File (PDF only) <span class="text-danger">*</span></label>
                            <input type="file" name="clearance_file" class="form-control" accept=".pdf" required>
                            <div class="form-text">Only PDF files allowed. Maximum size: 10MB.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Submit Clearance
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