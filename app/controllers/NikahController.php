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
     * Get statistics for new muslim dashboard
     */
    public function getNewMuslimDashboardStats() {
        return [
            'total' => $this->model->countAllNewMuslims(),
            'today' => $this->model->countNewMuslimsToday(),
            'month' => $this->model->countNewMuslimsThisMonth(),
            'recent' => $this->model->getRecentNewMuslims(5)
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

    /**
     * View a single new muslim certificate by ID
     */
    public function showNewMuslim($id) {
        $cert = $this->model->getNewMuslimById($id);
        if (!$cert) {
            flash('error', 'নওমুসলিম রেকর্ড পাওয়া যায়নি।');
            header("Location: dashboard.php");
            exit;
        }
        return $cert;
    }

    /**
     * Process creation form submission for New Muslim
     */
    public function handleCreateNewMuslim() {
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
        $errors = $this->validateNewMuslimFormData($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST; // Preserve input
            flash('error', 'অনুগ্রহ করে লাল চিহ্নিত ত্রুটিগুলো সংশোধন করুন।');
            return;
        }

        // Generate Certificate Number
        $data['certificate_no'] = $this->model->generateNewMuslimCertNo();

        // Create verification QR code content
        $host = $_SERVER['HTTP_HOST'];
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        // Verification url
        $verify_url = $protocol . "://" . $host . dirname($_SERVER['PHP_SELF']) . "/verify_new_muslim.php?cert_no=" . $data['certificate_no'];
        $data['qr_code'] = $verify_url;

        // Save
        $insert_id = $this->model->createNewMuslim($data);

        if ($insert_id) {
            flash('success', 'নওমুসলিম রেকর্ড সফলভাবে নিবন্ধিত হয়েছে।');
            // Clean dynamic form states
            unset($_SESSION['form_errors']);
            unset($_SESSION['form_data']);
            header("Location: view_new_muslim.php?id=" . $insert_id);
            exit;
        } else {
            $detail = flash('error_detail');
            $msg = 'নওমুসলিম রেকর্ড নিবন্ধন করতে ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।';
            if ($detail) {
                $msg .= ' কারণ: ' . $detail;
            }
            flash('error', $msg);
        }
    }

    /**
     * Process edit form submission for New Muslim
     */
    public function handleEditNewMuslim($id) {
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
        $errors = $this->validateNewMuslimFormData($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            flash('error', 'অনুগ্রহ করে লাল চিহ্নিত ত্রুটিগুলো সংশোধন করুন।');
            return;
        }

        // Update
        $success = $this->model->updateNewMuslim($id, $data);

        if ($success) {
            flash('success', 'নওমুসলিম রেকর্ড সফলভাবে আপডেট করা হয়েছে।');
            unset($_SESSION['form_errors']);
            header("Location: view_new_muslim.php?id=" . $id);
            exit;
        } else {
            $detail = flash('error_detail');
            $msg = 'নওমুসলিম রেকর্ড আপডেট করতে ব্যর্থ হয়েছে।';
            if ($detail) {
                $msg .= ' কারণ: ' . $detail;
            }
            flash('error', $msg);
        }
    }

    /**
     * Handle deletion of new muslim certificate
     */
    public function handleDeleteNewMuslim($id) {
        require_admin(); // Only admins can delete

        if ($this->model->deleteNewMuslim($id)) {
            flash('success', 'নওমুসলিম রেকর্ড সফলভাবে মুছে ফেলা হয়েছে।');
        } else {
            flash('error', 'নওমুসলিম রেকর্ড মুছতে ব্যর্থ হয়েছে।');
        }
        header("Location: dashboard.php");
        exit;
    }

    /**
     * Perform global search for new muslims
     */
    public function handleNewMuslimSearch($query) {
        return $this->model->searchNewMuslims(trim($query));
    }

    /**
     * Public verification logic for new muslims
     */
    public function handleNewMuslimVerify($certNo) {
        return $this->model->getNewMuslimByCertNo(trim($certNo));
    }

    /**
     * New Muslim validation
     */
    private function validateNewMuslimFormData($data) {
        $errors = [];

        // Required field validations
        $required = [
            'previous_name' => 'পূর্বের নাম প্রদান করা আবশ্যক।',
            'previous_religion' => 'পূর্বের ধর্ম প্রদান করা আবশ্যক।',
            'new_name' => 'নতুন ইসলামী নাম প্রদান করা আবশ্যক।',
            'father_name' => 'পিতার নাম প্রদান করা আবশ্যক।',
            'mother_name' => 'মাতার নাম প্রদান করা আবশ্যক।',
            'date_of_birth' => 'জন্ম তারিখ প্রদান করা আবশ্যক।',
            'phone_no' => 'মোবাইল নম্বর প্রদান করা আবশ্যক।',
            'address' => 'পূর্ণ ঠিকানা প্রদান করা আবশ্যক।',
            'declaration_date' => 'ইসলাম গ্রহণের তারিখ প্রদান করা আবশ্যক।',
            'imam_name' => 'ইমাম/আলেমের নাম প্রদান করা আবশ্যক।',
            'imam_title' => 'ইমামের পদবী প্রদান করা আবশ্যক।',
            'institution_name' => 'প্রতিষ্ঠানের নাম প্রদান করা আবশ্যক।',
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
        if (!empty($data['declaration_date']) && !$this->isValidDate($data['declaration_date'])) {
            $errors['declaration_date'] = 'অকার্যকর ইসলাম গ্রহণের তারিখ বিন্যাস।';
        }
        if (!empty($data['date_of_birth']) && !$this->isValidDate($data['date_of_birth'])) {
            $errors['date_of_birth'] = 'অকার্যকর জন্ম তারিখ বিন্যাস।';
        }

        return $errors;
    }

    /**
     * Get all users (Admin only)
     */
    public function getAllUsers() {
        require_admin();
        return $this->model->getAllUsers();
    }

    /**
     * Approve user (Admin only)
     */
    public function approveUser($userId) {
        require_admin();
        if ($this->model->approveUser($userId)) {
            flash('success', 'ব্যবহারকারী অ্যাকাউন্টটি সফলভাবে অনুমোদন করা হয়েছে।');
        } else {
            flash('error', 'ব্যবহারকারী অনুমোদন করতে ব্যর্থ হয়েছে।');
        }
        header("Location: dashboard.php");
        exit;
    }

    /**
     * Reject/Delete user (Admin only)
     */
    public function rejectUser($userId) {
        require_admin();
        if ($this->model->deleteUser($userId)) {
            flash('success', 'ব্যবহারকারী অ্যাকাউন্টটি বাতিল/মুছে ফেলা হয়েছে।');
        } else {
            flash('error', 'ব্যবহারকারী বাতিল করতে ব্যর্থ হয়েছে।');
        }
        header("Location: dashboard.php");
        exit;
    }

    /**
     * Change logged-in user's own password
     */
    public function handleOwnPasswordChange($currentPass, $newPass, $confirmPass) {
        $currentPass = trim($currentPass);
        $newPass = trim($newPass);
        $confirmPass = trim($confirmPass);

        if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
            flash('error', 'সবগুলো ঘর পূরণ করা আবশ্যক।');
            return false;
        }

        if ($newPass !== $confirmPass) {
            flash('error', 'নতুন পাসওয়ার্ড এবং নিশ্চিতকরণ পাসওয়ার্ড মেলেনি।');
            return false;
        }

        if (strlen($newPass) < 6) {
            flash('error', 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।');
            return false;
        }

        // Fetch user from db
        $user = $this->model->getUserByUsername($_SESSION['username']);
        if (!$user) {
            flash('error', 'ব্যবহারকারী পাওয়া যায়নি।');
            return false;
        }

        // Verify current password
        if (!password_verify($currentPass, $user['password'])) {
            flash('error', 'বর্তমান পাসওয়ার্ডটি সঠিক নয়।');
            return false;
        }

        // Hash & Update new password
        $hashed = password_hash($newPass, PASSWORD_BCRYPT);
        if ($this->model->updatePassword($user['id'], $hashed)) {
            flash('success', 'আপনার পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।');
            return true;
        }

        flash('error', 'পাসওয়ার্ড পরিবর্তন করতে ব্যর্থ হয়েছে।');
        return false;
    }

    /**
     * Admin forces password change for another officer
     */
    public function handleAdminPasswordChange($userId, $newPass, $confirmPass) {
        require_admin();
        $userId = trim($userId);
        $newPass = trim($newPass);
        $confirmPass = trim($confirmPass);

        if (empty($userId) || empty($newPass) || empty($confirmPass)) {
            flash('error', 'সবগুলো ঘর পূরণ করা আবশ্যক।');
            return false;
        }

        if ($newPass !== $confirmPass) {
            flash('error', 'নতুন পাসওয়ার্ড এবং নিশ্চিতকরণ পাসওয়ার্ড মেলেনি।');
            return false;
        }

        if (strlen($newPass) < 6) {
            flash('error', 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।');
            return false;
        }

        // Hash & Update password
        $hashed = password_hash($newPass, PASSWORD_BCRYPT);
        if ($this->model->updatePassword($userId, $hashed)) {
            flash('success', 'কর্মকর্তার পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।');
            return true;
        }

        flash('error', 'পাসওয়ার্ড পরিবর্তন করতে ব্যর্থ হয়েছে।');
        return false;
    }

    /**
     * Get last database model error
     */
    public function getLastError() {
        return $this->model->getLastError();
    }
}

