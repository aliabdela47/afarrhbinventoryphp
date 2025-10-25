<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

try {
    $stmt = $pdo->prepare("
        SELECT im.*, i.name as item_name, i.item_code, i.unit, u.full_name as moved_by_name
        FROM ITEMMOVEMENTS im
        JOIN ITEMS i ON im.item_id = i.id
        LEFT JOIN USERS u ON im.moved_by = u.id
        WHERE im.movement_date BETWEEN ? AND ?
        ORDER BY im.movement_date DESC, im.created_at DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    $movements = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="movement_report_' . date('Y-m-d') . '.xls"');

echo '<table border="1">';
echo '<thead>';
echo '<tr style="background-color: #6b46c1; color: white;">';
echo '<th colspan="8">Item Movement Report - AfarRHB Inventory</th>';
echo '</tr>';
echo '<tr style="background-color: #f0f0f0;">';
echo '<th>Date</th>';
echo '<th>Item Code</th>';
echo '<th>Item Name</th>';
echo '<th>Movement Type</th>';
echo '<th>Quantity</th>';
echo '<th>Unit</th>';
echo '<th>Reference Type</th>';
echo '<th>Moved By</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($movements as $movement) {
    echo '<tr>';
    echo '<td>' . date('d/m/Y', strtotime($movement['movement_date'])) . '</td>';
    echo '<td>' . htmlspecialchars($movement['item_code']) . '</td>';
    echo '<td>' . htmlspecialchars($movement['item_name']) . '</td>';
    echo '<td>' . $movement['movement_type'] . '</td>';
    echo '<td>' . number_format($movement['quantity']) . '</td>';
    echo '<td>' . htmlspecialchars($movement['unit']) . '</td>';
    echo '<td>' . htmlspecialchars($movement['reference_type']) . '</td>';
    echo '<td>' . htmlspecialchars($movement['moved_by_name'] ?: 'System') . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
