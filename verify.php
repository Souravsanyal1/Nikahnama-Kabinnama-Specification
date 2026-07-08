<?php
// verify.php

require_once 'app/controllers/NikahController.php';
require_once 'app/helpers/session.php';

$controller = new NikahController();
$cert = null;
$searched = false;
$cert_no = '';

if (isset($_GET['cert_no']) && !empty($_GET['cert_no'])) {
    $cert_no = sanitize($_GET['cert_no']);
    $cert = $controller->verify($cert_no);
    $searched = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification - Nikahnama System</title>
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

    <!-- Header Section -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fa-solid fa-mosque me-2"></i>NIKAH<span>NAMA</span>
            </a>
            <div class="ms-auto">
                <?php if (is_logged_in()): ?>
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">
                        <i class="fa-solid fa-gauge me-1"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary-custom btn-sm">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Officer Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-5 py-2">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Verification Search Form -->
                <div class="search-card mb-5">
                    <h3 class="fw-bold mb-3"><i class="fa-solid fa-shield-halved text-warning me-2"></i>Certificate Verification</h3>
                    <p class="text-light opacity-75 small">Enter the Certificate Number printed on the marriage registry document to check its legal validity.</p>
                    
                    <form action="verify.php" method="GET" class="mt-4">
                        <div class="input-group input-group-lg shadow">
                            <input type="text" class="form-control border-0" name="cert_no" value="<?php echo sanitize($cert_no); ?>" placeholder="e.g. NIK-YYYYMMDD-XXXX" required>
                            <button class="btn btn-primary-custom px-4" type="submit">
                                <i class="fa-solid fa-search me-1"></i> Verify
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Verification Result Display -->
                <?php if ($searched): ?>
                    <?php if ($cert): ?>
                        <!-- SUCCESS CARD (GENUINE RECORD) -->
                        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5">
                            <div class="bg-success text-white py-4 px-4 text-center">
                                <i class="fa-solid fa-circle-check fa-4x mb-2"></i>
                                <h4 class="fw-bold mb-0">VERIFIED GENUINE RECORD</h4>
                                <p class="mb-0 opacity-75 small mt-1">This marriage record is officially registered in the Central Nikahnama Database.</p>
                            </div>
                            
                            <div class="card-body p-4 bg-white">
                                <div class="row g-3 border-bottom pb-4 mb-4">
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block small">Certificate Number:</span>
                                        <strong class="text-primary fs-5"><?php echo sanitize($cert['certificate_no']); ?></strong>
                                    </div>
                                    <div class="col-sm-6 text-sm-end">
                                        <span class="text-muted d-block small">Registration Date:</span>
                                        <strong class="text-dark"><?php echo date('F d, Y', strtotime($cert['marriage_date'])); ?></strong>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <!-- Groom -->
                                    <div class="col-md-6 border-end">
                                        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-user-tie text-success me-2"></i>Bridegroom</h5>
                                        <table class="table table-sm table-borderless small mb-0">
                                            <tr>
                                                <td class="text-muted fw-bold py-1 w-35">Full Name:</td>
                                                <td class="text-dark fw-semibold py-1"><?php echo sanitize($cert['groom_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-bold py-1">Father:</td>
                                                <td class="py-1"><?php echo sanitize($cert['groom_father']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-bold py-1">NID (Last 4):</td>
                                                <td class="py-1"><?php echo !empty($cert['groom_nid']) ? '***' . substr($cert['groom_nid'], -4) : 'N/A'; ?></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Bride -->
                                    <div class="col-md-6">
                                        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-user-dress text-success me-2"></i>Bride</h5>
                                        <table class="table table-sm table-borderless small mb-0">
                                            <tr>
                                                <td class="text-muted fw-bold py-1 w-35">Full Name:</td>
                                                <td class="text-dark fw-semibold py-1"><?php echo sanitize($cert['bride_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-bold py-1">Father:</td>
                                                <td class="py-1"><?php echo sanitize($cert['bride_father']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-bold py-1">NID (Last 4):</td>
                                                <td class="py-1"><?php echo !empty($cert['bride_nid']) ? '***' . substr($cert['bride_nid'], -4) : 'N/A'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <!-- Marriage Details -->
                                    <div class="col-12 border-top pt-4">
                                        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-circle-info text-success me-2"></i>Solemnization & Registrar</h5>
                                        <div class="row g-3 small">
                                            <div class="col-sm-6">
                                                <span class="text-muted d-block">Marriage Place:</span>
                                                <span class="fw-semibold text-dark"><?php echo sanitize($cert['marriage_place']); ?></span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="text-muted d-block">Dower (Mahr) Amount:</span>
                                                <span class="fw-bold text-dark"><?php echo number_format($cert['mahr_amount'], 2) . ' ' . sanitize($cert['currency']); ?> (<?php echo strtoupper($cert['mahr_status']); ?>)</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="text-muted d-block">Registrar (Kazi):</span>
                                                <span class="fw-semibold text-dark"><?php echo sanitize($cert['registrar_name']); ?></span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="text-muted d-block">Registrar License:</span>
                                                <span class="fw-semibold text-dark"><?php echo sanitize($cert['registrar_license']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- FAILURE CARD (INVALID / NOT REGISTERED) -->
                        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5">
                            <div class="bg-danger text-white py-5 px-4 text-center">
                                <i class="fa-solid fa-circle-xmark fa-4x mb-3 animate__animated animate__shakeX"></i>
                                <h4 class="fw-bold mb-1">RECORD NOT FOUND</h4>
                                <p class="mb-0 opacity-75 small">This Certificate Number is either invalid, unregistered, or has been revoked.</p>
                            </div>
                            <div class="card-body p-4 bg-white text-center">
                                <p class="text-muted mb-0 small">Please double-check the characters (case sensitive) and try again. For further verification, contact the local Nikah Registrar office.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>
    </main>

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
