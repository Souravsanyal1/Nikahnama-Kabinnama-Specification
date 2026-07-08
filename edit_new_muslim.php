<?php
// edit_new_muslim.php

require_once 'app/controllers/NikahController.php';
require_once 'app/helpers/session.php';

// Route guards
require_login();

$controller = new NikahController();

// Check for ID parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    flash('error', 'নওমুসলিম আইডি প্রদান করা হয়নি।');
    header("Location: dashboard.php");
    exit;
}

$id = sanitize($_GET['id']);
$cert = $controller->showNewMuslim($id); // Redirects to dashboard if not found

// Process form update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleEditNewMuslim($id);
}

// Retrieve validation errors if they exist from session
$errors = $_SESSION['form_errors'] ?? [];

// Clear session variables after retrieving
unset($_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নিবন্ধন সংশোধন - নওমুসলিম ডিজিটাল রেজিস্ট্রি</title>
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
                        <a class="nav-link" href="create_new_muslim.php"><i class="fa-solid fa-circle-plus me-1"></i> নতুন নওমুসলিম</a>
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

    <!-- Main edit panel -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-pen-to-square me-2 text-warning"></i>নওমুসলিম নিবন্ধন তথ্য সংশোধন করুন
                        </h3>
                        <p class="text-muted small mb-0">সার্টিফিকেট নম্বর: <strong class="text-primary"><?php echo sanitize($cert['certificate_no']); ?></strong></p>
                    </div>
                    <div>
                        <a href="view_new_muslim.php?id=<?php echo $cert['id']; ?>" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="fa-solid fa-eye me-1"></i> তথ্য দেখুন
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i> ড্যাশবোর্ডে ফিরে যান
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
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="pill" data-bs-target="#personalPane" type="button" role="tab">
                                <i class="fa-solid fa-user-check me-2"></i>নওমুসলিমের সাধারণ তথ্য
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="declaration-tab" data-bs-toggle="pill" data-bs-target="#declarationPane" type="button" role="tab">
                                <i class="fa-solid fa-file-contract me-2"></i>ইসলাম গ্রহণ ও দীক্ষা
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="witness-tab" data-bs-toggle="pill" data-bs-target="#witnessPane" type="button" role="tab">
                                <i class="fa-solid fa-users me-2"></i>সাক্ষীগণের বিবরণ
                            </button>
                        </li>
                    </ul>

                    <!-- Form start -->
                    <form action="edit_new_muslim.php?id=<?php echo $cert['id']; ?>" method="POST" class="needs-validation" novalidate>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                        <div class="tab-content" id="formTabsContent">
                            
                            <!-- PERSONAL DETAILS PANE -->
                            <div class="tab-pane fade show active" id="personalPane" role="tabpanel">
                                <div class="form-section-title">ব্যক্তিগত তথ্য ও নাম পরিবর্তন</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="previous_name" class="form-label">পূর্বের নাম (ধর্মীয় নাম) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['previous_name']) ? 'is-invalid' : ''; ?>" id="previous_name" name="previous_name" value="<?php echo sanitize($cert['previous_name']); ?>" required>
                                        <?php if (isset($errors['previous_name'])): ?><div class="invalid-feedback"><?php echo $errors['previous_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="previous_religion" class="form-label">পূর্বের ধর্ম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['previous_religion']) ? 'is-invalid' : ''; ?>" id="previous_religion" name="previous_religion" value="<?php echo sanitize($cert['previous_religion']); ?>" required>
                                        <?php if (isset($errors['previous_religion'])): ?><div class="invalid-feedback"><?php echo $errors['previous_religion']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="new_name" class="form-label">নতুন ইসলামী নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['new_name']) ? 'is-invalid' : ''; ?>" id="new_name" name="new_name" value="<?php echo sanitize($cert['new_name']); ?>" required>
                                        <?php if (isset($errors['new_name'])): ?><div class="invalid-feedback"><?php echo $errors['new_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone_no" class="form-label">মোবাইল নম্বর <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['phone_no']) ? 'is-invalid' : ''; ?>" id="phone_no" name="phone_no" value="<?php echo sanitize($cert['phone_no']); ?>" required>
                                        <?php if (isset($errors['phone_no'])): ?><div class="invalid-feedback"><?php echo $errors['phone_no']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="father_name" class="form-label">পিতার নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['father_name']) ? 'is-invalid' : ''; ?>" id="father_name" name="father_name" value="<?php echo sanitize($cert['father_name']); ?>" required>
                                        <?php if (isset($errors['father_name'])): ?><div class="invalid-feedback"><?php echo $errors['father_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mother_name" class="form-label">মাতার নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['mother_name']) ? 'is-invalid' : ''; ?>" id="mother_name" name="mother_name" value="<?php echo sanitize($cert['mother_name']); ?>" required>
                                        <?php if (isset($errors['mother_name'])): ?><div class="invalid-feedback"><?php echo $errors['mother_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="date_of_birth" class="form-label">জন্ম তারিখ <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control <?php echo isset($errors['date_of_birth']) ? 'is-invalid' : ''; ?>" id="date_of_birth" name="date_of_birth" value="<?php echo sanitize($cert['date_of_birth']); ?>" required>
                                        <?php if (isset($errors['date_of_birth'])): ?><div class="invalid-feedback"><?php echo $errors['date_of_birth']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="nid_no" class="form-label">NID (জাতীয় পরিচয়পত্র) নম্বর</label>
                                        <input type="text" class="form-control" id="nid_no" name="nid_no" value="<?php echo sanitize($cert['nid_no']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="passport_no" class="form-label">পাসপোর্ট নম্বর (ঐচ্ছিক)</label>
                                        <input type="text" class="form-control" id="passport_no" name="passport_no" value="<?php echo sanitize($cert['passport_no']); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label for="address" class="form-label">পূর্ণ ঠিকানা (বর্তমান ও স্থায়ী) <span class="text-danger">*</span></label>
                                        <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" id="address" name="address" rows="3" required><?php echo sanitize($cert['address']); ?></textarea>
                                        <?php if (isset($errors['address'])): ?><div class="invalid-feedback"><?php echo $errors['address']; ?></div><?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="button" class="btn btn-primary-custom btn-next-tab" data-next="#declarationPane">
                                        পরবর্তী ধাপ <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- DECLARATION & EMBRACING PANE -->
                            <div class="tab-pane fade" id="declarationPane" role="tabpanel">
                                <div class="form-section-title">ইসলাম গ্রহণ ও দীক্ষা সংক্রান্ত তথ্য</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="declaration_date" class="form-label">স্বেচ্ছায় ইসলাম গ্রহণের তারিখ <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control <?php echo isset($errors['declaration_date']) ? 'is-invalid' : ''; ?>" id="declaration_date" name="declaration_date" value="<?php echo sanitize($cert['declaration_date']); ?>" required>
                                        <?php if (isset($errors['declaration_date'])): ?><div class="invalid-feedback"><?php echo $errors['declaration_date']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="institution_name" class="form-label">মসজিদ / দীক্ষাদানকারী প্রতিষ্ঠানের নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['institution_name']) ? 'is-invalid' : ''; ?>" id="institution_name" name="institution_name" value="<?php echo sanitize($cert['institution_name']); ?>" required>
                                        <?php if (isset($errors['institution_name'])): ?><div class="invalid-feedback"><?php echo $errors['institution_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="imam_name" class="form-label">দীক্ষাদানকারী ইমাম / আলেমের নাম <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['imam_name']) ? 'is-invalid' : ''; ?>" id="imam_name" name="imam_name" value="<?php echo sanitize($cert['imam_name']); ?>" required>
                                        <?php if (isset($errors['imam_name'])): ?><div class="invalid-feedback"><?php echo $errors['imam_name']; ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="imam_title" class="form-label">ইমামের পদবী <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php echo isset($errors['imam_title']) ? 'is-invalid' : ''; ?>" id="imam_title" name="imam_title" value="<?php echo sanitize($cert['imam_title']); ?>" required>
                                        <?php if (isset($errors['imam_title'])): ?><div class="invalid-feedback"><?php echo $errors['imam_title']; ?></div><?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-prev-tab" data-prev="#personalPane">
                                        <i class="fa-solid fa-arrow-left me-1"></i> পূর্ববর্তী ধাপ
                                    </button>
                                    <button type="button" class="btn btn-primary-custom btn-next-tab" data-next="#witnessPane">
                                        পরবর্তী ধাপ <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- WITNESSES PANE -->
                            <div class="tab-pane fade" id="witnessPane" role="tabpanel">
                                <div class="form-section-title">সাক্ষীগণের তথ্য</div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <div class="fw-bold text-dark mb-3 border-bottom pb-2">১ম সাক্ষী</div>
                                            <div class="mb-3">
                                                <label for="witness1_name" class="form-label">পূর্ণ নাম <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control <?php echo isset($errors['witness1_name']) ? 'is-invalid' : ''; ?>" id="witness1_name" name="witness1_name" value="<?php echo sanitize($cert['witness1_name']); ?>" required>
                                                <?php if (isset($errors['witness1_name'])): ?><div class="invalid-feedback"><?php echo $errors['witness1_name']; ?></div><?php endif; ?>
                                            </div>
                                            <div class="mb-3">
                                                <label for="witness1_nid" class="form-label">NID নম্বর <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control <?php echo isset($errors['witness1_nid']) ? 'is-invalid' : ''; ?>" id="witness1_nid" name="witness1_nid" value="<?php echo sanitize($cert['witness1_nid']); ?>" required>
                                                <?php if (isset($errors['witness1_nid'])): ?><div class="invalid-feedback"><?php echo $errors['witness1_nid']; ?></div><?php endif; ?>
                                            </div>
                                            <div>
                                                <label for="witness1_address" class="form-label">পূর্ণ ঠিকানা (ঐচ্ছিক)</label>
                                                <input type="text" class="form-control" id="witness1_address" name="witness1_address" value="<?php echo sanitize($cert['witness1_address']); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <div class="fw-bold text-dark mb-3 border-bottom pb-2">২য় সাক্ষী</div>
                                            <div class="mb-3">
                                                <label for="witness2_name" class="form-label">পূর্ণ নাম <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control <?php echo isset($errors['witness2_name']) ? 'is-invalid' : ''; ?>" id="witness2_name" name="witness2_name" value="<?php echo sanitize($cert['witness2_name']); ?>" required>
                                                <?php if (isset($errors['witness2_name'])): ?><div class="invalid-feedback"><?php echo $errors['witness2_name']; ?></div><?php endif; ?>
                                            </div>
                                            <div class="mb-3">
                                                <label for="witness2_nid" class="form-label">NID নম্বর <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control <?php echo isset($errors['witness2_nid']) ? 'is-invalid' : ''; ?>" id="witness2_nid" name="witness2_nid" value="<?php echo sanitize($cert['witness2_nid']); ?>" required>
                                                <?php if (isset($errors['witness2_nid'])): ?><div class="invalid-feedback"><?php echo $errors['witness2_nid']; ?></div><?php endif; ?>
                                            </div>
                                            <div>
                                                <label for="witness2_address" class="form-label">পূর্ণ ঠিকানা (ঐচ্ছিক)</label>
                                                <input type="text" class="form-control" id="witness2_address" name="witness2_address" value="<?php echo sanitize($cert['witness2_address']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section-title">অতিরিক্ত তথ্য / বিশেষ মন্তব্য</div>
                                <div class="mb-3">
                                    <label for="notes" class="form-label">যদি কোনো বিশেষ আইনগত হলফনামা বা অন্যান্য মন্তব্য থাকে</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo sanitize($cert['notes']); ?></textarea>
                                </div>

                                <div class="d-flex justify-content-between mt-5 border-top pt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-prev-tab" data-prev="#declarationPane">
                                        <i class="fa-solid fa-arrow-left me-1"></i> পূর্ববর্তী ধাপ
                                    </button>
                                    <div>
                                        <button type="submit" class="btn btn-primary-custom px-4">
                                            <i class="fa-solid fa-save me-2"></i> তথ্য আপডেট করুন
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
