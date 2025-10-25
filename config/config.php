<?php
/**
 * Application Configuration
 * AfarRHB Inventory Management System
 */

// Application settings
define('APP_NAME', 'AfarRHB Inventory');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/afarrhbinventoryphp');

// Path settings
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('UPLOAD_MAX_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']);

// Session settings
define('SESSION_LIFETIME', 7200); // 2 hours in seconds

// Pagination
define('ITEMS_PER_PAGE', 20);

// Date format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd/m/Y');
define('DISPLAY_DATETIME_FORMAT', 'd/m/Y H:i');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// File paths
define('FPDF_PATH', BASE_PATH . '/lib/fpdf');

// Development mode (set to false in production)
define('DEV_MODE', true);

// Error reporting
if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}

// Timezone
date_default_timezone_set('Africa/Addis_Ababa');

// Start session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
