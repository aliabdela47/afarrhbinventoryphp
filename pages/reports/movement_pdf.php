<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once FPDF_PATH . '/fpdf.php';

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

class MovementPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Item Movement Report', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'AfarRHB Inventory Management System', 0, 1, 'C');
        $this->Cell(0, 5, 'Period: ' . $_GET['start_date'] . ' to ' . $_GET['end_date'], 0, 1, 'C');
        $this->Cell(0, 5, 'Generated: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new MovementPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 8);

$pdf->Cell(25, 7, 'Date', 1);
$pdf->Cell(25, 7, 'Item Code', 1);
$pdf->Cell(50, 7, 'Item Name', 1);
$pdf->Cell(15, 7, 'Type', 1);
$pdf->Cell(20, 7, 'Quantity', 1);
$pdf->Cell(30, 7, 'Reference', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 7);

foreach ($movements as $movement) {
    $pdf->Cell(25, 6, date('d/m/Y', strtotime($movement['movement_date'])), 1);
    $pdf->Cell(25, 6, $movement['item_code'], 1);
    $pdf->Cell(50, 6, substr($movement['item_name'], 0, 30), 1);
    $pdf->Cell(15, 6, $movement['movement_type'], 1);
    $pdf->Cell(20, 6, number_format($movement['quantity']) . ' ' . $movement['unit'], 1);
    $pdf->Cell(30, 6, substr($movement['reference_type'], 0, 20), 1);
    $pdf->Ln();
}

$pdf->Output('D', 'movement_report_' . date('Y-m-d') . '.pdf');
