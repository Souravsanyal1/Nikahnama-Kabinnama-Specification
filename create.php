<?php
// create.php

require_once 'app/controllers/NikahController.php';
require_once 'app/helpers/session.php';

// Route guards
require_login();

$controller = new NikahController();
$controller->handleCreate();

// Retrieve errors or previous form inputs if they exist
$errors = $_SESSION['form_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];

// Clear session variables after retrieving
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নিকাহনামা নিবন্ধন - ডিজিটাল নিকাহনামা রেজিস্ট্রি</title>
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
                <i class="fa-solid fa-mosque me-2"></i>নিকাহ<span>নামা</span>
            </a>
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-gauge me-1"></i> ড্যাশবোর্ড</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="create.php"><i class="fa-solid fa-circle-plus me-1"></i> নতুন নিবন্ধন</a>
                    </li>
                    <li class="nav-item me-3">
                        <span class="navbar-text text-light opacity-75 ms-2">
                            <i class="fa-solid fa-user me-1 text-warning"></i> <?php echo sanitize($_SESSION['fullname']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-sm btn-outline-danger">
                            <i class="fa-solid fa-right-from-bracket me-1"></i> লগআউট
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Form container -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h3 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-file-signature me-2 text-warning"></i>বিবাহ নিবন্ধন ফরম (নিকাহনামা)
                    </h3>
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i> ড্যাশবোর্ডে ফিরে যান
                    </a>
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
                                <i class="fa-solid fa-user-tie me-2"></i>বরের বিবরণ
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bride-tab" data-bs-toggle="pill" data-bs-target="#bridePane" type="button" role="tab">
                                <i class="fa-solid fa-user-dress me-2"></i>কনের বিবরণ
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="marriage-tab" data-bs-toggle="pill" data-bs-target="#marriagePane" type="button" role="tab">
                                <i class="fa-solid fa-ring me-2"></i>বিবাহ ও দেনমোহর
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="witness-tab" data-bs-toggle="pill" data-bs-target="#witnessPane" type="button" role="tab">
                                <i class="fa-solid fa-users me-2"></i>সাক্ষী ও কাজী
                            </button>
                        </li>
                    </ul>

                    <!-- Form start -->
                    <form action="create.php" method="POST" class="needs-validation" novalidate>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                        <div class="tab-content" id="formTabsContent">
                            
                            <!-- GROOM DETAILS PANE -->
                            <div class="tab-pane fade show active" id="groomPane" role="tabpanel">
                                <div class="form-section-title">বরের সাধারণ তথ্য</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="groom_name" class="form-label">বরের পূর্ণ নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['groom_name']) ? 'is-invalid' : ''; ?>" id="groom_name" name="groom_name" value="<?php echo sanitize($form_data['groom_name'] ?? ''); ?>" required>
                                        <?php if (isset($errors['groom_name'])): ?><div class="invalid-feedback"><?php echo $errors['groom_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="groom_phone" class="form-label">মোবাইল নম্বর <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['groom_phone']) ? 'is-invalid' : ''; ?>" id="groom_phone" name="groom_phone" value="<?php echo sanitize($form_data['groom_phone'] ?? ''); ?>" required>
                                        <?php if (isset($errors['groom_phone'])): ?><div class="invalid-feedback"><?php echo $errors['groom_phone']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="groom_father" class="form-label">পিতার নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['groom_father']) ? 'is-invalid' : ''; ?>" id="groom_father" name="groom_father" value="<?php echo sanitize($form_data['groom_father'] ?? ''); ?>" required>
                                        <?php if (isset($errors['groom_father'])): ?><div class="invalid-feedback"><?php echo $errors['groom_father']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="groom_mother" class="form-label">মাতার নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['groom_mother']) ? 'is-invalid' : ''; ?>" id="groom_mother" name="groom_mother" value="<?php echo sanitize($form_data['groom_mother'] ?? ''); ?>" required>
                                        <?php if (isset($errors['groom_mother'])): ?><div class="invalid-feedback"><?php echo $errors['groom_mother']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="groom_birth" class="form-label">জন্ম তারিখ <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control <?php echo isset($errors['groom_birth']) ? 'is-invalid' : ''; ?>" id="groom_birth" name="groom_birth" value="<?php echo sanitize($form_data['groom_birth'] ?? ''); ?>" required>
                                        <?php if (isset($errors['groom_birth'])): ?><div class="invalid-feedback"><?php echo $errors['groom_birth']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="groom_nid" class="form-label">NID (জাতীয় পরিচয়পত্র) নম্বর</label>
                                        <input type="text" class="form-control" id="groom_nid" name="groom_nid" value="<?php echo sanitize($form_data['groom_nid'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="groom_passport" class="form-label">পাসপোর্ট নম্বর (ঐচ্ছিক)</label>
                                        <input type="text" class="form-control" id="groom_passport" name="groom_passport" value="<?php echo sanitize($form_data['groom_passport'] ?? ''); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label for="groom_address" class="form-label">পূর্ণ ঠিকানা (বর্তমান ও স্থায়ী) <span class="text-danger">*</span></label>
                                        <textarea class="form-control <?php echo isset($errors['groom_address']) ? 'is-invalid' : ''; ?>" id="groom_address" name="groom_address" rows="3" required><?php echo sanitize($form_data['groom_address'] ?? ''); ?></textarea>
                                        <?php if (isset($errors['groom_address'])): ?><div class="invalid-feedback"><?php echo $errors['groom_address']; ?></div><?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="button" class="btn btn-primary-custom btn-next-tab" data-next="#bridePane">
                                        পরবর্তী ধাপ <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- BRIDE DETAILS PANE -->
                            <div class="tab-pane fade" id="bridePane" role="tabpanel">
                                <div class="form-section-title">কনের সাধারণ তথ্য</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="bride_name" class="form-label">কনের পূর্ণ নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['bride_name']) ? 'is-invalid' : ''; ?>" id="bride_name" name="bride_name" value="<?php echo sanitize($form_data['bride_name'] ?? ''); ?>" required>
                                        <?php if (isset($errors['bride_name'])): ?><div class="invalid-feedback"><?php echo $errors['bride_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bride_phone" class="form-label">মোবাইল নম্বর <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['bride_phone']) ? 'is-invalid' : ''; ?>" id="bride_phone" name="bride_phone" value="<?php echo sanitize($form_data['bride_phone'] ?? ''); ?>" required>
                                        <?php if (isset($errors['bride_phone'])): ?><div class="invalid-feedback"><?php echo $errors['bride_phone']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bride_father" class="form-label">পিতার নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['bride_father']) ? 'is-invalid' : ''; ?>" id="bride_father" name="bride_father" value="<?php echo sanitize($form_data['bride_father'] ?? ''); ?>" required>
                                        <?php if (isset($errors['bride_father'])): ?><div class="invalid-feedback"><?php echo $errors['bride_father']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bride_mother" class="form-label">মাতার নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['bride_mother']) ? 'is-invalid' : ''; ?>" id="bride_mother" name="bride_mother" value="<?php echo sanitize($form_data['bride_mother'] ?? ''); ?>" required>
                                        <?php if (isset($errors['bride_mother'])): ?><div class="invalid-feedback"><?php echo $errors['bride_mother']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="bride_birth" class="form-label">জন্ম তারিখ <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control <?php echo isset($errors['bride_birth']) ? 'is-invalid' : ''; ?>" id="bride_birth" name="bride_birth" value="<?php echo sanitize($form_data['bride_birth'] ?? ''); ?>" required>
                                        <?php if (isset($errors['bride_birth'])): ?><div class="invalid-feedback"><?php echo $errors['bride_birth']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="bride_nid" class="form-label">NID (জাতীয় পরিচয়পত্র) নম্বর</label>
                                        <input type="text" class="form-control" id="bride_nid" name="bride_nid" value="<?php echo sanitize($form_data['bride_nid'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="bride_passport" class="form-label">পাসপোর্ট নম্বর (ঐচ্ছিক)</label>
                                        <input type="text" class="form-control" id="bride_passport" name="bride_passport" value="<?php echo sanitize($form_data['bride_passport'] ?? ''); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label for="bride_address" class="form-label">পূর্ণ ঠিকানা (বর্তমান ও স্থায়ী) <span class="text-danger">*</span></label>
                                        <textarea class="form-control <?php echo isset($errors['bride_address']) ? 'is-invalid' : ''; ?>" id="bride_address" name="bride_address" rows="3" required><?php echo sanitize($form_data['bride_address'] ?? ''); ?></textarea>
                                        <?php if (isset($errors['bride_address'])): ?><div class="invalid-feedback"><?php echo $errors['bride_address']; ?></div><?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-prev-tab" data-prev="#groomPane">
                                        <i class="fa-solid fa-arrow-left me-1"></i> পূর্ববর্তী ধাপ
                                    </button>
                                    <button type="button" class="btn btn-primary-custom btn-next-tab" data-next="#marriagePane">
                                        পরবর্তী ধাপ <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- MARRIAGE & MAHR DETAILS PANE -->
                            <div class="tab-pane fade" id="marriagePane" role="tabpanel">
                                <div class="form-section-title">বিবাহ ও অভিভাবকের তথ্য</div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label for="marriage_date" class="form-label">বিবাহ সম্পন্ন হওয়ার তারিখ <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control <?php echo isset($errors['marriage_date']) ? 'is-invalid' : ''; ?>" id="marriage_date" name="marriage_date" value="<?php echo sanitize($form_data['marriage_date'] ?? ''); ?>" required>
                                        <?php if (isset($errors['marriage_date'])): ?><div class="invalid-feedback"><?php echo $errors['marriage_date']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="marriage_time" class="form-label">বিবাহের সময় <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control <?php echo isset($errors['marriage_time']) ? 'is-invalid' : ''; ?>" id="marriage_time" name="marriage_time" value="<?php echo sanitize($form_data['marriage_time'] ?? ''); ?>" required>
                                        <?php if (isset($errors['marriage_time'])): ?><div class="invalid-feedback"><?php echo $errors['marriage_time']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="wali_name" class="form-label">ওয়ালী / অভিভাবকের নাম (ঐচ্ছিক)</label>
                                        <input type="text" class="form-control" id="wali_name" name="wali_name" value="<?php echo sanitize($form_data['wali_name'] ?? ''); ?>" placeholder="পিতা, ভাই বা অভিভাবক">
                                    </div>
                                    <div class="col-12">
                                        <label for="marriage_place" class="form-label">বিবাহের স্থান / ভেন্যুর পূর্ণ ঠিকানা <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['marriage_place']) ? 'is-invalid' : ''; ?>" id="marriage_place" name="marriage_place" value="<?php echo sanitize($form_data['marriage_place'] ?? ''); ?>" placeholder="মসজিদ বা ভেন্যুর ঠিকানা" required>
                                        <?php if (isset($errors['marriage_place'])): ?><div class="invalid-feedback"><?php echo $errors['marriage_place']; ?></div><?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-section-title">দেনমোহর (মোহরানা) সংক্রান্ত তথ্য</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="mahr_amount" class="form-label">দেনমোহরের পরিমাণ <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control <?php echo isset($errors['mahr_amount']) ? 'is-invalid' : ''; ?>" id="mahr_amount" name="mahr_amount" value="<?php echo sanitize($form_data['mahr_amount'] ?? ''); ?>" placeholder="0.00" required>
                                        <?php if (isset($errors['mahr_amount'])): ?><div class="invalid-feedback"><?php echo $errors['mahr_amount']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="currency" class="form-label">মুদ্রা <span class="text-danger">*</span></label>
                                        <select class="form-select" id="currency" name="currency">
                                            <option value="BDT" <?php echo ($form_data['currency'] ?? 'BDT') === 'BDT' ? 'selected' : ''; ?>>BDT (৳)</option>
                                            <option value="USD" <?php echo ($form_data['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                            <option value="SAR" <?php echo ($form_data['currency'] ?? '') === 'SAR' ? 'selected' : ''; ?>>SAR (SR)</option>
                                            <option value="EUR" <?php echo ($form_data['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="mahr_status" class="form-label">পরিশোধের অবস্থা <span class="text-danger">*</span></label>
                                        <select class="form-select <?php echo isset($errors['mahr_status']) ? 'is-invalid' : ''; ?>" id="mahr_status" name="mahr_status" required>
                                            <option value="" disabled <?php echo !isset($form_data['mahr_status']) ? 'selected' : ''; ?>>বাছাই করুন...</option>
                                            <option value="paid" <?php echo ($form_data['mahr_status'] ?? '') === 'paid' ? 'selected' : ''; ?>>পরিশোধিত (উসুল)</option>
                                            <option value="due" <?php echo ($form_data['mahr_status'] ?? '') === 'due' ? 'selected' : ''; ?>>বকেয়া (মুয়াজ্জাল)</option>
                                            <option value="partially_paid" <?php echo ($form_data['mahr_status'] ?? '') === 'partially_paid' ? 'selected' : ''; ?>>আংশিক পরিশোধিত</option>
                                        </select>
                                        <?php if (isset($errors['mahr_status'])): ?><div class="invalid-feedback"><?php echo $errors['mahr_status']; ?></div><?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-prev-tab" data-prev="#bridePane">
                                        <i class="fa-solid fa-arrow-left me-1"></i> পূর্ববর্তী ধাপ
                                    </button>
                                    <button type="button" class="btn btn-primary-custom btn-next-tab" data-next="#witnessPane">
                                        পরবর্তী ধাপ <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- WITNESSES & REGISTRAR PANE -->
                            <div class="tab-pane fade" id="witnessPane" role="tabpanel">
                                <div class="form-section-title">সাক্ষীদের সাধারণ তথ্য</div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="witness1_name" class="form-label">১ম সাক্ষীর পূর্ণ নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['witness1_name']) ? 'is-invalid' : ''; ?>" id="witness1_name" name="witness1_name" value="<?php echo sanitize($form_data['witness1_name'] ?? ''); ?>" required>
                                        <?php if (isset($errors['witness1_name'])): ?><div class="invalid-feedback"><?php echo $errors['witness1_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="witness1_nid" class="form-label">১ম সাক্ষীর NID নম্বর <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['witness1_nid']) ? 'is-invalid' : ''; ?>" id="witness1_nid" name="witness1_nid" value="<?php echo sanitize($form_data['witness1_nid'] ?? ''); ?>" required>
                                        <?php if (isset($errors['witness1_nid'])): ?><div class="invalid-feedback"><?php echo $errors['witness1_nid']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="witness2_name" class="form-label">২য় সাক্ষীর পূর্ণ নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['witness2_name']) ? 'is-invalid' : ''; ?>" id="witness2_name" name="witness2_name" value="<?php echo sanitize($form_data['witness2_name'] ?? ''); ?>" required>
                                        <?php if (isset($errors['witness2_name'])): ?><div class="invalid-feedback"><?php echo $errors['witness2_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="witness2_nid" class="form-label">২য় সাক্ষীর NID নম্বর <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['witness2_nid']) ? 'is-invalid' : ''; ?>" id="witness2_nid" name="witness2_nid" value="<?php echo sanitize($form_data['witness2_nid'] ?? ''); ?>" required>
                                        <?php if (isset($errors['witness2_nid'])): ?><div class="invalid-feedback"><?php echo $errors['witness2_nid']; ?></div><?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-section-title">নিকাহ রেজিস্টার (কাজী) সংক্রান্ত তথ্য</div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="registrar_name" class="form-label">কাজীর পূর্ণ নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['registrar_name']) ? 'is-invalid' : ''; ?>" id="registrar_name" name="registrar_name" value="<?php echo sanitize($form_data['registrar_name'] ?? ''); ?>" required>
                                        <?php if (isset($errors['registrar_name'])): ?><div class="invalid-feedback"><?php echo $errors['registrar_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="registrar_license" class="form-label">লাইসেন্স নম্বর <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['registrar_license']) ? 'is-invalid' : ''; ?>" id="registrar_license" name="registrar_license" value="<?php echo sanitize($form_data['registrar_license'] ?? ''); ?>" placeholder="যেমন: LIC/2026/9932" required>
                                        <?php if (isset($errors['registrar_license'])): ?><div class="invalid-feedback"><?php echo $errors['registrar_license']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="registrar_phone" class="form-label">কাজীর মোবাইল নম্বর <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['registrar_phone']) ? 'is-invalid' : ''; ?>" id="registrar_phone" name="registrar_phone" value="<?php echo sanitize($form_data['registrar_phone'] ?? ''); ?>" required>
                                        <?php if (isset($errors['registrar_phone'])): ?><div class="invalid-feedback"><?php echo $errors['registrar_phone']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="registrar_address" class="form-label">কাজীর কার্যালয়ের ঠিকানা <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['registrar_address']) ? 'is-invalid' : ''; ?>" id="registrar_address" name="registrar_address" value="<?php echo sanitize($form_data['registrar_address'] ?? ''); ?>" required>
                                        <?php if (isset($errors['registrar_address'])): ?><div class="invalid-feedback"><?php echo $errors['registrar_address']; ?></div><?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-section-title">বিশেষ মন্তব্য / শর্তাবলী</div>
                                <div class="mb-3">
                                    <label for="notes" class="form-label">কাবিননামার বিশেষ শর্ত বা অন্যান্য তথ্য (যদি থাকে)</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="বিশেষ কোনো সামাজিক বা আর্থিক চুক্তি থাকলে লিখুন..."><?php echo sanitize($form_data['notes'] ?? ''); ?></textarea>
                                </div>

                                <div class="d-flex justify-content-between mt-5 border-top pt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-prev-tab" data-prev="#marriagePane">
                                        <i class="fa-solid fa-arrow-left me-1"></i> পূর্ববর্তী ধাপ
                                    </button>
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary me-2">রিসেট করুন</button>
                                        <button type="submit" class="btn btn-primary-custom px-4">
                                            <i class="fa-solid fa-floppy-disk me-2"></i>নিবন্ধন সম্পন্ন করুন
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
            <p class="mb-1">&copy; <?php echo date('Y'); ?> ডিজিটাল নিকাহনামা রেজিস্ট্রি সিস্টেম। সর্বস্বত্ব সংরক্ষিত।</p>
            <p class="small text-muted mb-0">সিস্টেম সংস্করণ ২.০.০</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
