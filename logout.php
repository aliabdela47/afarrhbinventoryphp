<?php
/**
 * Logout Handler
 * AfarRHB Inventory Management System
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';

// Logout user
logout($pdo);

// Redirect to login
flash('success', t('logout_success'));
redirect('index.php');
