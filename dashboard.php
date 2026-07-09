<?php
// dashboard.php

require_once 'app/controllers/NikahController.php';
require_once 'app/helpers/session.php';

// Route guards
require_login();

$controller = new NikahController();

// Process User Approval / Rejection / Password Changes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'approve_user') {
        require_admin();
        $userId = $_POST['user_id'] ?? '';
        if ($userId) {
            $controller->approveUser($userId);
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'reject_user') {
        require_admin();
        $userId = $_POST['user_id'] ?? '';
        if ($userId) {
            $controller->rejectUser($userId);
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'change_own_password') {
        $controller->handleOwnPasswordChange(
            $_POST['current_password'] ?? '',
            $_POST['new_password'] ?? '',
            $_POST['confirm_password'] ?? ''
        );
    }

    if (isset($_POST['action']) && $_POST['action'] === 'admin_change_password') {
        require_admin();
        $controller->handleAdminPasswordChange(
            $_POST['user_id'] ?? '',
            $_POST['new_password'] ?? '',
            $_POST['confirm_password'] ?? ''
        );
    }
}

// Handle AJAX search requests for Nikah Certificates
if (isset($_GET['action']) && $_GET['action'] === 'search') {
    header('Content-Type: application/json');
    $query = $_GET['q'] ?? '';
    $results = $controller->handleSearch($query);
    echo json_encode($results);
    exit;
}

// Handle AJAX search requests for New Muslims
if (isset($_GET['action']) && $_GET['action'] === 'search_new_muslim') {
    header('Content-Type: application/json');
    $query = $_GET['q'] ?? '';
    $results = $controller->handleNewMuslimSearch($query);
    echo json_encode($results);
    exit;
}

// Fetch Nikahnama dashboard statistics and recent data
$stats = $controller->getDashboardStats();

// Fetch New Muslim statistics and recent data
$nm_stats = $controller->getNewMuslimDashboardStats();

// Fetch all registered users for Admin panel
$all_users = [];
if ($_SESSION['username'] === 'sourav.sanyal.dev@gmail.com' || $_SESSION['role'] === 'admin') {
    $all_users = $controller->getAllUsers();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ড্যাশবোর্ড - ডিজিটাল রেজিস্ট্রি সিস্টেম</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Firebase SDK Integration -->
    <script type="module" src="assets/js/firebase-init.js"></script>
    <style>
        /* Modern dashboard tabs styling */
        .dashboard-nav-tabs {
            border-bottom: 2px solid #E2E8F0;
            margin-bottom: 30px;
        }
        .dashboard-nav-tabs .nav-link {
            border: none;
            color: #4A5568;
            font-weight: 700;
            padding: 12px 24px;
            font-size: 1.05rem;
            position: relative;
            background: transparent;
            transition: color 0.2s;
        }
        .dashboard-nav-tabs .nav-link:hover {
            color: #FF8A00;
        }
        .dashboard-nav-tabs .nav-link.active {
            color: #FF8A00;
            background: transparent;
        }
        .dashboard-nav-tabs .nav-link.active::after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #FF8A00;
            border-radius: 3px;
        }
    </style>
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
                        <a class="nav-link active" href="dashboard.php"><i class="fa-solid fa-gauge me-1"></i> ড্যাশবোর্ড</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-circle-plus me-1"></i> নতুন নিবন্ধন
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="create.php"><i class="fa-solid fa-file-contract me-2 text-primary"></i>নতুন নিকাহনামা</a></li>
                            <li><a class="dropdown-item" href="create_new_muslim.php"><i class="fa-solid fa-user-check me-2 text-success"></i>নতুন নওমুসলিম</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link dropdown-toggle text-light opacity-90 ms-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user text-warning me-1"></i> <?php echo sanitize($_SESSION['fullname']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#changeOwnPasswordModal">
                                    <i class="fa-solid fa-key me-2 text-warning"></i>পাসওয়ার্ড পরিবর্তন
                                </button>
                            </li>
                        </ul>
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

    <!-- Main Content -->
    <div class="container my-5">
        
        <!-- Toast Alerts -->
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

        <!-- Dashboard Module Navigation Tabs -->
        <ul class="nav nav-tabs dashboard-nav-tabs" id="moduleTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="nikah-tab" data-bs-toggle="tab" data-bs-target="#nikahPane" type="button" role="tab" aria-controls="nikahPane" aria-selected="true">
                    <i class="fa-solid fa-file-signature me-2"></i>নিকাহনামা ও কাবিননামা রেজিস্ট্রি
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="newmuslim-tab" data-bs-toggle="tab" data-bs-target="#newmuslimPane" type="button" role="tab" aria-controls="newmuslimPane" aria-selected="false">
                    <i class="fa-solid fa-user-check me-2"></i>নওমুসলিম নিবন্ধন ডাটাবেজ
                </button>
            </li>
            <?php if ($_SESSION['username'] === 'sourav.sanyal.dev@gmail.com' || $_SESSION['role'] === 'admin'): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#usersPane" type="button" role="tab" aria-controls="usersPane" aria-selected="false">
                    <i class="fa-solid fa-users-gear me-2"></i>নিবন্ধক কর্মকর্তা অনুমোদন
                </button>
            </li>
            <?php endif; ?>
        </ul>

        <div class="tab-content" id="moduleTabsContent">
            
            <!-- MODULE 1: NIKAHNAMA REGISTRY PANE -->
            <div class="tab-pane fade show active" id="nikahPane" role="tabpanel" aria-labelledby="nikah-tab">
                <!-- Statistics Section -->
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase small fw-bold">মোট নিকাহনামা</span>
                                    <div class="stat-val"><?php echo $stats['total']; ?></div>
                                </div>
                                <div class="icon-wrapper">
                                    <i class="fa-solid fa-book-open"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase small fw-bold">আজকের নিবন্ধন</span>
                                    <div class="stat-val text-warning"><?php echo $stats['today']; ?></div>
                                </div>
                                <div class="icon-wrapper text-warning">
                                    <i class="fa-solid fa-calendar-day"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase small fw-bold">এই মাসের নিবন্ধন</span>
                                    <div class="stat-val text-success"><?php echo $stats['month']; ?></div>
                                </div>
                                <div class="icon-wrapper text-success">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Bar and Action Bar -->
                <div class="row g-4 mb-4">
                    <div class="col-md-8">
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" class="form-control border-0" id="dashboardSearch" placeholder="সার্টিফিকেট নম্বর, বর/কনের নাম, মোবাইল নম্বর, NID অথবা তারিখ দিয়ে খুঁজুন...">
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="create.php" class="btn btn-primary-custom w-100 py-3 shadow-sm">
                            <i class="fa-solid fa-circle-plus me-2"></i>নতুন নিকাহনামা তৈরি করুন
                        </a>
                    </div>
                </div>

                <!-- Recent Certificates Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-warning"></i>সাম্প্রতিক বিবাহ নিবন্ধনসমূহ</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>সার্টিফিকেট নম্বর</th>
                                    <th>বরের নাম</th>
                                    <th>কনের নাম</th>
                                    <th> বিবাহের তারিখ</th>
                                    <th>দেনমোহর</th>
                                    <th>দেনমোহরের অবস্থা</th>
                                    <th class="text-end">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody id="searchResultsTable">
                                <?php if (empty($stats['recent'])): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary"></i>
                                            <p class="mb-0">কোনো নিবন্ধন পাওয়া যায়নি। শুরু করতে "নতুন নিকাহনামা তৈরি করুন" বোতামে ক্লিক করুন।</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($stats['recent'] as $item): ?>
                                        <tr>
                                            <td><strong class="text-primary"><?php echo sanitize($item['certificate_no']); ?></strong></td>
                                            <td><?php echo sanitize($item['groom_name']); ?></td>
                                            <td><?php echo sanitize($item['bride_name']); ?></td>
                                            <td><?php echo sanitize($item['marriage_date']); ?></td>
                                            <td><?php echo sanitize($item['mahr_amount']) . ' ' . sanitize($item['currency']); ?></td>
                                            <td>
                                                <?php if ($item['mahr_status'] === 'paid'): ?>
                                                    <span class="badge badge-paid">পরিশোধিত</span>
                                                <?php elseif ($item['mahr_status'] === 'due'): ?>
                                                    <span class="badge badge-due">বকেয়া</span>
                                                <?php else: ?>
                                                    <span class="badge badge-partial">আংশিক</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="view.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fa-solid fa-eye me-1"></i>দেখুন
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                        <i class="fa-solid fa-pen me-1"></i>সম্পাদনা
                                                    </a>
                                                    <a href="print.php?id=<?php echo $item['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa-solid fa-print me-1"></i>প্রিন্ট
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- MODULE 2: NEW MUSLIM REGISTRY PANE -->
            <div class="tab-pane fade" id="newmuslimPane" role="tabpanel" aria-labelledby="newmuslim-tab">
                <!-- Statistics Section -->
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase small fw-bold">মোট নওমুসলিম নিবন্ধন</span>
                                    <div class="stat-val text-success"><?php echo $nm_stats['total']; ?></div>
                                </div>
                                <div class="icon-wrapper text-success">
                                    <i class="fa-solid fa-user-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase small fw-bold">আজকের নিবন্ধন</span>
                                    <div class="stat-val text-warning"><?php echo $nm_stats['today']; ?></div>
                                </div>
                                <div class="icon-wrapper text-warning">
                                    <i class="fa-solid fa-calendar-day"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase small fw-bold">এই মাসের নিবন্ধন</span>
                                    <div class="stat-val text-info"><?php echo $nm_stats['month']; ?></div>
                                </div>
                                <div class="icon-wrapper text-info">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Bar and Action Bar -->
                <div class="row g-4 mb-4">
                    <div class="col-md-8">
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" class="form-control border-0" id="newMuslimSearch" placeholder="সার্টিফিকেট নম্বর, নতুন/পূর্বের নাম, মোবাইল নম্বর, NID অথবা ইমামের নাম দিয়ে খুঁজুন...">
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="create_new_muslim.php" class="btn btn-success w-100 py-3 shadow-sm" style="font-weight: 700; border-radius: 8px;">
                            <i class="fa-solid fa-user-plus me-2"></i>নতুন নওমুসলিম নিবন্ধন করুন
                        </a>
                    </div>
                </div>

                <!-- Recent New Muslims Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-success"></i>সাম্প্রতিক নওমুসলিম নিবন্ধনসমূহ</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>সার্টিফিকেট নম্বর</th>
                                    <th>নতুন নাম</th>
                                    <th>পূর্বের নাম ও ধর্ম</th>
                                    <th>ইসলাম গ্রহণের তারিখ</th>
                                    <th>মোবাইল নম্বর</th>
                                    <th>দীক্ষাদানকারী ইমাম</th>
                                    <th class="text-end">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody id="newMuslimSearchResultsTable">
                                <?php if (empty($nm_stats['recent'])): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-user-check fa-3x mb-3 text-secondary"></i>
                                            <p class="mb-0">কোনো নওমুসলিম নিবন্ধন পাওয়া যায়নি। শুরু করতে "নতুন নওমুসলিম নিবন্ধন করুন" বোতামে ক্লিক করুন।</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($nm_stats['recent'] as $item): ?>
                                        <tr>
                                            <td><strong class="text-success"><?php echo sanitize($item['certificate_no']); ?></strong></td>
                                            <td><strong class="text-dark"><?php echo sanitize($item['new_name']); ?></strong></td>
                                            <td>
                                                <span class="text-danger"><?php echo sanitize($item['previous_name']); ?></span><br>
                                                <span class="small text-muted">(<?php echo sanitize($item['previous_religion']); ?>)</span>
                                            </td>
                                            <td><?php echo sanitize($item['declaration_date']); ?></td>
                                            <td><?php echo sanitize($item['phone_no']); ?></td>
                                            <td><?php echo sanitize($item['imam_name']); ?></td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="view_new_muslim.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fa-solid fa-eye me-1"></i>দেখুন
                                                    </a>
                                                    <a href="edit_new_muslim.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                        <i class="fa-solid fa-pen me-1"></i>সম্পাদনা
                                                    </a>
                                                    <a href="print_new_muslim.php?id=<?php echo $item['id']; ?>&type=certificate" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa-solid fa-print me-1"></i>সনদ
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($_SESSION['username'] === 'sourav.sanyal.dev@gmail.com' || $_SESSION['role'] === 'admin'): ?>
            <!-- MODULE 3: USER APPROVAL PANE -->
            <div class="tab-pane fade" id="usersPane" role="tabpanel" aria-labelledby="users-tab">
                <!-- Pending Approvals Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-user-clock me-2 text-warning"></i>অনুমোদনের জন্য অপেক্ষারত কর্মকর্তাগণ</h5>
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill small fw-bold">
                            <?php 
                                $pending_count = 0;
                                foreach ($all_users as $u) {
                                    if (isset($u['approved']) && $u['approved'] === false && strcasecmp($u['username'], 'sourav.sanyal.dev@gmail.com') !== 0) {
                                        $pending_count++;
                                    }
                                }
                                echo $pending_count;
                            ?> জন পেন্ডিং
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>নাম</th>
                                    <th>ইমেইল (ইউজারনেম)</th>
                                    <th>লাইসেন্স নম্বর</th>
                                    <th>মোবাইল নম্বর</th>
                                    <th>কার্যালয়ের ঠিকানা</th>
                                    <th class="text-end">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pending_count === 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-clipboard-check fa-3x mb-3 text-secondary"></i>
                                            <p class="mb-0">অনুমোদনের জন্য কোনো নতুন আবেদন পেন্ডিং নেই।</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($all_users as $u): ?>
                                        <?php if (isset($u['approved']) && $u['approved'] === false && strcasecmp($u['username'], 'sourav.sanyal.dev@gmail.com') !== 0): ?>
                                            <tr>
                                                <td><strong class="text-dark"><?php echo sanitize($u['fullname']); ?></strong></td>
                                                <td><?php echo sanitize($u['username']); ?></td>
                                                <td><span class="badge bg-light text-dark border font-monospace"><?php echo sanitize($u['license_no'] ?? 'N/A'); ?></span></td>
                                                <td><?php echo sanitize($u['phone'] ?? 'N/A'); ?></td>
                                                <td class="small text-secondary"><?php echo nl2br(sanitize($u['address'] ?? 'N/A')); ?></td>
                                                <td class="text-end">
                                                    <form action="dashboard.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="approve_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-success me-1">
                                                            <i class="fa-solid fa-check me-1"></i>অনুমোদন
                                                        </button>
                                                    </form>
                                                    <form action="dashboard.php" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই আবেদনটি বাতিল করতে চান?');">
                                                        <input type="hidden" name="action" value="reject_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fa-solid fa-xmark me-1"></i>বাতিল
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Approved Officers Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-user-check me-2 text-success"></i>অনুমোদিত নিবন্ধক কর্মকর্তাগণ</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>নাম</th>
                                    <th>ইমেইল (ইউজারনেম)</th>
                                    <th>লাইসেন্স নম্বর</th>
                                    <th>মোবাইল নম্বর</th>
                                    <th>পদবী (রোল)</th>
                                    <th class="text-end">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $approved_count = 0;
                                    foreach ($all_users as $u) {
                                        if ((isset($u['approved']) && $u['approved'] === true) || strcasecmp($u['username'], 'sourav.sanyal.dev@gmail.com') === 0) {
                                            $approved_count++;
                                        }
                                    }
                                ?>
                                <?php if ($approved_count === 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">কোনো অনুমোদিত কর্মকর্তা নেই।</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($all_users as $u): ?>
                                        <?php if ((isset($u['approved']) && $u['approved'] === true) || strcasecmp($u['username'], 'sourav.sanyal.dev@gmail.com') === 0): ?>
                                            <tr>
                                                <td><strong class="text-dark"><?php echo sanitize($u['fullname']); ?></strong></td>
                                                <td><?php echo sanitize($u['username']); ?></td>
                                                <td><span class="badge bg-light text-dark border font-monospace"><?php echo sanitize($u['license_no'] ?? 'N/A'); ?></span></td>
                                                <td><?php echo sanitize($u['phone'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php if (strcasecmp($u['username'], 'sourav.sanyal.dev@gmail.com') === 0 || strcasecmp($u['role'] ?? '', 'admin') === 0): ?>
                                                        <span class="badge bg-danger px-2.5 py-1.5">প্রধান এডমিন (Admin)</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary px-2.5 py-1.5">নিবন্ধক কর্মকর্তা</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <?php if (strcasecmp($u['username'], 'sourav.sanyal.dev@gmail.com') !== 0): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning me-1" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#adminChangePasswordModal" 
                                                                data-user-id="<?php echo $u['id']; ?>" 
                                                                data-user-name="<?php echo sanitize($u['fullname']); ?>">
                                                            <i class="fa-solid fa-key me-1"></i>পাসওয়ার্ড
                                                        </button>
                                                        <form action="dashboard.php" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই কর্মকর্তার অ্যাকাউন্টটি নিষ্ক্রিয়/মুছে ফেলতে চান?');">
                                                            <input type="hidden" name="action" value="reject_user">
                                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fa-solid fa-trash me-1"></i>মুছে ফেলুন
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted small italic">সুরক্ষিত</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

    </div>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> ডিজিটাল নিকাহনামা রেজিস্ট্রি সিস্টেম। সর্বস্বত্ব সংরক্ষিত।</p>
            <p class="small text-muted mb-0">সিস্টেম সংস্করণ ২.০.০</p>
        </div>
    </footer>

    <!-- Modal for changing own password -->
    <div class="modal fade" id="changeOwnPasswordModal" tabindex="-1" aria-labelledby="changeOwnPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="changeOwnPasswordModalLabel"><i class="fa-solid fa-key me-2 text-warning"></i>পাসওয়ার্ড পরিবর্তন করুন</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="dashboard.php" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="change_own_password">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-bold text-dark">বর্তমান পাসওয়ার্ড</label>
                            <input type="password" class="form-control py-2" id="current_password" name="current_password" required placeholder="বর্তমান পাসওয়ার্ডটি লিখুন">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-bold text-dark">নতুন পাসওয়ার্ড</label>
                            <input type="password" class="form-control py-2" id="new_password" name="new_password" required minlength="6" placeholder="কমপক্ষে ৬ অক্ষরের নতুন পাসওয়ার্ড">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label fw-bold text-dark">নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                            <input type="password" class="form-control py-2" id="confirm_password" name="confirm_password" required minlength="6" placeholder="নতুন পাসওয়ার্ডটি আবার লিখুন">
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">বন্ধ করুন</button>
                        <button type="submit" class="btn btn-primary-custom px-4 py-2">সংরক্ষণ করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for admin changing other user's password -->
    <div class="modal fade" id="adminChangePasswordModal" tabindex="-1" aria-labelledby="adminChangePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="adminChangePasswordModalLabel"><i class="fa-solid fa-user-shield me-2 text-warning"></i>পাসওয়ার্ড পরিবর্তন</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="dashboard.php" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="admin_change_password">
                        <input type="hidden" name="user_id" id="admin_change_user_id">
                        
                        <div class="mb-3">
                            <label for="admin_new_password" class="form-label fw-bold text-dark">নতুন পাসওয়ার্ড</label>
                            <input type="password" class="form-control py-2" id="admin_new_password" name="new_password" required minlength="6" placeholder="কমপক্ষে ৬ অক্ষরের নতুন পাসওয়ার্ড">
                        </div>
                        <div class="mb-3">
                            <label for="admin_confirm_password" class="form-label fw-bold text-dark">নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                            <input type="password" class="form-control py-2" id="admin_confirm_password" name="confirm_password" required minlength="6" placeholder="নতুন পাসওয়ার্ডটি আবার লিখুন">
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">বন্ধ করুন</button>
                        <button type="submit" class="btn btn-primary-custom px-4 py-2">সংরক্ষণ করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const adminModal = document.getElementById('adminChangePasswordModal');
            if (adminModal) {
                adminModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const userId = button.getAttribute('data-user-id');
                    const userName = button.getAttribute('data-user-name');
                    
                    const modalTitle = adminModal.querySelector('.modal-title');
                    const userIdInput = adminModal.querySelector('#admin_change_user_id');
                    
                    modalTitle.innerHTML = '<i class="fa-solid fa-user-shield me-2 text-warning"></i>' + userName + ' - এর পাসওয়ার্ড পরিবর্তন';
                    userIdInput.value = userId;
                });
            }
        });
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
