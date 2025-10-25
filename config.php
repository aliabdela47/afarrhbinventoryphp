<?php
/**
 * AfarRHB Inventory Management System
 * Configuration File
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application settings
define('APP_NAME', 'AfarRHB Inventory');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/afarrhbinventoryphp'); // Change this to your actual URL

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'afarrhb_inventory');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Directory paths
define('BASE_PATH', __DIR__);
define('UPLOAD_DIR', BASE_PATH . '/uploads');
define('DOCUMENT_DIR', UPLOAD_DIR . '/documents');
define('TEMP_DIR', UPLOAD_DIR . '/temp');

// URL paths
define('BASE_URL', rtrim(APP_URL, '/'));
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// Upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif']);

// Default settings
define('DEFAULT_LANG', 'en'); // en or am (Amharic)
define('DEFAULT_CALENDAR', 'gregorian'); // gregorian or ethiopian
define('ITEMS_PER_PAGE', 20);

// Timezone
date_default_timezone_set('Africa/Addis_Ababa');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('CSRF_TOKEN_LENGTH', 32);

// Initialize session variables if not set
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = DEFAULT_LANG;
}

if (!isset($_SESSION['calendar'])) {
    $_SESSION['calendar'] = DEFAULT_CALENDAR;
}

if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light';
}
