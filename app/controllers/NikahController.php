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
            flash('error', 'সার্টিফিকেট পাওয়া যায়নি।');
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
            flash('error', 'CSRF ভেরিফিকেশন ব্যর্থ হয়েছে।');
            return;
        }

        // Clean & sanitize input
        $data = sanitize($_POST);

        // Validation Errors collector
        $errors = $this->validateFormData($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST; // Preserve input
            flash('error', 'অনুগ্রহ করে লাল চিহ্নিত ত্রুটিগুলো সংশোধন করুন।');
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
            flash('success', 'নিকাহনামা সার্টিফিকেট সফলভাবে নিবন্ধিত হয়েছে।');
            // Clean dynamic form states
            unset($_SESSION['form_errors']);
            unset($_SESSION['form_data']);
            header("Location: view.php?id=" . $insert_id);
            exit;
        } else {
            $detail = flash('error_detail');
            $msg = 'সার্টিফিকেট নিবন্ধন করতে ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।';
            if ($detail) {
                $msg .= ' কারণ: ' . $detail;
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
            flash('error', 'CSRF ভেরিফিকেশন ব্যর্থ হয়েছে।');
            return;
        }

        // Clean & sanitize
        $data = sanitize($_POST);

        // Validate
        $errors = $this->validateFormData($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            flash('error', 'অনুগ্রহ করে লাল চিহ্নিত ত্রুটিগুলো সংশোধন করুন।');
            return;
        }

        // Update
        $success = $this->model->update($id, $data);

        if ($success) {
            flash('success', 'নিকাহনামা সার্টিফিকেট সফলভাবে আপডেট করা হয়েছে।');
            unset($_SESSION['form_errors']);
            header("Location: view.php?id=" . $id);
            exit;
        } else {
            $detail = flash('error_detail');
            $msg = 'সার্টিফিকেট আপডেট করতে ব্যর্থ হয়েছে।';
            if ($detail) {
                $msg .= ' কারণ: ' . $detail;
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
            flash('success', 'সার্টিফিকেটটি সফলভাবে মুছে ফেলা হয়েছে।');
        } else {
            flash('error', 'সার্টিফিকেটটি মুছতে ব্যর্থ হয়েছে।');
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
    public function handleVerify($certNo) {
        return $this->model->getByCertificateNo(trim($certNo));
    }

    /**
     * Form validations
     */
    private function validateFormData($data) {
        $errors = [];

        // Required field validations
        $required = [
            'marriage_date' => 'বিবাহ সম্পন্ন হওয়ার তারিখ প্রদান করা আবশ্যক।',
            'marriage_time' => 'বিবাহের সময় প্রদান করা আবশ্যক।',
            'marriage_place' => 'বিবাহের স্থান / ভেন্যু প্রদান করা আবশ্যক।',
            'mahr_amount' => 'দেনমোহরের পরিমাণ প্রদান করা আবশ্যক।',
            'mahr_status' => 'দেনমোহর পরিশোধের অবস্থা নির্ধারণ করা আবশ্যক।',
            
            'bride_name' => 'কনের পূর্ণ নাম প্রদান করা আবশ্যক।',
            'bride_father' => 'কনের পিতার নাম প্রদান করা আবশ্যক।',
            'bride_mother' => 'কনের মাতার নাম প্রদান করা আবশ্যক।',
            'bride_birth' => 'কনের জন্ম তারিখ প্রদান করা আবশ্যক।',
            'bride_phone' => 'কনের মোবাইল নম্বর প্রদান করা আবশ্যক।',
            'bride_address' => 'কনের পূর্ণ ঠিকানা প্রদান করা আবশ্যক।',
            
            'groom_name' => 'বরের পূর্ণ নাম প্রদান করা আবশ্যক।',
            'groom_father' => 'বরের পিতার নাম প্রদান করা আবশ্যক।',
            'groom_mother' => 'বরের মাতার নাম প্রদান করা আবশ্যক।',
            'groom_birth' => 'বরের জন্ম তারিখ প্রদান করা আবশ্যক।',
            'groom_phone' => 'বরের মোবাইল নম্বর প্রদান করা আবশ্যক।',
            'groom_address' => 'বরের পূর্ণ ঠিকানা প্রদান করা আবশ্যক।',
            
            'registrar_name' => 'কাজী (নিকাহ রেজিস্টার) এর পূর্ণ নাম প্রদান করা আবশ্যক।',
            'registrar_license' => 'কাজী লাইসেন্স নম্বর প্রদান করা আবশ্যক।',
            'registrar_phone' => 'কাজীর মোবাইল নম্বর প্রদান করা আবশ্যক।',
            'registrar_address' => 'কাজীর কার্যালয়ের ঠিকানা প্রদান করা আবশ্যক।',
            
            'witness1_name' => '১ম সাক্ষীর নাম প্রদান করা আবশ্যক।',
            'witness1_nid' => '১ম সাক্ষীর NID নম্বর প্রদান করা আবশ্যক।',
            'witness2_name' => '২য় সাক্ষীর নাম প্রদান করা আবশ্যক।',
            'witness2_nid' => '২য় সাক্ষীর NID নম্বর প্রদান করা আবশ্যক।',
        ];

        foreach ($required as $field => $msg) {
            if (empty($data[$field])) {
                $errors[$field] = $msg;
            }
        }

        // Validate dates
        if (!empty($data['marriage_date']) && !$this->isValidDate($data['marriage_date'])) {
            $errors['marriage_date'] = 'অকার্যকর বিবাহের তারিখ বিন্যাস।';
        }
        if (!empty($data['bride_birth']) && !$this->isValidDate($data['bride_birth'])) {
            $errors['bride_birth'] = 'অকার্যকর কনের জন্ম তারিখ বিন্যাস।';
        }
        if (!empty($data['groom_birth']) && !$this->isValidDate($data['groom_birth'])) {
            $errors['groom_birth'] = 'অকার্যকর বরের জন্ম তারিখ বিন্যাস।';
        }

        // Validate numbers
        if (!empty($data['mahr_amount']) && !is_numeric($data['mahr_amount'])) {
            $errors['mahr_amount'] = 'দেনমোহরের পরিমাণ অবশ্যই একটি সঠিক সংখ্যা হতে হবে।';
        }

        return $errors;
    }

    private function isValidDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
