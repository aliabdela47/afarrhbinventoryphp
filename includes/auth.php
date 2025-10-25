<?php
/**
 * Authentication Functions
 * AfarRHB Inventory Management System
 */

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isAuthenticated()) {
        flash('error', t('please_login'));
        redirect('index.php');
        exit();
    }
}

/**
 * Require specific role(s)
 */
function requireRole($roles) {
    requireAuth();
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    if (!in_array($_SESSION['user_role'], $roles)) {
        flash('error', t('access_denied'));
        redirect('dashboard.php');
        exit();
    }
}

/**
 * Check if user has role
 */
function hasRole($role) {
    return isAuthenticated() && $_SESSION['user_role'] === $role;
}

/**
 * Check if user has any of the roles
 */
function hasAnyRole($roles) {
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return isAuthenticated() && in_array($_SESSION['user_role'], $roles);
}

/**
 * Login user
 */
function login($pdo, $email, $password) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM USERS WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            
            // Log successful login
            logAudit($pdo, 'LOGIN', 'USERS', $user['id']);
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

/**
 * Logout user
 */
function logout($pdo) {
    // Log logout
    if (isset($_SESSION['user_id'])) {
        logAudit($pdo, 'LOGOUT', 'USERS', $_SESSION['user_id']);
    }
    
    // Clear session
    $_SESSION = [];
    
    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Get current user
 */
function getCurrentUser($pdo) {
    if (!isAuthenticated()) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM USERS WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get current user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Update last login
 */
function updateLastLogin($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("UPDATE USERS SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    } catch (PDOException $e) {
        error_log("Update last login error: " . $e->getMessage());
    }
}

/**
 * Check password strength
 */
function isPasswordStrong($password) {
    // At least 8 characters, one uppercase, one lowercase, one number
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return false;
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    return true;
}

/**
 * Get user's full name
 */
function getUserFullName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

/**
 * Get user's role
 */
function getUserRole() {
    return $_SESSION['user_role'] ?? 'viewer';
}
