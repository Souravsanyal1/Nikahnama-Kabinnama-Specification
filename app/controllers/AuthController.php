<?php
// app/controllers/AuthController.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session.php';

class AuthController {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Authenticate user login
     */
    public function login($username, $password) {
        $username = trim($username);
        if (empty($username) || empty($password)) {
            flash('error', 'Username and password are required.');
            return false;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                login_user($user['id'], $user['username'], $user['fullname'], $user['role']);
                return true;
            }

            flash('error', 'Invalid username or password.');
            return false;
        } catch (PDOException $e) {
            flash('error', 'Database error during authentication: ' . $e->getMessage());
            return false;
        }
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
