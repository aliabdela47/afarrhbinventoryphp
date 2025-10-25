<?php
/**
 * Helper Functions
 * AfarRHB Inventory Management System
 */

/**
 * Escape output to prevent XSS
 */
function e($string) {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a page
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Set flash message
 */
function flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Translate text based on current language
 */
function t($key) {
    global $translations;
    return $translations[$key] ?? $key;
}

/**
 * Load language file
 */
function loadLanguage($lang = 'en') {
    $langFile = BASE_PATH . "/lang/{$lang}.php";
    if (file_exists($langFile)) {
        return require $langFile;
    }
    return require BASE_PATH . "/lang/en.php";
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME]) || !hash_equals($_SESSION[CSRF_TOKEN_NAME], $token)) {
        return false;
    }
    return true;
}

/**
 * Get user IP address
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Log audit trail
 */
function logAudit($pdo, $action, $tableName, $recordId = null, $oldValue = null, $newValue = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO AUDITLOG (user_id, action, table_name, record_id, old_value, new_value, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = getUserIP();
        
        $stmt->execute([
            $userId,
            $action,
            $tableName,
            $recordId,
            $oldValue ? json_encode($oldValue) : null,
            $newValue ? json_encode($newValue) : null,
            $ipAddress
        ]);
    } catch (PDOException $e) {
        error_log("Audit log error: " . $e->getMessage());
    }
}

/**
 * Sanitize filename
 */
function sanitizeFilename($filename) {
    // Remove any path components
    $filename = basename($filename);
    
    // Get extension
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    
    // Clean the filename
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
    
    // Generate unique name
    $uniqueName = $name . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    
    return $uniqueName;
}

/**
 * Validate file upload
 */
function validateFileUpload($file) {
    $errors = [];
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "No file uploaded";
        return $errors;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload error code: " . $file['error'];
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        $errors[] = "File size exceeds maximum allowed size of " . (UPLOAD_MAX_SIZE / 1024 / 1024) . "MB";
    }
    
    // Check file type
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_FILE_TYPES)) {
        $errors[] = "File type not allowed. Allowed types: " . implode(', ', ALLOWED_FILE_TYPES);
    }
    
    return $errors;
}

/**
 * Format date for display
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'ETB') {
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Generate unique code
 */
function generateUniqueCode($prefix, $length = 6) {
    $timestamp = time();
    $random = strtoupper(substr(md5($timestamp . rand()), 0, $length));
    return $prefix . '-' . date('Y') . '-' . $random;
}

/**
 * Paginate results
 */
function paginate($totalItems, $currentPage = 1, $perPage = ITEMS_PER_PAGE) {
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Ethiopian to Gregorian date conversion (using JDN)
 */
function ethiopianToGregorian($ethYear, $ethMonth, $ethDay) {
    // Convert Ethiopian date to JDN
    $jdn = ethiopianToJDN($ethYear, $ethMonth, $ethDay);
    
    // Convert JDN to Gregorian
    return jdnToGregorian($jdn);
}

/**
 * Ethiopian date to JDN
 */
function ethiopianToJDN($year, $month, $day) {
    $jdn = (1723856 + 365) +
           365 * ($year - 1) +
           floor($year / 4) +
           30 * $month +
           $day - 31;
    return $jdn;
}

/**
 * JDN to Gregorian date
 */
function jdnToGregorian($jdn) {
    $a = $jdn + 32044;
    $b = floor((4 * $a + 3) / 146097);
    $c = $a - floor((146097 * $b) / 4);
    $d = floor((4 * $c + 3) / 1461);
    $e = $c - floor((1461 * $d) / 4);
    $m = floor((5 * $e + 2) / 153);
    
    $day = $e - floor((153 * $m + 2) / 5) + 1;
    $month = $m + 3 - 12 * floor($m / 10);
    $year = 100 * $b + $d - 4800 + floor($m / 10);
    
    return [
        'year' => $year,
        'month' => $month,
        'day' => $day,
        'date' => sprintf('%04d-%02d-%02d', $year, $month, $day)
    ];
}

/**
 * Gregorian to Ethiopian date conversion
 */
function gregorianToEthiopian($gregYear, $gregMonth, $gregDay) {
    $jdn = gregorianToJDN($gregYear, $gregMonth, $gregDay);
    return jdnToEthiopian($jdn);
}

/**
 * Gregorian to JDN
 */
function gregorianToJDN($year, $month, $day) {
    $a = floor((14 - $month) / 12);
    $y = $year + 4800 - $a;
    $m = $month + 12 * $a - 3;
    
    return $day + floor((153 * $m + 2) / 5) + 365 * $y + floor($y / 4) - floor($y / 100) + floor($y / 400) - 32045;
}

/**
 * JDN to Ethiopian date
 */
function jdnToEthiopian($jdn) {
    $r = ($jdn - 1723856) % 1461;
    $n = ($r % 365) + 365 * floor($r / 1460);
    
    $year = 4 * floor(($jdn - 1723856) / 1461) + floor($r / 365) - floor($r / 1460);
    $month = floor($n / 30) + 1;
    $day = ($n % 30) + 1;
    
    return [
        'year' => $year,
        'month' => $month,
        'day' => $day,
        'date' => sprintf('%04d-%02d-%02d', $year, $month, $day)
    ];
}

/**
 * Get Ethiopian month name
 */
function getEthiopianMonthName($month, $lang = 'en') {
    $months = [
        'en' => [
            1 => 'Meskerem', 2 => 'Tikimt', 3 => 'Hidar', 4 => 'Tahsas',
            5 => 'Tir', 6 => 'Yekatit', 7 => 'Megabit', 8 => 'Miazia',
            9 => 'Ginbot', 10 => 'Sene', 11 => 'Hamle', 12 => 'Nehase', 13 => 'Pagume'
        ],
        'am' => [
            1 => 'መስከረም', 2 => 'ጥቅምት', 3 => 'ኅዳር', 4 => 'ታኅሣሥ',
            5 => 'ጥር', 6 => 'የካቲት', 7 => 'መጋቢት', 8 => 'ሚያዝያ',
            9 => 'ግንቦት', 10 => 'ሰኔ', 11 => 'ሐምሌ', 12 => 'ነሐሴ', 13 => 'ጳጉሜ'
        ]
    ];
    
    return $months[$lang][$month] ?? $month;
}

// Load translations for current language
$currentLang = $_SESSION['lang'] ?? 'en';
$translations = loadLanguage($currentLang);
