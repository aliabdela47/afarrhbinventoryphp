<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/auth.php';

requireAuth();
$pageTitle = 'Vehicle Reports - ' . APP_NAME;

include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-earmark-bar-graph"></i> Vehicle Reports</h2>
            <a href="../list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Vehicles
            </a>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-truck"></i> Vehicle List Report</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Generate a comprehensive list of all vehicles with their details, status, and maintenance information.</p>
                        <div class="d-flex gap-2">
                            <a href="vehicles_pdf.php" class="btn btn-danger" target="_blank">
                                <i class="bi bi-file-pdf"></i> PDF
                            </a>
                            <a href="vehicles_excel.php" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Vehicle Utilization Report</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">View detailed utilization metrics including assignments, mileage, and fuel consumption.</p>
                        <div class="d-flex gap-2">
                            <a href="utilization_pdf.php" class="btn btn-danger" target="_blank">
                                <i class="bi bi-file-pdf"></i> PDF
                            </a>
                            <a href="utilization_excel.php" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../../includes/footer.php'; ?>
