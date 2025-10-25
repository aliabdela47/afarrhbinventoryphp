<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['theme']) && in_array($data['theme'], ['light', 'dark'])) {
        $_SESSION['theme'] = $data['theme'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid theme']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
