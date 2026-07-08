<?php
// app/controllers/AuthController.php

require_once __DIR__ . '/../helpers/session.php';

class AuthController {

    public function __construct() {
        // No local model initialization required for authentication anymore
    }

    /**
     * Authenticate user login using Firebase Auth REST API
     */
    public function login($username, $password) {
        $username = trim($username);
        if (empty($username) || empty($password)) {
            flash('error', 'Username and password are required.');
            return false;
        }

        // Firebase Web API Key from project config
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Prevent local SSL verification issues
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $res = json_decode($response, true);
            if ($res && isset($res['localId'])) {
                // Extract fullname from email prefix for display
                $fullname = explode('@', $res['email'])[0];
                $fullname = ucwords(str_replace(['.', '_', '-'], ' ', $fullname));
                
                // Establish user session
                login_user($res['localId'], $res['email'], $fullname, 'admin');
                return true;
            }
        }
        
        // Handle failure responses
        $errMsg = 'Invalid email or password.';
        if ($response) {
            $res = json_decode($response, true);
            if (isset($res['error']['message'])) {
                $firebaseErr = $res['error']['message'];
                if ($firebaseErr === 'EMAIL_NOT_FOUND' || $firebaseErr === 'INVALID_PASSWORD' || $firebaseErr === 'INVALID_LOGIN_CREDENTIALS') {
                    $errMsg = 'Invalid email or password.';
                } elseif ($firebaseErr === 'USER_DISABLED') {
                    $errMsg = 'This account has been disabled.';
                } else {
                    $errMsg = 'Authentication error: ' . str_replace('_', ' ', $firebaseErr);
                }
            }
        }
        
        flash('error', $errMsg);
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
