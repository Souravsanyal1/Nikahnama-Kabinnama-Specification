<?php
// edit.php

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

$id = intval($_GET['id']);
$cert = $controller->show($id); // This will redirect if not found

// Process form update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleEdit($id);
}

// Retrieve validation errors if they exist from session
$errors = $_SESSION['form_errors'] ?? [];

// Clear session variables after retrieving
unset($_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Certificate - Nikahnama Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="assets/css/style.css">
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

    <!-- Main edit panel -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Edit Certificate Details
                        </h3>
                        <p class="text-muted small mb-0">Modifying Certificate: <strong class="text-primary"><?php echo sanitize($cert['certificate_no']); ?></strong></p>
                    </div>
                    <div>
                        <a href="view.php?id=<?php echo $cert['id']; ?>" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="fa-solid fa-eye me-1"></i> View Record
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>

                <?php if (has_flash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo flash('error'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <!-- Form Tabs Header -->
                    <ul class="nav nav-pills form-tabs mb-4 justify-content-center" id="formTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="groom-tab" data-bs-toggle="pill" data-bs-target="#groomPane" type="button" role="tab">
                                <i class="fa-solid fa-user-tie me-2"></i>Groom Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bride-tab" data-bs-toggle="pill" data-bs-target="#bridePane" type="button" role="tab">
                                <i class="fa-solid fa-user-dress me-2"></i>Bride Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="marriage-tab" data-bs-toggle="pill" data-bs-target="#marriagePane" type="button" role="tab">
                                <i class="fa-solid fa-ring me-2"></i>Marriage & Mahr
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="witness-tab" data-bs-toggle="pill" data-bs-target="#witnessPane" type="button" role="tab">
                                <i class="fa-solid fa-users me-2"></i>Witnesses & Registrar
                            </button>
                        </li>
                    </ul>

                    <!-- Form start -->
                    <form action="edit.php?id=<?php echo $cert['id']; ?>" method="POST" class="needs-validation" novalidate>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                        <div class="tab-content" id="formTabsContent">
                            
                            <!-- GROOM DETAILS PANE -->
                            <div class="tab-pane fade show active" id="groomPane" role="tabpanel">
                                <div class="form-section-title">Groom Information</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="groom_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['groom_name']) ? 'is-invalid' : ''; ?>" id="groom_name" name="groom_name" value="<?php echo sanitize($cert['groom_name']); ?>" required>
                                        <?php if (isset($errors['groom_name'])): ?><div class="invalid-feedback"><?php echo $errors['groom_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="groom_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['groom_phone']) ? 'is-invalid' : ''; ?>" id="groom_phone" name="groom_phone" value="<?php echo sanitize($cert['groom_phone']); ?>" required>
                                        <?php if (isset($errors['groom_phone'])): ?><div class="invalid-feedback"><?php echo $errors['groom_phone']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="groom_father" class="form-label">Father's Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['groom_father']) ? 'is-invalid' : ''; ?>" id="groom_father" name="groom_father" value="<?php echo sanitize($cert['groom_father']); ?>" required>
                                        <?php if (isset($errors['groom_father'])): ?><div class="invalid-feedback"><?php echo $errors['groom_father']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="groom_mother" class="form-label">Mother's Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['groom_mother']) ? 'is-invalid' : ''; ?>" id="groom_mother" name="groom_mother" value="<?php echo sanitize($cert['groom_mother']); ?>" required>
                                        <?php if (isset($errors['groom_mother'])): ?><div class="invalid-feedback"><?php echo $errors['groom_mother']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="groom_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control <?php echo isset($errors['groom_birth']) ? 'is-invalid' : ''; ?>" id="groom_birth" name="groom_birth" value="<?php echo sanitize($cert['groom_birth']); ?>" required>
                                        <?php if (isset($errors['groom_birth'])): ?><div class="invalid-feedback"><?php echo $errors['groom_birth']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="groom_nid" class="form-label">NID Number</label>
                                        <input type="text" class="form-control" id="groom_nid" name="groom_nid" value="<?php echo sanitize($cert['groom_nid']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="groom_passport" class="form-label">Passport Number</label>
                                        <input type="text" class="form-control" id="groom_passport" name="groom_passport" value="<?php echo sanitize($cert['groom_passport']); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label for="groom_address" class="form-label">Address (Present & Permanent) <span class="text-danger">*</span></label>
                                        <textarea class="form-control <?php echo isset($errors['groom_address']) ? 'is-invalid' : ''; ?>" id="groom_address" name="groom_address" rows="3" required><?php echo sanitize($cert['groom_address']); ?></textarea>
                                        <?php if (isset($errors['groom_address'])): ?><div class="invalid-feedback"><?php echo $errors['groom_address']; ?></div><?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="button" class="btn btn-primary-custom btn-next-tab" data-next="#bridePane">
                                        Next Tab <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- BRIDE DETAILS PANE -->
                            <div class="tab-pane fade" id="bridePane" role="tabpanel">
                                <div class="form-section-title">Bride Information</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="bride_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['bride_name']) ? 'is-invalid' : ''; ?>" id="bride_name" name="bride_name" value="<?php echo sanitize($cert['bride_name']); ?>" required>
                                        <?php if (isset($errors['bride_name'])): ?><div class="invalid-feedback"><?php echo $errors['bride_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bride_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['bride_phone']) ? 'is-invalid' : ''; ?>" id="bride_phone" name="bride_phone" value="<?php echo sanitize($cert['bride_phone']); ?>" required>
                                        <?php if (isset($errors['bride_phone'])): ?><div class="invalid-feedback"><?php echo $errors['bride_phone']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bride_father" class="form-label">Father's Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['bride_father']) ? 'is-invalid' : ''; ?>" id="bride_father" name="bride_father" value="<?php echo sanitize($cert['bride_father']); ?>" required>
                                        <?php if (isset($errors['bride_father'])): ?><div class="invalid-feedback"><?php echo $errors['bride_father']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bride_mother" class="form-label">Mother's Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['bride_mother']) ? 'is-invalid' : ''; ?>" id="bride_mother" name="bride_mother" value="<?php echo sanitize($cert['bride_mother']); ?>" required>
                                        <?php if (isset($errors['bride_mother'])): ?><div class="invalid-feedback"><?php echo $errors['bride_mother']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="bride_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control <?php echo isset($errors['bride_birth']) ? 'is-invalid' : ''; ?>" id="bride_birth" name="bride_birth" value="<?php echo sanitize($cert['bride_birth']); ?>" required>
                                        <?php if (isset($errors['bride_birth'])): ?><div class="invalid-feedback"><?php echo $errors['bride_birth']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="bride_nid" class="form-label">NID Number</label>
                                        <input type="text" class="form-control" id="bride_nid" name="bride_nid" value="<?php echo sanitize($cert['bride_nid']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="bride_passport" class="form-label">Passport Number</label>
                                        <input type="text" class="form-control" id="bride_passport" name="bride_passport" value="<?php echo sanitize($cert['bride_passport']); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label for="bride_address" class="form-label">Address (Present & Permanent) <span class="text-danger">*</span></label>
                                        <textarea class="form-control <?php echo isset($errors['bride_address']) ? 'is-invalid' : ''; ?>" id="bride_address" name="bride_address" rows="3" required><?php echo sanitize($cert['bride_address']); ?></textarea>
                                        <?php if (isset($errors['bride_address'])): ?><div class="invalid-feedback"><?php echo $errors['bride_address']; ?></div><?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-prev-tab" data-prev="#groomPane">
                                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                                    </button>
                                    <button type="button" class="btn btn-primary-custom btn-next-tab" data-next="#marriagePane">
                                        Next Tab <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- MARRIAGE & MAHR DETAILS PANE -->
                            <div class="tab-pane fade" id="marriagePane" role="tabpanel">
                                <div class="form-section-title">Marriage & Wali Information</div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label for="marriage_date" class="form-label">Marriage Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control <?php echo isset($errors['marriage_date']) ? 'is-invalid' : ''; ?>" id="marriage_date" name="marriage_date" value="<?php echo sanitize($cert['marriage_date']); ?>" required>
                                        <?php if (isset($errors['marriage_date'])): ?><div class="invalid-feedback"><?php echo $errors['marriage_date']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="marriage_time" class="form-label">Marriage Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control <?php echo isset($errors['marriage_time']) ? 'is-invalid' : ''; ?>" id="marriage_time" name="marriage_time" value="<?php echo sanitize($cert['marriage_time']); ?>" required>
                                        <?php if (isset($errors['marriage_time'])): ?><div class="invalid-feedback"><?php echo $errors['marriage_time']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="wali_name" class="form-label">Guardian / Wali Name</label>
                                        <input type="text" class="form-control" id="wali_name" name="wali_name" value="<?php echo sanitize($cert['wali_name']); ?>" placeholder="Optional">
                                    </div>
                                    <div class="col-12">
                                        <label for="marriage_place" class="form-label">Marriage Place / Venue Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['marriage_place']) ? 'is-invalid' : ''; ?>" id="marriage_place" name="marriage_place" value="<?php echo sanitize($cert['marriage_place']); ?>" required>
                                        <?php if (isset($errors['marriage_place'])): ?><div class="invalid-feedback"><?php echo $errors['marriage_place']; ?></div><?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-section-title">Mahr (Dower) Details</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="mahr_amount" class="form-label">Mahr Amount <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control <?php echo isset($errors['mahr_amount']) ? 'is-invalid' : ''; ?>" id="mahr_amount" name="mahr_amount" value="<?php echo sanitize($cert['mahr_amount']); ?>" required>
                                        <?php if (isset($errors['mahr_amount'])): ?><div class="invalid-feedback"><?php echo $errors['mahr_amount']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                                        <select class="form-select" id="currency" name="currency">
                                            <option value="BDT" <?php echo $cert['currency'] === 'BDT' ? 'selected' : ''; ?>>BDT (৳)</option>
                                            <option value="USD" <?php echo $cert['currency'] === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                            <option value="SAR" <?php echo $cert['currency'] === 'SAR' ? 'selected' : ''; ?>>SAR (SR)</option>
                                            <option value="EUR" <?php echo $cert['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="mahr_status" class="form-label">Payment Status <span class="text-danger">*</span></label>
                                        <select class="form-select <?php echo isset($errors['mahr_status']) ? 'is-invalid' : ''; ?>" id="mahr_status" name="mahr_status" required>
                                            <option value="paid" <?php echo $cert['mahr_status'] === 'paid' ? 'selected' : ''; ?>>Paid (Wasl)</option>
                                            <option value="due" <?php echo $cert['mahr_status'] === 'due' ? 'selected' : ''; ?>>Due (Mu'ajjal)</option>
                                            <option value="partially_paid" <?php echo $cert['mahr_status'] === 'partially_paid' ? 'selected' : ''; ?>>Partially Paid</option>
                                        </select>
                                        <?php if (isset($errors['mahr_status'])): ?><div class="invalid-feedback"><?php echo $errors['mahr_status']; ?></div><?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-prev-tab" data-prev="#bridePane">
                                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                                    </button>
                                    <button type="button" class="btn btn-primary-custom btn-next-tab" data-next="#witnessPane">
                                        Next Tab <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- WITNESSES & REGISTRAR PANE -->
                            <div class="tab-pane fade" id="witnessPane" role="tabpanel">
                                <div class="form-section-title">Witnesses Details</div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="witness1_name" class="form-label">Witness 1 Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['witness1_name']) ? 'is-invalid' : ''; ?>" id="witness1_name" name="witness1_name" value="<?php echo sanitize($cert['witness1_name']); ?>" required>
                                        <?php if (isset($errors['witness1_name'])): ?><div class="invalid-feedback"><?php echo $errors['witness1_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="witness1_nid" class="form-label">Witness 1 NID Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['witness1_nid']) ? 'is-invalid' : ''; ?>" id="witness1_nid" name="witness1_nid" value="<?php echo sanitize($cert['witness1_nid']); ?>" required>
                                        <?php if (isset($errors['witness1_nid'])): ?><div class="invalid-feedback"><?php echo $errors['witness1_nid']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="witness2_name" class="form-label">Witness 2 Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['witness2_name']) ? 'is-invalid' : ''; ?>" id="witness2_name" name="witness2_name" value="<?php echo sanitize($cert['witness2_name']); ?>" required>
                                        <?php if (isset($errors['witness2_name'])): ?><div class="invalid-feedback"><?php echo $errors['witness2_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="witness2_nid" class="form-label">Witness 2 NID Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['witness2_nid']) ? 'is-invalid' : ''; ?>" id="witness2_nid" name="witness2_nid" value="<?php echo sanitize($cert['witness2_nid']); ?>" required>
                                        <?php if (isset($errors['witness2_nid'])): ?><div class="invalid-feedback"><?php echo $errors['witness2_nid']; ?></div><?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-section-title">Nikah Registrar Details</div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="registrar_name" class="form-label">Registrar Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['registrar_name']) ? 'is-invalid' : ''; ?>" id="registrar_name" name="registrar_name" value="<?php echo sanitize($cert['registrar_name']); ?>" required>
                                        <?php if (isset($errors['registrar_name'])): ?><div class="invalid-feedback"><?php echo $errors['registrar_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="registrar_license" class="form-label">License Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['registrar_license']) ? 'is-invalid' : ''; ?>" id="registrar_license" name="registrar_license" value="<?php echo sanitize($cert['registrar_license']); ?>" required>
                                        <?php if (isset($errors['registrar_license'])): ?><div class="invalid-feedback"><?php echo $errors['registrar_license']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="registrar_phone" class="form-label">Registrar Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['registrar_phone']) ? 'is-invalid' : ''; ?>" id="registrar_phone" name="registrar_phone" value="<?php echo sanitize($cert['registrar_phone']); ?>" required>
                                        <?php if (isset($errors['registrar_phone'])): ?><div class="invalid-feedback"><?php echo $errors['registrar_phone']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="registrar_address" class="form-label">Registrar Office Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['registrar_address']) ? 'is-invalid' : ''; ?>" id="registrar_address" name="registrar_address" value="<?php echo sanitize($cert['registrar_address']); ?>" required>
                                        <?php if (isset($errors['registrar_address'])): ?><div class="invalid-feedback"><?php echo $errors['registrar_address']; ?></div><?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-section-title">Special Notes</div>
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Custom Notes or Clauses</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo sanitize($cert['notes']); ?></textarea>
                                </div>

                                <div class="d-flex justify-content-between mt-5 border-top pt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-prev-tab" data-prev="#marriagePane">
                                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                                    </button>
                                    <div>
                                        <button type="submit" class="btn btn-primary-custom px-4">
                                            <i class="fa-solid fa-save me-2"></i>Update Certificate
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> Nikahnama Registry System. All Rights Reserved.</p>
            <p class="small text-muted mb-0">System Version 2.0.0 | Powered by PHP, PDO & Material 3 Principles</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
