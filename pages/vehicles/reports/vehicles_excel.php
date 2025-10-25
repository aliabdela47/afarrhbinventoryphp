<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/auth.php';

requireAuth();

try {
    $stmt = $pdo->query("SELECT * FROM VEHICLES ORDER BY plate_number");
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="vehicles_report_' . date('Y-m-d') . '.xls"');

echo '<table border="1">';
echo '<thead>';
echo '<tr style="background-color: #6b46c1; color: white;">';
echo '<th colspan="10">Vehicle List Report - AfarRHB Inventory</th>';
echo '</tr>';
echo '<tr style="background-color: #f0f0f0;">';
echo '<th>Vehicle Code</th>';
echo '<th>Plate Number</th>';
echo '<th>Type</th>';
echo '<th>Make</th>';
echo '<th>Model</th>';
echo '<th>Year</th>';
echo '<th>Status</th>';
echo '<th>Mileage (km)</th>';
echo '<th>Next Service</th>';
echo '<th>Insurance Expiry</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($vehicles as $vehicle) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($vehicle['vehicle_code']) . '</td>';
    echo '<td>' . htmlspecialchars($vehicle['plate_number']) . '</td>';
    echo '<td>' . htmlspecialchars($vehicle['vehicle_type']) . '</td>';
    echo '<td>' . htmlspecialchars($vehicle['make']) . '</td>';
    echo '<td>' . htmlspecialchars($vehicle['model']) . '</td>';
    echo '<td>' . $vehicle['year'] . '</td>';
    echo '<td>' . ucfirst($vehicle['status']) . '</td>';
    echo '<td>' . number_format($vehicle['mileage'], 0) . '</td>';
    echo '<td>' . ($vehicle['next_service_date'] ?: '-') . '</td>';
    echo '<td>' . ($vehicle['insurance_expiry'] ?: '-') . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
