<?php
/**
 * Change Language API
 * AfarRHB Inventory Management System
 */

require_once '../config/config.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

// Require authentication
requireAuth();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate language
$lang = $input['lang'] ?? '';
if (!in_array($lang, ['en', 'am'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid language']);
    exit;
}

// Set language in session
$_SESSION['lang'] = $lang;

// Return success
http_response_code(200);
echo json_encode(['success' => true, 'lang' => $lang]);
