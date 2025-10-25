<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/auth.php';
require_once FPDF_PATH . '/fpdf.php';

requireAuth();

try {
    $stmt = $pdo->query("SELECT * FROM VEHICLES ORDER BY plate_number");
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

class VehiclesPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Vehicle List Report', 0, 1, 'C');
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

$pdf = new VehiclesPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 9);

// Table header
$pdf->Cell(30, 7, 'Plate Number', 1);
$pdf->Cell(40, 7, 'Make/Model', 1);
$pdf->Cell(15, 7, 'Year', 1);
$pdf->Cell(25, 7, 'Type', 1);
$pdf->Cell(25, 7, 'Status', 1);
$pdf->Cell(30, 7, 'Mileage (km)', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 8);

foreach ($vehicles as $vehicle) {
    $pdf->Cell(30, 6, $vehicle['plate_number'], 1);
    $pdf->Cell(40, 6, $vehicle['make'] . ' ' . $vehicle['model'], 1);
    $pdf->Cell(15, 6, $vehicle['year'], 1);
    $pdf->Cell(25, 6, $vehicle['vehicle_type'], 1);
    $pdf->Cell(25, 6, ucfirst($vehicle['status']), 1);
    $pdf->Cell(30, 6, number_format($vehicle['mileage'], 0), 1);
    $pdf->Ln();
}

$pdf->Output('D', 'vehicles_report_' . date('Y-m-d') . '.pdf');
