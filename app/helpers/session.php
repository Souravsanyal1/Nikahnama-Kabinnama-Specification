<?php
// app/helpers/session.php

if (session_status() === PHP_SESSION_NONE) {
    // Configure session cookie options for extra security
    session_start([
        'cookie_httponly' => true,
        'cookie_use_only_cookies' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

/**
 * Regenerates session ID to prevent session fixation.
 */
function secure_session_regenerate() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Log a user in by storing information in the session.
 */
function login_user($user_id, $username, $fullname, $role) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['fullname'] = $fullname;
    $_SESSION['role'] = $role;
    $_SESSION['last_activity'] = time();
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    secure_session_regenerate();
}

/**
 * Check if the current user is logged in.
 */
function is_logged_in() {
    if (isset($_SESSION['user_id'])) {
        // Prevent session hijacking by verifying IP and User-Agent
        if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            logout_user();
            return false;
        }
        
        // Timeout session after 30 minutes of inactivity
        if (time() - $_SESSION['last_activity'] > 1800) {
            logout_user();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

/**
 * Force login; redirect to login page if user is not authenticated.
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Force admin role; redirect to dashboard if not admin.
 */
function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        flash('error', 'Unauthorized access. Admin privileges required.');
        header("Location: dashboard.php");
        exit;
    }
}

/**
 * Log the user out and clean up session variables.
 */
function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Generate CSRF token and store it in session.
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token.
 */
function validate_csrf($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * XSS Sanitation helper
 */
function sanitize($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($value);
        }
        return $data;
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Flash messages helper
 */
function flash($name, $message = '') {
    if (!empty($name)) {
        if (!empty($message)) {
            $_SESSION['flash_' . $name] = $message;
        } elseif (isset($_SESSION['flash_' . $name])) {
            $msg = $_SESSION['flash_' . $name];
            unset($_SESSION['flash_' . $name]);
            return $msg;
        }
    }
    return '';
}

/**
 * Check if a flash message exists
 */
function has_flash($name) {
    return isset($_SESSION['flash_' . $name]);
}
