<?php
/**
 * Login Handler
 * AfarRHB Inventory Management System
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// Verify CSRF token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    flash('error', 'Invalid request. Please try again.');
    redirect('index.php');
}

// Get form data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    flash('error', t('please_login'));
    redirect('index.php');
}

// Attempt login
if (login($pdo, $email, $password)) {
    // Update last login
    updateLastLogin($pdo, $_SESSION['user_id']);
    
    flash('success', t('login_success'));
    redirect('dashboard.php');
} else {
    flash('error', t('login_failed'));
    redirect('index.php');
}
