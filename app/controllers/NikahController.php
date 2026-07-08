<?php
// app/controllers/NikahController.php

require_once __DIR__ . '/../models/Nikahnama.php';
require_once __DIR__ . '/../helpers/session.php';

class NikahController {
    private $model;

    public function __construct() {
        $this->model = new Nikahnama();
    }

    /**
     * Get statistics for dashboard
     */
    public function getDashboardStats() {
        return [
            'total' => $this->model->countAll(),
            'today' => $this->model->countToday(),
            'month' => $this->model->countThisMonth(),
            'recent' => $this->model->getRecent(5)
        ];
    }

    /**
     * View a single certificate by ID
     */
    public function show($id) {
        $cert = $this->model->getById($id);
        if (!$cert) {
            flash('error', 'Certificate not found.');
            header("Location: dashboard.php");
            exit;
        }
        return $cert;
    }

    /**
     * Process creation form submission
     */
    public function handleCreate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Validate CSRF
        if (!isset($_POST['csrf_token']) || !validate_csrf($_POST['csrf_token'])) {
            flash('error', 'CSRF token validation failed.');
            return;
        }

        // Clean & sanitize input
        $data = sanitize($_POST);

        // Validation Errors collector
        $errors = $this->validateFormData($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST; // Preserve input
            flash('error', 'Please correct the highlighted errors.');
            return;
        }

        // Generate Certificate & Registration numbers
        $data['certificate_no'] = $this->model->generateCertificateNo();
        $data['registration_no'] = $this->model->generateRegistrationNo();

        // Create verification QR code content
        $host = $_SERVER['HTTP_HOST'];
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        // Verification url
        $verify_url = $protocol . "://" . $host . dirname($_SERVER['PHP_SELF']) . "/verify.php?cert_no=" . $data['certificate_no'];
        $data['qr_code'] = $verify_url;

        // Save
        $insert_id = $this->model->create($data);

        if ($insert_id) {
            flash('success', 'Nikahnama Certificate registered successfully.');
            // Clean dynamic form states
            unset($_SESSION['form_errors']);
            unset($_SESSION['form_data']);
            header("Location: view.php?id=" . $insert_id);
            exit;
        } else {
            $detail = flash('error_detail');
            $msg = 'Failed to register Certificate. Please try again.';
            if ($detail) {
                $msg .= ' Details: ' . $detail;
            }
            flash('error', $msg);
        }
    }

    /**
     * Process edit form submission
     */
    public function handleEdit($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Validate CSRF
        if (!isset($_POST['csrf_token']) || !validate_csrf($_POST['csrf_token'])) {
            flash('error', 'CSRF token validation failed.');
            return;
        }

        // Clean & sanitize
        $data = sanitize($_POST);

        // Validate
        $errors = $this->validateFormData($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            flash('error', 'Please correct the highlighted errors.');
            return;
        }

        // Update
        $success = $this->model->update($id, $data);

        if ($success) {
            flash('success', 'Nikahnama Certificate updated successfully.');
            unset($_SESSION['form_errors']);
            header("Location: view.php?id=" . $id);
            exit;
        } else {
            $detail = flash('error_detail');
            $msg = 'Failed to update Certificate.';
            if ($detail) {
                $msg .= ' Details: ' . $detail;
            }
            flash('error', $msg);
        }
    }

    /**
     * Handle deletion of certificate
     */
    public function handleDelete($id) {
        require_admin(); // Only admins can delete certificates

        if ($this->model->delete($id)) {
            flash('success', 'Certificate deleted successfully.');
        } else {
            flash('error', 'Failed to delete certificate.');
        }
        header("Location: dashboard.php");
        exit;
    }

    /**
     * Perform global search
     */
    public function handleSearch($query) {
        return $this->model->search(trim($query));
    }

    /**
     * Public verification logic
     */
    public function verify($certNo) {
        return $this->model->getByCertificateNo(trim($certNo));
    }

    /**
     * Form validations
     */
    private function validateFormData($data) {
        $errors = [];

        // Required field validations
        $required = [
            'marriage_date' => 'Marriage date is required',
            'marriage_time' => 'Marriage time is required',
            'marriage_place' => 'Marriage venue is required',
            'mahr_amount' => 'Mahr amount is required',
            'mahr_status' => 'Mahr status selection is required',
            
            'bride_name' => 'Bride name is required',
            'bride_father' => 'Bride father name is required',
            'bride_mother' => 'Bride mother name is required',
            'bride_birth' => 'Bride date of birth is required',
            'bride_phone' => 'Bride phone number is required',
            'bride_address' => 'Bride address is required',
            
            'groom_name' => 'Groom name is required',
            'groom_father' => 'Groom father name is required',
            'groom_mother' => 'Groom mother name is required',
            'groom_birth' => 'Groom date of birth is required',
            'groom_phone' => 'Groom phone number is required',
            'groom_address' => 'Groom address is required',
            
            'registrar_name' => 'Registrar name is required',
            'registrar_license' => 'Registrar license number is required',
            'registrar_phone' => 'Registrar phone number is required',
            'registrar_address' => 'Registrar address is required',
            
            'witness1_name' => 'Witness 1 name is required',
            'witness1_nid' => 'Witness 1 NID is required',
            'witness2_name' => 'Witness 2 name is required',
            'witness2_nid' => 'Witness 2 NID is required',
        ];

        foreach ($required as $field => $msg) {
            if (empty($data[$field])) {
                $errors[$field] = $msg;
            }
        }

        // Validate dates
        if (!empty($data['marriage_date']) && !$this->isValidDate($data['marriage_date'])) {
            $errors['marriage_date'] = 'Invalid marriage date format.';
        }
        if (!empty($data['bride_birth']) && !$this->isValidDate($data['bride_birth'])) {
            $errors['bride_birth'] = 'Invalid bride birth date format.';
        }
        if (!empty($data['groom_birth']) && !$this->isValidDate($data['groom_birth'])) {
            $errors['groom_birth'] = 'Invalid groom birth date format.';
        }

        // Validate numbers
        if (!empty($data['mahr_amount']) && !is_numeric($data['mahr_amount'])) {
            $errors['mahr_amount'] = 'Mahr amount must be a number.';
        }

        return $errors;
    }

    private function isValidDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
