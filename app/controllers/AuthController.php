<?php
// app/controllers/AuthController.php

require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../models/Nikahnama.php';

class AuthController {
    private $model;

    public function __construct() {
        $this->model = new Nikahnama();
    }

    /**
     * Authenticate user login using local DB/Firestore or Firebase Auth fallback
     */
    public function login($username, $password) {
        $username = trim($username);
        $password = trim($password);
        
        if (empty($username) || empty($password)) {
            flash('error', 'ইউজারনেম এবং পাসওয়ার্ড উভয়ই প্রদান করা আবশ্যক।');
            return false;
        }

        // 1. First search in local Firestore /users collection
        $user = $this->model->getUserByUsername($username);
        
        if ($user) {
            // Check if approved
            $is_admin = (strcasecmp($username, 'sourav.sanyal.dev@gmail.com') === 0 || strcasecmp($user['role'] ?? '', 'admin') === 0);
            
            // If they are not approved and not admin
            if (!$is_admin && isset($user['approved']) && $user['approved'] === false) {
                flash('error', 'আপনার অ্যাকাউন্টটি এখনো এডমিন দ্বারা অনুমোদিত হয়নি। অনুগ্রহ করে এডমিনের অনুমোদনের জন্য অপেক্ষা করুন।');
                return false;
            }
            
            // Verify password using bcrypt hash
            if (password_verify($password, $user['password'])) {
                $role = $is_admin ? 'admin' : ($user['role'] ?? 'officer');
                login_user($user['id'], $user['username'], $user['fullname'], $role);
                return true;
            }
        }

        // 2. Fallback to Firebase Auth REST API (for manually created Firebase Auth users)
        $apiKey = "AIzaSyBwIqGjfaGpdccxg2AyhpmLXDG6A9oC_yI";
        $url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=" . $apiKey;
        
        $postData = [
            'email' => $username,
            'password' => $password,
            'returnSecureToken' => true
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (PHP_VERSION_ID < 80500) {
            @curl_close($ch);
        }
        
        if ($httpCode === 200) {
            $res = json_decode($response, true);
            if ($res && isset($res['localId'])) {
                // If it is sourav.sanyal.dev@gmail.com, make them admin
                $is_admin = (strcasecmp($res['email'], 'sourav.sanyal.dev@gmail.com') === 0);
                $role = $is_admin ? 'admin' : 'officer';
                
                // Extract fullname
                $fullname = explode('@', $res['email'])[0];
                $fullname = ucwords(str_replace(['.', '_', '-'], ' ', $fullname));
                
                login_user($res['localId'], $res['email'], $fullname, $role);
                return true;
            }
        }
        
        // Handle failure responses
        $errMsg = 'ভুল ইমেইল অথবা পাসওয়ার্ড।';
        if ($response) {
            $res = json_decode($response, true);
            if (isset($res['error']['message'])) {
                $firebaseErr = $res['error']['message'];
                if ($firebaseErr === 'EMAIL_NOT_FOUND' || $firebaseErr === 'INVALID_PASSWORD' || $firebaseErr === 'INVALID_LOGIN_CREDENTIALS') {
                    $errMsg = 'ভুল ইমেইল অথবা পাসওয়ার্ড।';
                } elseif ($firebaseErr === 'USER_DISABLED') {
                    $errMsg = 'এই অ্যাকাউন্টটি নিষ্ক্রিয় করা হয়েছে।';
                } else {
                    $errMsg = 'অনুমোদন ত্রুটি: ' . str_replace('_', ' ', $firebaseErr);
                }
            }
        }
        
        flash('error', $errMsg);
        return false;
    }

    /**
     * Handle user registration
     */
    public function register($fullname, $email, $password, $license, $phone, $address) {
        $fullname = trim($fullname);
        $email = trim($email);
        $password = trim($password);
        $license = trim($license);
        $phone = trim($phone);
        $address = trim($address);

        if (empty($fullname) || empty($email) || empty($password) || empty($license) || empty($phone) || empty($address)) {
            flash('error', 'সবগুলো ঘর পূরণ করা আবশ্যক।');
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'অনুগ্রহ করে একটি সঠিক ইমেইল এড্রেস প্রদান করুন।');
            return false;
        }

        // Check if email already exists
        $existing = $this->model->getUserByUsername($email);
        if ($existing) {
            flash('error', 'এই ইমেইলটি ইতিপূর্বে ব্যবহার করা হয়েছে।');
            return false;
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Auto approve admin account
        $is_admin = (strcasecmp($email, 'sourav.sanyal.dev@gmail.com') === 0);
        $approved = $is_admin ? true : false;
        $role = $is_admin ? 'admin' : 'officer';

        // Prepare user document payload
        $userData = [
            'fullname' => $fullname,
            'username' => $email,
            'password' => $hashed_password,
            'license_no' => $license,
            'phone' => $phone,
            'address' => $address,
            'role' => $role,
            'approved' => $approved,
            'created_at' => date('c')
        ];

        // Save to Firestore /users collection using model helper
        $res = $this->model->createUser($userData);

        if ($res && isset($res['name'])) {
            if ($is_admin) {
                flash('success', 'অ্যাডমিন অ্যাকাউন্ট সফলভাবে নিবন্ধিত হয়েছে! আপনি এখন লগইন করতে পারেন।');
            } else {
                flash('success', 'আপনার রেজিস্ট্রেশন সফল হয়েছে! অ্যাকাউন্টটি সক্রিয় করার জন্য এডমিনের অনুমোদনের অপেক্ষা করুন।');
            }
            return true;
        }

        flash('error', 'নিবন্ধন ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
        return false;
    }

    /**
     * Log out current user
     */
    public function logout() {
        logout_user();
        header("Location: login.php");
        exit;
    }
}
