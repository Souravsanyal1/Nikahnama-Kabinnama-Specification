<?php
// view_new_muslim.php

require_once 'app/controllers/NikahController.php';
require_once 'app/helpers/session.php';

// Route guards
require_login();

$controller = new NikahController();

// Check for ID parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    flash('error', 'আইডি প্রদান করা হয়নি।');
    header("Location: dashboard.php");
    exit;
}

$id = sanitize($_GET['id']);

// Handle deletion request (POST to prevent CSRF)
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (isset($_POST['csrf_token']) && validate_csrf($_POST['csrf_token'])) {
        $controller->handleDeleteNewMuslim($id);
    } else {
        flash('error', 'CSRF ভেরিফিকেশন ব্যর্থ হয়েছে।');
    }
}

$cert = $controller->showNewMuslim($id); // Redirects to dashboard if not found
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নওমুসলিম বিবরণ - ডিজিটাল রেজিস্ট্রি</title>
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

    <!-- Main Content Container -->
    <div class="container my-5">
        
        <!-- Alerts -->
        <?php if (has_flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?php echo flash('success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (has_flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo flash('error'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">
                    <i class="fa-solid fa-user-check me-2 text-warning"></i>নওমুসলিম নিবন্ধনের বিস্তারিত বিবরণ
                </h3>
                <p class="text-muted small mb-0">রেকর্ড আইডি: #<?php echo $cert['id']; ?> | নিবন্ধনের সময়: <?php echo date('d-m-Y H:i A', strtotime($cert['created_at'])); ?></p>
            </div>
            
            <div class="d-flex flex-wrap gap-2">
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> ড্যাশবোর্ড
                </a>
                <a href="edit_new_muslim.php?id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-outline-warning">
                    <i class="fa-solid fa-pen-to-square me-1"></i> তথ্য সংশোধন
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fa-solid fa-trash me-1"></i> মুছে ফেলুন
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4 Document Print Actions Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-print text-warning me-2"></i>প্রিন্ট এবং পিডিএফ ডাউনলোড অপশন</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 border rounded-3 text-center h-100 bg-light">
                            <i class="fa-solid fa-certificate fa-2x text-primary mb-2"></i>
                            <h6 class="fw-bold text-dark">১. ইসলাম গ্রহণের সনদ</h6>
                            <div class="mt-3 d-grid gap-2">
                                <a href="print_new_muslim.php?id=<?php echo $cert['id']; ?>&type=certificate" target="_blank" class="btn btn-sm btn-primary-custom">সনদ প্রিন্ট</a>
                                <a href="pdf_new_muslim.php?id=<?php echo $cert['id']; ?>&type=certificate" target="_blank" class="btn btn-sm btn-outline-success">পিডিএফ ডাউনলোড</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 border rounded-3 text-center h-100 bg-light">
                            <i class="fa-solid fa-file-contract fa-2x text-warning mb-2"></i>
                            <h6 class="fw-bold text-dark">২. ইসলাম গ্রহণের ঘোষণা</h6>
                            <div class="mt-3 d-grid gap-2">
                                <a href="print_new_muslim.php?id=<?php echo $cert['id']; ?>&type=declaration" target="_blank" class="btn btn-sm btn-primary-custom">ঘোষণা প্রিন্ট</a>
                                <a href="pdf_new_muslim.php?id=<?php echo $cert['id']; ?>&type=declaration" target="_blank" class="btn btn-sm btn-outline-success">পিডিএফ ডাউনলোড</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 border rounded-3 text-center h-100 bg-light">
                            <i class="fa-solid fa-scale-balanced fa-2x text-success mb-2"></i>
                            <h6 class="fw-bold text-dark">৩. হলফনামার খসড়া</h6>
                            <div class="mt-3 d-grid gap-2">
                                <a href="print_new_muslim.php?id=<?php echo $cert['id']; ?>&type=affidavit" target="_blank" class="btn btn-sm btn-primary-custom">হলফনামা প্রিন্ট</a>
                                <a href="pdf_new_muslim.php?id=<?php echo $cert['id']; ?>&type=affidavit" target="_blank" class="btn btn-sm btn-outline-success">পিডিএফ ডাউনলোড</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 border rounded-3 text-center h-100 bg-light">
                            <i class="fa-solid fa-users-viewfinder fa-2x text-secondary mb-2"></i>
                            <h6 class="fw-bold text-dark">৪. সাক্ষীর বিবৃতি</h6>
                            <div class="mt-3 d-grid gap-2">
                                <a href="print_new_muslim.php?id=<?php echo $cert['id']; ?>&type=witness" target="_blank" class="btn btn-sm btn-primary-custom">বিবৃতি প্রিন্ট</a>
                                <a href="pdf_new_muslim.php?id=<?php echo $cert['id']; ?>&type=witness" target="_blank" class="btn btn-sm btn-outline-success">পিডিএফ ডাউনলোড</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Details Grid -->
            <div class="col-lg-8">
                
                <!-- Personal Info Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-user-check text-warning me-2"></i>নওমুসলিমের ব্যক্তিগত বিবরণ</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless small mb-0">
                            <tr>
                                <td class="fw-bold text-muted w-30">নতুন ইসলামী নাম:</td>
                                <td class="fw-bold text-success fs-6"><?php echo sanitize($cert['new_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">পূর্বের নাম (ধর্মীয়):</td>
                                <td class="fw-semibold text-danger"><?php echo sanitize($cert['previous_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">পূর্বের ধর্ম:</td>
                                <td><?php echo sanitize($cert['previous_religion']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">মোবাইল নম্বর:</td>
                                <td class="fw-semibold text-dark"><?php echo sanitize($cert['phone_no']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">পিতার নাম:</td>
                                <td><?php echo sanitize($cert['father_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">মাতার নাম:</td>
                                <td><?php echo sanitize($cert['mother_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">জন্ম তারিখ:</td>
                                <td><?php echo sanitize($cert['date_of_birth']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">NID নম্বর:</td>
                                <td><?php echo sanitize($cert['nid_no'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">পাসপোর্ট নম্বর:</td>
                                <td><?php echo sanitize($cert['passport_no'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">পূর্ণ ঠিকানা:</td>
                                <td><?php echo nl2br(sanitize($cert['address'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Conversion Details Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-file-invoice-dollar text-warning me-2"></i>ইসলাম গ্রহণ ও দীক্ষার তথ্য</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <div class="p-2 border-bottom">
                                    <span class="fw-bold text-muted d-block">স্বেচ্ছায় ইসলাম গ্রহণের তারিখ:</span>
                                    <span class="text-dark fw-semibold"><?php echo sanitize($cert['declaration_date']); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-2 border-bottom">
                                    <span class="fw-bold text-muted d-block">দীক্ষাদানকারী ইমামের নাম:</span>
                                    <span class="text-dark fw-semibold"><?php echo sanitize($cert['imam_name']); ?> (<?php echo sanitize($cert['imam_title']); ?>)</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-2">
                                    <span class="fw-bold text-muted d-block">দীক্ষাদানকারী মসজিদ / প্রতিষ্ঠানের নাম:</span>
                                    <span class="text-dark fw-semibold"><?php echo sanitize($cert['institution_name']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Witnesses Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-users text-warning me-2"></i>সাক্ষীগণের বিবরণ</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <span class="fw-bold text-dark d-block mb-2">১ম সাক্ষী:</span>
                                    <span class="text-muted d-block">নাম: <strong class="text-dark"><?php echo sanitize($cert['witness1_name']); ?></strong></span>
                                    <span class="text-muted d-block">NID: <strong class="text-dark"><?php echo sanitize($cert['witness1_nid']); ?></strong></span>
                                    <?php if (!empty($cert['witness1_address'])): ?>
                                        <span class="text-muted d-block">ঠিকানা: <span class="small text-secondary"><?php echo sanitize($cert['witness1_address']); ?></span></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <span class="fw-bold text-dark d-block mb-2">২য় সাক্ষী:</span>
                                    <span class="text-muted d-block">নাম: <strong class="text-dark"><?php echo sanitize($cert['witness2_name']); ?></strong></span>
                                    <span class="text-muted d-block">NID: <strong class="text-dark"><?php echo sanitize($cert['witness2_nid']); ?></strong></span>
                                    <?php if (!empty($cert['witness2_address'])): ?>
                                        <span class="text-muted d-block">ঠিকানা: <span class="small text-secondary"><?php echo sanitize($cert['witness2_address']); ?></span></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <?php if (!empty($cert['notes'])): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-note-sticky text-warning me-2"></i>বিশেষ শর্তাবলী / মন্তব্য</h5>
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
                    <h5 class="fw-bold text-dark mb-3">সার্টিফিকেট মেটা ডাটা</h5>
                    <div class="p-3 bg-light rounded-3 mb-4">
                        <span class="text-muted small d-block uppercase fw-bold">সনদ নম্বর (Certificate No)</span>
                        <strong class="text-primary d-block fs-5"><?php echo sanitize($cert['certificate_no']); ?></strong>
                    </div>

                    <!-- QR Code verification panel -->
                    <div class="border-top pt-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-qrcode me-2 text-warning"></i>ভেরিফিকেশন কিউআর কোড</h6>
                        <div class="d-inline-block bg-white p-2 border rounded-3 mb-2 shadow-sm">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($cert['qr_code']); ?>" alt="Verification QR" width="150" height="150">
                        </div>
                        <p class="text-muted small mb-0">এই কিউআর কোডটি স্ক্যান করে নওমুসলিম নিবন্ধনের অনলাইন সত্যতা যাচাই করা যাবে।</p>
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
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>মুছে ফেলার নিশ্চিতকরণ</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4 text-center">
                        <i class="fa-solid fa-circle-exclamation text-danger fa-4x mb-3"></i>
                        <p class="lead fw-semibold text-dark mb-2">আপনি কি নিশ্চিত?</p>
                        <p class="text-muted mb-0 small">এই কাজটি বাতিল করা যাবে না। এটি চিরতরে ডেটাবেজ থেকে <strong><?php echo sanitize($cert['certificate_no']); ?></strong> নওমুসলিম রেকর্ডটি মুছে ফেলবে।</p>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল করুন</button>
                        <form action="view_new_muslim.php?id=<?php echo $cert['id']; ?>" method="POST" class="d-inline">
                            <!-- CSRF -->
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-danger px-4">হ্যাঁ, মুছে ফেলুন</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> ডিজিটাল নিকাহনামা রেজিস্ট্রি সিস্টেম। সর্বস্বত্ব সংরক্ষিত।</p>
            <p class="small text-muted mb-0">সিস্টেম সংস্করণ ২.০.০</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
