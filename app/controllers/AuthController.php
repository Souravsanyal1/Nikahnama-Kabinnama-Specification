<?php
// app/controllers/AuthController.php

require_once __DIR__ . '/../models/Nikahnama.php';
require_once __DIR__ . '/../helpers/session.php';

class AuthController {
    private $model;

    public function __construct() {
        $this->model = new Nikahnama();
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
            $user = $this->model->getUserByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                login_user($user['id'], $user['username'], $user['fullname'], $user['role']);
                return true;
            }

            flash('error', 'Invalid username or password.');
            return false;
        } catch (Exception $e) {
            flash('error', 'Authentication error: ' . $e->getMessage());
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
