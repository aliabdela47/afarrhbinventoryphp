<?php
/**
 * Change Calendar API
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

// Validate calendar
$calendar = $input['calendar'] ?? '';
if (!in_array($calendar, ['gregorian', 'ethiopian'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid calendar']);
    exit;
}

// Set calendar in session
$_SESSION['calendar'] = $calendar;

// Return success
http_response_code(200);
echo json_encode(['success' => true, 'calendar' => $calendar]);
