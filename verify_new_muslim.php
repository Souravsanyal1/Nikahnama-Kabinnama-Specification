<?php
// verify_new_muslim.php

require_once 'app/controllers/NikahController.php';
require_once 'app/helpers/session.php';

$controller = new NikahController();
$cert = null;
$error = null;
$searched = false;

if (isset($_GET['cert_no']) && !empty($_GET['cert_no'])) {
    $searched = true;
    $cert_no = trim($_GET['cert_no']);
    $cert = $controller->handleNewMuslimVerify($cert_no);
    if (!$cert) {
        $error = "প্রদানকৃত সার্টিফিকেট নম্বরটি আমাদের ডাটাবেজে পাওয়া যায়নি। অনুগ্রহ করে সঠিক নম্বরটি লিখুন।";
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নওমুসলিম নিবন্ধন যাচাই - ডিজিটাল রেজিস্ট্রি</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Firebase SDK Integration -->
    <script type="module" src="assets/js/firebase-init.js"></script>
</head>
<body class="bg-light">

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fa-solid fa-mosque me-2"></i>নিকাহ<span>নামা</span>
            </a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fa-solid fa-house me-1"></i> প্রধান পাতা
                </a>
                <a href="login.php" class="btn btn-primary-custom btn-sm">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> কর্মকর্তা লগইন
                </a>
            </div>
        </div>
    </nav>

    <!-- Content Container -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Verification status header -->
                <div class="text-center mb-5">
                    <i class="fa-solid fa-shield-halved text-warning fa-4x mb-3"></i>
                    <h2 class="fw-bold text-dark">নওমুসলিম ডিজিটাল সত্যতা যাচাইকরণ পোর্টাল</h2>
                    <p class="text-secondary small">ইসলাম গ্রহণের প্রমাণপত্র এবং ঘোষণার বৈধতা পরীক্ষা করুন</p>
                </div>

                <!-- Input search area if needed -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                    <form action="verify_new_muslim.php" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label for="cert_no" class="form-label fw-bold text-dark">সার্টিফিকেট নম্বর দিয়ে অনুসন্ধান করুন</label>
                            <input type="text" class="form-control form-control-lg" id="cert_no" name="cert_no" value="<?php echo sanitize($_GET['cert_no'] ?? ''); ?>" placeholder="যেমন: NMC-YYYYMMDD-XXXX" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary-custom btn-lg w-100 py-2.5">
                                <i class="fa-solid fa-circle-check me-2"></i>যাচাই করুন
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Search Results panel -->
                <?php if ($searched): ?>
                    <?php if ($cert): ?>
                        <div class="alert alert-success border-0 shadow-sm rounded-4 p-4 d-flex align-items-center mb-4" role="alert">
                            <i class="fa-solid fa-circle-check text-success fa-3x me-4"></i>
                            <div>
                                <h5 class="alert-heading fw-bold mb-1">নিবন্ধনটি সঠিক ও বৈধ!</h5>
                                <p class="mb-0 small text-muted">নওমুসলিম ডিজিটাল রেজিস্ট্রি ডাটাবেজে এই সার্টিফিকেট নম্বরটি নিবন্ধিত পাওয়া গেছে।</p>
                            </div>
                        </div>

                        <!-- Certificate detail card -->
                        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5">
                            <div class="card-header bg-dark text-white text-center py-4">
                                <h4 class="mb-1 fw-bold">নওমুসলিম ভেরিফিকেশন রেকর্ড</h4>
                                <span class="badge bg-warning text-dark px-3 py-1.5 fs-7 rounded-pill">সার্টিফিকেট নম্বর: <?php echo sanitize($cert['certificate_no']); ?></span>
                            </div>
                            <div class="card-body p-4 bg-white">
                                <div class="row g-4">
                                    
                                    <!-- Party Summaries -->
                                    <div class="col-md-6 border-end">
                                        <h5 class="fw-bold border-bottom pb-2 mb-3 text-dark"><i class="fa-solid fa-user-check text-success me-2"></i>ইসলামী নতুন তথ্য</h5>
                                        <p class="mb-2">নতুন নাম: <strong class="text-success"><?php echo sanitize($cert['new_name']); ?></strong></p>
                                        <p class="mb-2">পিতার নাম: <span><?php echo sanitize($cert['father_name']); ?></span></p>
                                        <p class="mb-2">মাতার নাম: <span><?php echo sanitize($cert['mother_name']); ?></span></p>
                                        <p class="mb-0">ঠিকানা: <span class="text-secondary small"><?php echo nl2br(sanitize($cert['address'])); ?></span></p>
                                    </div>

                                    <div class="col-md-6">
                                        <h5 class="fw-bold border-bottom pb-2 mb-3 text-dark"><i class="fa-solid fa-user-xmark text-danger me-2"></i>পূর্বের বিবরণ</h5>
                                        <p class="mb-2">পূর্বের নাম: <strong class="text-danger"><?php echo sanitize($cert['previous_name']); ?></strong></p>
                                        <p class="mb-2">পূর্বের ধর্ম: <span><?php echo sanitize($cert['previous_religion']); ?></span></p>
                                        <p class="mb-2">জন্ম তারিখ: <span><?php echo sanitize($cert['date_of_birth']); ?></span></p>
                                        <p class="mb-2">NID নম্বর: <span><?php echo sanitize($cert['nid_no'] ?? 'N/A'); ?></span></p>
                                    </div>

                                    <!-- Embracing Details -->
                                    <div class="col-12 mt-4 pt-3 border-top">
                                        <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-mosque text-warning me-2"></i>ইসলাম গ্রহণ ও দীক্ষার বিবরণ</h5>
                                        <div class="row g-3 small bg-light p-3 rounded-3">
                                            <div class="col-md-6">
                                                <strong>ইসলাম গ্রহণের তারিখ:</strong> <?php echo sanitize($cert['declaration_date']); ?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>দীক্ষাদানকারী ইমাম:</strong> <?php echo sanitize($cert['imam_name']); ?> (<?php echo sanitize($cert['imam_title']); ?>)
                                            </div>
                                            <div class="col-12">
                                                <strong>দীক্ষাদানকারী প্রতিষ্ঠান/মসজিদ:</strong> <?php echo sanitize($cert['institution_name']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Error panel -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 text-center mb-5">
                            <i class="fa-solid fa-circle-xmark text-danger fa-4x mb-3"></i>
                            <h4 class="fw-bold text-dark mb-2">সত্যতা যাচাই করা যায়নি!</h4>
                            <p class="text-danger small mb-0"><?php echo $error; ?></p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

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
</body>
</html>
