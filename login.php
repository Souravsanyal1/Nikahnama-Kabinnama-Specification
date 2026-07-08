<?php
// login.php

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
        if ($auth->login($_POST['username'], $_POST['password'])) {
            header("Location: dashboard.php");
            exit;
        } else {
            $error_msg = flash('error');
        }
    } else {
        $error_msg = 'CSRF verification failed.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Login - Nikahnama Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Firebase SDK Integration -->
    <script type="module" src="assets/js/firebase-init.js"></script>
</head>
<body class="bg-light">

    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h2><i class="fa-solid fa-mosque me-2"></i>NIKAH<span>NAMA</span></h2>
                    <p class="mb-0 text-light opacity-75 small">Officer Authentication Portal</p>
                </div>
                <div class="card-body p-4 bg-white">
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo $error_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form action="login.php" method="POST">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="border-color: #E2E8F0; color: #6C757D;">
                                    <i class="fa-solid fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 py-2.5">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Authenticate
                        </button>
                        
                        <div class="text-center mt-4">
                            <a href="index.php" class="text-muted text-decoration-none small">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back to Public Page
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>
