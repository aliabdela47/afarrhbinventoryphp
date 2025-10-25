<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/auth.php';
require_once FPDF_PATH . '/fpdf.php';

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

class UtilizationPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Vehicle Utilization Report', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'AfarRHB Inventory Management System', 0, 1, 'C');
        $this->Cell(0, 5, 'Generated: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new UtilizationPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 9);

$pdf->Cell(35, 7, 'Plate Number', 1);
$pdf->Cell(40, 7, 'Make/Model', 1);
$pdf->Cell(30, 7, 'Mileage (km)', 1);
$pdf->Cell(30, 7, 'Assignments', 1);
$pdf->Cell(30, 7, 'Fuel Used (L)', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 8);

foreach ($vehicles as $vehicle) {
    $pdf->Cell(35, 6, $vehicle['plate_number'], 1);
    $pdf->Cell(40, 6, $vehicle['make'] . ' ' . $vehicle['model'], 1);
    $pdf->Cell(30, 6, number_format($vehicle['mileage'], 0), 1);
    $pdf->Cell(30, 6, $vehicle['assignment_count'], 1);
    $pdf->Cell(30, 6, number_format($vehicle['total_fuel'] ?: 0, 2), 1);
    $pdf->Ln();
}

$pdf->Output('D', 'vehicle_utilization_' . date('Y-m-d') . '.pdf');
