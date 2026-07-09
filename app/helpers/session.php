<?php
// app/helpers/session.php

define('SESSION_COOKIE_NAME', 'NIKAH_SESS');
define('SESSION_SECRET_KEY', 'AIzaSyBwIqGjfaGpdccxg2AyhpmLXDG6A9oC_yI_SEC_SESS_KEY');

// Initialize virtual $_SESSION
if (!isset($_SESSION)) {
    $_SESSION = [];
}

/**
 * Custom session loader - Reads from signed cookie
 */
function start_custom_session() {
    if (isset($_COOKIE[SESSION_COOKIE_NAME])) {
        $cookie_val = $_COOKIE[SESSION_COOKIE_NAME];
        $parts = explode('.', $cookie_val, 2);
        if (count($parts) === 2) {
            $payload = $parts[0];
            $sig = $parts[1];
            
            // Verify HMAC signature to prevent tampering
            $expected_sig = hash_hmac('sha256', $payload, SESSION_SECRET_KEY);
            if (hash_equals($expected_sig, $sig)) {
                $decoded = json_decode(base64_decode($payload), true);
                if (is_array($decoded)) {
                    global $_SESSION;
                    $_SESSION = $decoded;
                }
            }
        }
    }
    
    // Register shutdown function to write session back to cookie at end of request
    register_shutdown_function('write_custom_session');
}

/**
 * Custom session writer - Writes to signed cookie
 */
function write_custom_session() {
    global $_SESSION;
    if (empty($_SESSION)) {
        if (isset($_COOKIE[SESSION_COOKIE_NAME])) {
            // Set cookie parameters safely for Vercel
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            setcookie(SESSION_COOKIE_NAME, '', time() - 3600, '/', '', $secure, true);
        }
        return;
    }
    
    $payload = base64_encode(json_encode($_SESSION));
    $sig = hash_hmac('sha256', $payload, SESSION_SECRET_KEY);
    $cookie_val = $payload . '.' . $sig;
    
    // Set cookie for 1 day, secure on HTTPS
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    setcookie(SESSION_COOKIE_NAME, $cookie_val, time() + 86400, '/', '', $secure, true);
}

// Start custom stateless session immediately
start_custom_session();

/**
 * Log a user in by storing information in the session.
 */
function login_user($user_id, $username, $fullname, $role) {
    global $_SESSION;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['fullname'] = $fullname;
    $_SESSION['role'] = $role;
    $_SESSION['last_activity'] = time();
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * Check if the current user is logged in.
 */
function is_logged_in() {
    global $_SESSION;
    if (isset($_SESSION['user_id'])) {
        // Verify User-Agent to prevent session hijacking
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            logout_user();
            return false;
        }
        
        // Timeout session after 30 minutes of inactivity
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
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
    global $_SESSION;
    if ($_SESSION['role'] !== 'admin') {
        flash('error', 'অনুমতি নেই। এই সুবিধাটি শুধুমাত্র এডমিনের জন্য সংরক্ষিত।');
        header("Location: dashboard.php");
        exit;
    }
}

/**
 * Log the user out and clean up session variables.
 */
function logout_user() {
    global $_SESSION;
    $_SESSION = [];
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    setcookie(SESSION_COOKIE_NAME, '', time() - 3600, '/', '', $secure, true);
}

/**
 * Generate CSRF token and store it in session.
 */
function csrf_token() {
    global $_SESSION;
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token.
 */
function validate_csrf($token) {
    global $_SESSION;
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
    global $_SESSION;
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
    global $_SESSION;
    return isset($_SESSION['flash_' . $name]);
}
