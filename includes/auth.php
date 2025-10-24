<?php
/**
 * Authentication System
 */

require_once __DIR__ . '/db.php';

/**
 * Authenticate user with email and password
 */
function login($email, $password) {
    $user = Database::fetchOne(
        "SELECT * FROM USERS WHERE email = ?",
        [$email]
    );
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        // Log successful login
        require_once __DIR__ . '/audit.php';
        auditLog('User Login', 'USERS', $user['user_id']);
        
        return true;
    }
    
    return false;
}

/**
 * Logout current user
 */
function logout() {
    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/audit.php';
        auditLog('User Logout', 'USERS', $_SESSION['user_id']);
    }
    
    session_destroy();
    session_start();
    
    // Preserve language and theme preferences
    if (!isset($_SESSION['lang'])) {
        $_SESSION['lang'] = DEFAULT_LANG;
    }
    if (!isset($_SESSION['calendar'])) {
        $_SESSION['calendar'] = DEFAULT_CALENDAR;
    }
    if (!isset($_SESSION['theme'])) {
        $_SESSION['theme'] = 'light';
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        logout();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Get current user data
 */
function currentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}

/**
 * Require user to be logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $roles = ['viewer', 'staff', 'manager', 'admin'];
    $userRoleIndex = array_search($_SESSION['user_role'], $roles);
    $requiredRoleIndex = array_search($role, $roles);
    
    return $userRoleIndex !== false && $userRoleIndex >= $requiredRoleIndex;
}

/**
 * Require user to have specific role
 */
function requireRole($role) {
    requireLogin();
    
    if (!hasRole($role)) {
        http_response_code(403);
        die("Access denied. You don't have permission to access this page.");
    }
}

/**
 * Check if user can perform action
 */
function canCreate() {
    return hasRole('staff');
}

function canEdit() {
    return hasRole('staff');
}

function canDelete() {
    return hasRole('manager');
}

function canApprove() {
    return hasRole('manager');
}

function isAdmin() {
    return hasRole('admin');
}
