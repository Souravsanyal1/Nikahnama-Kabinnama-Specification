<?php
// register.php

require_once 'app/controllers/AuthController.php';
require_once 'app/helpers/session.php';

// Redirect to dashboard if already logged in
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (isset($_POST['csrf_token']) && validate_csrf($_POST['csrf_token'])) {
        $auth = new AuthController();
        $success = $auth->register(
            $_POST['fullname'] ?? '',
            $_POST['email'] ?? '',
            $_POST['password'] ?? '',
            $_POST['license_no'] ?? '',
            $_POST['phone'] ?? '',
            $_POST['address'] ?? ''
        );
        if ($success) {
            header("Location: login.php");
            exit;
        } else {
            $error_msg = flash('error');
        }
    } else {
        $error_msg = 'CSRF ভেরিফিকেশন ব্যর্থ হয়েছে।';
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>কর্মকর্তা নিবন্ধন আবেদন - ডিজিটাল নিকাহনামা রেজিস্ট্রি</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Firebase SDK Integration -->
    <script type="module" src="assets/js/firebase-init.js"></script>
    <style>
        .register-container {
            max-width: 550px;
            margin: 50px auto;
        }
        .register-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .register-header h2 {
            font-weight: 800;
            margin-bottom: 5px;
            color: #fff;
        }
        .register-header h2 span {
            color: var(--primary-color);
        }
    </style>
</head>
<body class="bg-light">

    <div class="container">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <h2><i class="fa-solid fa-mosque me-2"></i>নিকাহ<span>নামা</span></h2>
                    <p class="mb-0 text-light opacity-75 small">নতুন কর্মকর্তা নিবন্ধন আবেদন পোর্টাল</p>
                </div>
                <div class="card-body p-4 bg-white">
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo $error_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form action="register.php" method="POST">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        
                        <div class="mb-3">
                            <label for="fullname" class="form-label text-dark fw-bold">পূর্ণ নাম</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user-tie text-muted"></i></span>
                                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="নাম লিখুন" required value="<?php echo isset($_POST['fullname']) ? sanitize($_POST['fullname']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label text-dark fw-bold">ইমেইল এড্রেস</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="ইমেইল এড্রেস লিখুন" required value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>">
                            </div>
                            <div class="form-text text-muted small">এটি আপনার লগইন ইউজারনেম হিসেবে ব্যবহৃত হবে।</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label text-dark fw-bold">পাসওয়ার্ড</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="পাসওয়ার্ড লিখুন" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="border-color: #E2E8F0; color: #6C757D;">
                                    <i class="fa-solid fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="license_no" class="form-label text-dark fw-bold">কাজী লাইসেন্স নম্বর</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-id-card text-muted"></i></span>
                                <input type="text" class="form-control" id="license_no" name="license_no" placeholder="যেমন: LIC-2026-XXXX" required value="<?php echo isset($_POST['license_no']) ? sanitize($_POST['license_no']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label text-dark fw-bold">মোবাইল নম্বর</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-phone text-muted"></i></span>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="যেমন: 01xxxxxxxxx" required value="<?php echo isset($_POST['phone']) ? sanitize($_POST['phone']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="address" class="form-label text-dark fw-bold">কার্যালয়ের ঠিকানা</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-building text-muted"></i></span>
                                <textarea class="form-control" id="address" name="address" rows="3" placeholder="সম্পূর্ণ ঠিকানা লিখুন" required><?php echo isset($_POST['address']) ? sanitize($_POST['address']) : ''; ?></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 py-2.5">
                            <i class="fa-solid fa-user-plus me-2"></i>নিবন্ধনের আবেদন করুন
                        </button>
                        
                        <div class="text-center mt-3">
                            <span class="text-muted small">ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="login.php" class="text-primary fw-bold text-decoration-none">লগইন করুন</a></span>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="index.php" class="text-muted text-decoration-none small">
                                <i class="fa-solid fa-arrow-left me-1"></i> প্রধান পাতায় ফিরে যান
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Password toggle script -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
    </script>
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
