<?php
// dashboard.php

require_once 'app/controllers/NikahController.php';
require_once 'app/helpers/session.php';

// Route guards
require_login();

$controller = new NikahController();

// Handle AJAX search requests
if (isset($_GET['action']) && $_GET['action'] === 'search') {
    header('Content-Type: application/json');
    $query = $_GET['q'] ?? '';
    $results = $controller->handleSearch($query);
    echo json_encode($results);
    exit;
}

// Fetch dashboard statistics and recent data
$stats = $controller->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nikahnama Management System</title>
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
                        <a class="nav-link active" href="dashboard.php"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create.php"><i class="fa-solid fa-circle-plus me-1"></i> Create Nikah</a>
                    </li>
                    <li class="nav-item me-3">
                        <span class="navbar-text text-light opacity-75 ms-2">
                            <i class="fa-solid fa-user me-1 text-warning"></i> <?php echo sanitize($_SESSION['fullname']); ?> (<?php echo strtoupper($_SESSION['role']); ?>)
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

        <!-- Statistics Section -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase small fw-bold">Total Certificates</span>
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
                            <span class="text-muted text-uppercase small fw-bold">Today's Certificates</span>
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
                            <span class="text-muted text-uppercase small fw-bold">Monthly Certificates</span>
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
                    <input type="text" class="form-control border-0" id="dashboardSearch" placeholder="Search by Certificate No, Bride/Groom Name, Phone, Date, or NID...">
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="create.php" class="btn btn-primary-custom w-100 py-3 shadow-sm">
                    <i class="fa-solid fa-circle-plus me-2"></i>Create New Certificate
                </a>
            </div>
        </div>

        <!-- Recent Certificates Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-warning"></i>Recent Registrations</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Certificate No</th>
                            <th>Groom Name</th>
                            <th>Bride Name</th>
                            <th>Marriage Date</th>
                            <th>Mahr Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="searchResultsTable">
                        <?php if (empty($stats['recent'])): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary"></i>
                                    <p class="mb-0">No records found. Click "Create New Certificate" to add your first registration.</p>
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
                                            <span class="badge badge-paid">Paid</span>
                                        <?php elseif ($item['mahr_status'] === 'due'): ?>
                                            <span class="badge badge-due">Due</span>
                                        <?php else: ?>
                                            <span class="badge badge-partial">Partial</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="view.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fa-solid fa-eye me-1"></i>View
                                            </a>
                                            <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                <i class="fa-solid fa-pen me-1"></i>Edit
                                            </a>
                                            <a href="print.php?id=<?php echo $item['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-print me-1"></i>Print
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
