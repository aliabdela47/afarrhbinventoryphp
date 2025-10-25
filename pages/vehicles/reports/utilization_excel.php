<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/auth.php';

requireAuth();

try {
    $stmt = $pdo->query("
        SELECT v.*, COUNT(va.id) as assignment_count,
               SUM(va.fuel_issued) as total_fuel
        FROM VEHICLES v
        LEFT JOIN VEHICLEASSIGNMENTS va ON v.id = va.vehicle_id
        GROUP BY v.id
        ORDER BY v.plate_number
    ");
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="vehicle_utilization_' . date('Y-m-d') . '.xls"');

echo '<table border="1">';
echo '<thead>';
echo '<tr style="background-color: #6b46c1; color: white;">';
echo '<th colspan="7">Vehicle Utilization Report - AfarRHB Inventory</th>';
echo '</tr>';
echo '<tr style="background-color: #f0f0f0;">';
echo '<th>Plate Number</th>';
echo '<th>Make/Model</th>';
echo '<th>Type</th>';
echo '<th>Mileage (km)</th>';
echo '<th>Total Assignments</th>';
echo '<th>Total Fuel Used (L)</th>';
echo '<th>Status</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($vehicles as $vehicle) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($vehicle['plate_number']) . '</td>';
    echo '<td>' . htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) . '</td>';
    echo '<td>' . htmlspecialchars($vehicle['vehicle_type']) . '</td>';
    echo '<td>' . number_format($vehicle['mileage'], 0) . '</td>';
    echo '<td>' . $vehicle['assignment_count'] . '</td>';
    echo '<td>' . number_format($vehicle['total_fuel'] ?: 0, 2) . '</td>';
    echo '<td>' . ucfirst($vehicle['status']) . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
