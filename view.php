<?php
// view.php

require_once 'app/controllers/NikahController.php';
require_once 'app/helpers/session.php';

// Route guards
require_login();

$controller = new NikahController();

// Check for ID parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    flash('error', 'No certificate ID provided.');
    header("Location: dashboard.php");
    exit;
}

$id = sanitize($_GET['id']);

// Handle deletion request (POST to prevent CSRF / accidental deletes)
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Validate CSRF
    if (isset($_POST['csrf_token']) && validate_csrf($_POST['csrf_token'])) {
        $controller->handleDelete($id);
    } else {
        flash('error', 'CSRF verification failed.');
    }
}

$cert = $controller->show($id); // Redirects to dashboard if not found
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Certificate - Nikahnama Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Firebase SDK Integration -->
    <script type="module" src="assets/js/firebase-init.js"></script>
</head>
<body class="bg-light">

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fa-solid fa-mosque me-2"></i>NIKAH<span>NAMA</span>
            </a>
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create.php"><i class="fa-solid fa-circle-plus me-1"></i> Create Nikah</a>
                    </li>
                    <li class="nav-item me-3">
                        <span class="navbar-text text-light opacity-75 ms-2">
                            <i class="fa-solid fa-user me-1 text-warning"></i> <?php echo sanitize($_SESSION['fullname']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-sm btn-outline-danger">
                            <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main details panel -->
    <div class="container my-5">
        
        <!-- Alerts -->
        <?php if (has_flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?php echo flash('success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">
                    <i class="fa-solid fa-file-invoice me-2 text-warning"></i>Marriage Certificate Overview
                </h3>
                <p class="text-muted small mb-0">Record ID: #<?php echo $cert['id']; ?> | Created on: <?php echo date('F d, Y H:i A', strtotime($cert['created_at'])); ?></p>
            </div>
            
            <div class="d-flex flex-wrap gap-2">
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
                </a>
                <a href="edit.php?id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-outline-warning">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                </a>
                <a href="print.php?id=<?php echo $cert['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-print me-1"></i> Print A4
                </a>
                <a href="pdf.php?id=<?php echo $cert['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success">
                    <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fa-solid fa-trash me-1"></i> Delete
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            <!-- Details Grid -->
            <div class="col-lg-8">
                
                <!-- Groom & Bride Side-by-Side -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-dark text-white py-3">
                                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-user-tie text-warning me-2"></i>Groom Profile</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless small mb-0">
                                    <tr>
                                        <td class="fw-bold text-muted w-35">Name:</td>
                                        <td class="fw-semibold text-dark"><?php echo sanitize($cert['groom_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Phone:</td>
                                        <td><?php echo sanitize($cert['groom_phone']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Father:</td>
                                        <td><?php echo sanitize($cert['groom_father']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Mother:</td>
                                        <td><?php echo sanitize($cert['groom_mother']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Date of Birth:</td>
                                        <td><?php echo sanitize($cert['groom_birth']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">NID:</td>
                                        <td><?php echo sanitize($cert['groom_nid'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Passport:</td>
                                        <td><?php echo sanitize($cert['groom_passport'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Address:</td>
                                        <td class="text-wrap"><?php echo nl2br(sanitize($cert['groom_address'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-dark text-white py-3">
                                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-user-dress text-warning me-2"></i>Bride Profile</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless small mb-0">
                                    <tr>
                                        <td class="fw-bold text-muted w-35">Name:</td>
                                        <td class="fw-semibold text-dark"><?php echo sanitize($cert['bride_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Phone:</td>
                                        <td><?php echo sanitize($cert['bride_phone']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Father:</td>
                                        <td><?php echo sanitize($cert['bride_father']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Mother:</td>
                                        <td><?php echo sanitize($cert['bride_mother']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Date of Birth:</td>
                                        <td><?php echo sanitize($cert['bride_birth']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">NID:</td>
                                        <td><?php echo sanitize($cert['bride_nid'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Passport:</td>
                                        <td><?php echo sanitize($cert['bride_passport'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Address:</td>
                                        <td class="text-wrap"><?php echo nl2br(sanitize($cert['bride_address'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Marriage Info -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-circle-info text-warning me-2"></i>Marriage Registration Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <div class="p-2 border-bottom">
                                    <span class="fw-bold text-muted d-block">Marriage Date & Time:</span>
                                    <span class="text-dark fw-semibold"><?php echo sanitize($cert['marriage_date']) . ' at ' . date('h:i A', strtotime($cert['marriage_time'])); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-2 border-bottom">
                                    <span class="fw-bold text-muted d-block">Guardian (Wali):</span>
                                    <span class="text-dark fw-semibold"><?php echo sanitize($cert['wali_name'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-2 border-bottom">
                                    <span class="fw-bold text-muted d-block">Marriage Place / Venue:</span>
                                    <span class="text-dark fw-semibold"><?php echo sanitize($cert['marriage_place']); ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-2">
                                    <span class="fw-bold text-muted d-block">Mahr Amount:</span>
                                    <span class="text-dark fw-bold"><?php echo number_format($cert['mahr_amount'], 2) . ' ' . sanitize($cert['currency']); ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-2">
                                    <span class="fw-bold text-muted d-block">Payment Status:</span>
                                    <?php if ($cert['mahr_status'] === 'paid'): ?>
                                        <span class="badge badge-paid">Paid (Wasl)</span>
                                    <?php elseif ($cert['mahr_status'] === 'due'): ?>
                                        <span class="badge badge-due">Due (Mu'ajjal)</span>
                                    <?php else: ?>
                                        <span class="badge badge-partial">Partially Paid</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Witnesses & Registrar -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-user-shield text-warning me-2"></i>Witnesses & registrar</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <div class="p-2 bg-light rounded-3 h-100">
                                    <span class="fw-bold text-dark d-block mb-2">Witness 1:</span>
                                    <span class="text-muted d-block">Name: <strong class="text-dark"><?php echo sanitize($cert['witness1_name']); ?></strong></span>
                                    <span class="text-muted">NID: <strong class="text-dark"><?php echo sanitize($cert['witness1_nid']); ?></strong></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-2 bg-light rounded-3 h-100">
                                    <span class="fw-bold text-dark d-block mb-2">Witness 2:</span>
                                    <span class="text-muted d-block">Name: <strong class="text-dark"><?php echo sanitize($cert['witness2_name']); ?></strong></span>
                                    <span class="text-muted">NID: <strong class="text-dark"><?php echo sanitize($cert['witness2_nid']); ?></strong></span>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <span class="fw-bold text-dark d-block mb-2">Registrar Information:</span>
                                <div class="row g-3 border-top pt-2">
                                    <div class="col-md-4">
                                        <span class="text-muted d-block small">Name:</span>
                                        <span class="fw-semibold"><?php echo sanitize($cert['registrar_name']); ?></span>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="text-muted d-block small">License Number:</span>
                                        <span class="fw-semibold text-primary"><?php echo sanitize($cert['registrar_license']); ?></span>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="text-muted d-block small">Phone:</span>
                                        <span class="fw-semibold"><?php echo sanitize($cert['registrar_phone']); ?></span>
                                    </div>
                                    <div class="col-12">
                                        <span class="text-muted d-block small">Office Address:</span>
                                        <span class="fw-semibold"><?php echo sanitize($cert['registrar_address']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <?php if (!empty($cert['notes'])): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-note-sticky text-warning me-2"></i>Special Notes / Clauses</h5>
                        </div>
                        <div class="card-body text-dark small">
                            <?php echo nl2br(sanitize($cert['notes'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 text-center p-4 mb-4">
                    <h5 class="fw-bold text-dark mb-3">System Serial Meta</h5>
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <span class="text-muted small d-block uppercase fw-bold">Certificate Number</span>
                        <strong class="text-primary d-block fs-5"><?php echo sanitize($cert['certificate_no']); ?></strong>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-4">
                        <span class="text-muted small d-block uppercase fw-bold">Registration Number</span>
                        <strong class="text-dark d-block fs-5"><?php echo sanitize($cert['registration_no']); ?></strong>
                    </div>

                    <!-- QR Code verification panel -->
                    <div class="border-top pt-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-qrcode me-2 text-warning"></i>Security Verification QR</h6>
                        <div class="d-inline-block bg-white p-2 border rounded-3 mb-2 shadow-sm">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($cert['qr_code']); ?>" alt="Verification QR" width="150" height="150">
                        </div>
                        <p class="text-muted small mb-0">This QR redirects to the public verification portal to authenticate registry details.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal (Admin only) -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Confirm Deletion</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4 text-center">
                        <i class="fa-solid fa-circle-exclamation text-danger fa-4x mb-3 animate__animated animate__pulse animate__infinite"></i>
                        <p class="lead fw-semibold text-dark mb-2">Are you absolutely sure?</p>
                        <p class="text-muted mb-0 small">This action cannot be undone. This will permanently remove certificate <strong><?php echo sanitize($cert['certificate_no']); ?></strong> from the database registry.</p>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal">Cancel</button>
                        <form action="view.php?id=<?php echo $cert['id']; ?>" method="POST" class="d-inline">
                            <!-- CSRF -->
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-danger px-4">Yes, Delete Certificate</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> Nikahnama Registry System. All Rights Reserved.</p>
            <p class="small text-muted mb-0">System Version 2.0.0 | Powered by PHP, PDO & Material 3 Principles</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
