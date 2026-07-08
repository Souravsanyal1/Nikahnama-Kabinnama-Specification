<?php
// index.php

require_once 'app/helpers/session.php';

// If logged in, redirect to dashboard
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Nikahnama Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <!-- Header Section -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fa-solid fa-mosque me-2"></i>NIKAH<span>NAMA</span>
            </a>
            <div class="ms-auto">
                <a href="login.php" class="btn btn-primary-custom">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Officer Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Banner / Verification Area -->
    <main class="container my-5 py-4">
        <div class="row align-items-center justify-content-center g-5">
            <div class="col-lg-6">
                <div class="pe-0 pe-lg-4 text-center text-lg-start">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-3 fw-bold">SECURE & LEGAL</span>
                    <h1 class="display-5 fw-bold mb-3 text-dark">Islamic Marriage Certificate Registry</h1>
                    <p class="lead text-secondary mb-4">
                        A state-of-the-art management system to record, search, print, and verify Nikahnama/Kabinnama certificates with integrated QR code verification.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                        <a href="#verifySection" class="btn btn-outline-custom btn-lg">Verify Certificate</a>
                        <a href="login.php" class="btn btn-primary-custom btn-lg">Create Nikahnama</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 bg-dark text-white text-center">
                        <i class="fa-solid fa-certificate text-warning fa-4x my-3"></i>
                        <h4 class="fw-bold mb-3">Official Verification Portal</h4>
                        <p class="text-light opacity-75 small">
                            Scan the QR code printed on the bottom of the certificate, or enter the unique Certificate Number below to verify legitimacy.
                        </p>
                    </div>
                    <div class="card-body p-4 bg-white" id="verifySection">
                        <form action="verify.php" method="GET">
                            <div class="mb-4">
                                <label for="cert_no" class="form-label fw-bold">Certificate Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="text" class="form-control" id="cert_no" name="cert_no" placeholder="e.g. NIK-YYYYMMDD-XXXX" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary-custom w-100 py-3">
                                <i class="fa-solid fa-shield-check me-2"></i>Verify Authenticity
                            </button>
                        </form>
                    </div>
                </div>
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
