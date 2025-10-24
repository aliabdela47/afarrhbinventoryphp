<?php
/**
 * Helper Functions
 */

/**
 * Get base URL
 */
function baseUrl($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * HTML escape
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF input field
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Validate CSRF token from request
 */
function validateCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
}

// ============================================
// Internationalization (i18n)
// ============================================

/**
 * Get translated text
 */
function __($key, $default = null) {
    static $translations = null;
    
    if ($translations === null) {
        $lang = $_SESSION['lang'] ?? DEFAULT_LANG;
        $langFile = BASE_PATH . "/lang/{$lang}.php";
        
        if (file_exists($langFile)) {
            $translations = require $langFile;
        } else {
            $translations = [];
        }
    }
    
    return $translations[$key] ?? $default ?? $key;
}

/**
 * Get current language
 */
function currentLang() {
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}

/**
 * Set language
 */
function setLang($lang) {
    if (in_array($lang, ['en', 'am'])) {
        $_SESSION['lang'] = $lang;
    }
}

/**
 * Get current calendar type
 */
function currentCalendar() {
    return $_SESSION['calendar'] ?? DEFAULT_CALENDAR;
}

/**
 * Set calendar type
 */
function setCalendar($calendar) {
    if (in_array($calendar, ['gregorian', 'ethiopian'])) {
        $_SESSION['calendar'] = $calendar;
    }
}

// ============================================
// Ethiopian Calendar Functions
// ============================================

/**
 * Convert Gregorian date to Ethiopian date
 * 
 * @param string $gregorianDate Date in Y-m-d format
 * @return array Ethiopian date ['year', 'month', 'day']
 */
function gregorianToEthiopian($gregorianDate) {
    $date = new DateTime($gregorianDate);
    $timestamp = $date->getTimestamp();
    
    // Ethiopian calendar starts on September 11 (or 12 in leap years)
    $ethiopianEpoch = mktime(0, 0, 0, 9, 11, 2007); // Jan 1, 2000 EC = Sept 11, 2007 GC
    
    $daysSinceEpoch = floor(($timestamp - $ethiopianEpoch) / 86400);
    
    // Calculate Ethiopian year
    $ethiopianYear = 2000 + floor($daysSinceEpoch / 365.25);
    
    // Simple approximation - for production use a proper library
    $dayOfYear = $daysSinceEpoch % 365;
    $ethiopianMonth = floor($dayOfYear / 30) + 1;
    $ethiopianDay = ($dayOfYear % 30) + 1;
    
    // Adjust for month boundaries
    if ($ethiopianMonth > 13) {
        $ethiopianMonth = 1;
        $ethiopianYear++;
    }
    
    return [
        'year' => $ethiopianYear,
        'month' => $ethiopianMonth,
        'day' => $ethiopianDay
    ];
}

/**
 * Convert Ethiopian date to Gregorian date
 * 
 * @param int $year Ethiopian year
 * @param int $month Ethiopian month (1-13)
 * @param int $day Ethiopian day
 * @return string Gregorian date in Y-m-d format
 */
function ethiopianToGregorian($year, $month, $day) {
    // Ethiopian calendar starts on September 11 (or 12 in leap years)
    $baseYear = 2007 + ($year - 2000);
    
    // Calculate days from start of Ethiopian year
    $dayOfYear = (($month - 1) * 30) + $day;
    
    // Create base date (Sept 11 of the Gregorian year)
    $baseDate = new DateTime("$baseYear-09-11");
    
    // Add the days
    $baseDate->modify("+$dayOfYear days");
    
    return $baseDate->format('Y-m-d');
}

/**
 * Format date based on current calendar setting
 */
function formatDate($date, $format = 'Y-m-d') {
    if (empty($date) || $date === '0000-00-00') {
        return '';
    }
    
    $calendar = currentCalendar();
    
    if ($calendar === 'ethiopian') {
        $eth = gregorianToEthiopian($date);
        return sprintf('%04d-%02d-%02d', $eth['year'], $eth['month'], $eth['day']);
    }
    
    return date($format, strtotime($date));
}

/**
 * Get Ethiopian month names
 */
function getEthiopianMonths() {
    return [
        1 => 'Meskerem',
        2 => 'Tikimt',
        3 => 'Hidar',
        4 => 'Tahsas',
        5 => 'Tir',
        6 => 'Yekatit',
        7 => 'Megabit',
        8 => 'Miazia',
        9 => 'Ginbot',
        10 => 'Sene',
        11 => 'Hamle',
        12 => 'Nehase',
        13 => 'Pagumen'
    ];
}

// ============================================
// File Upload Functions
// ============================================

/**
 * Validate uploaded file
 */
function validateUpload($file) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed';
        return $errors;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'File size exceeds maximum allowed size';
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_FILE_TYPES)) {
        $errors[] = 'File type not allowed';
    }
    
    return $errors;
}

/**
 * Upload file
 */
function uploadFile($file, $directory = DOCUMENT_DIR) {
    $errors = validateUpload($file);
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Create directory if it doesn't exist
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $directory . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'relative_path' => str_replace(BASE_PATH . '/', '', $filepath)
        ];
    }
    
    return ['success' => false, 'errors' => ['Failed to save file']];
}

// ============================================
// Utility Functions
// ============================================

/**
 * Format currency
 */
function formatCurrency($amount) {
    return number_format($amount, 2) . ' ETB';
}

/**
 * Format number
 */
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals);
}

/**
 * Get flash message
 */
function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

/**
 * Set flash message
 */
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

/**
 * Generate pagination HTML
 */
function pagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . '">' . __('Previous') . '</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">' . __('Previous') . '</span></li>';
    }
    
    // Page numbers
    for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
        $active = $i === $currentPage ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . '">' . __('Next') . '</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">' . __('Next') . '</span></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Sanitize input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return trim(strip_tags($data));
}
