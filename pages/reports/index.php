<?php
/**
 * Reports Dashboard
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();

$pageTitle = t('reports') . ' - ' . APP_NAME;

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="mb-4">
            <h2><i class="bi bi-file-earmark-text"></i> <?php echo e(t('reports')); ?></h2>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-box-seam"></i> <?php echo e(t('inventory_report')); ?></h5>
                    </div>
                    <div class="card-body">
                        <p>Generate inventory stock reports</p>
                        <div class="btn-group">
                            <a href="inventory_pdf.php" class="btn btn-danger" target="_blank">
                                <i class="bi bi-file-pdf"></i> <?php echo e(t('export_pdf')); ?>
                            </a>
                            <a href="inventory_excel.php" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> <?php echo e(t('export_excel')); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-arrow-left-right"></i> <?php echo e(t('movement_report')); ?></h5>
                    </div>
                    <div class="card-body">
                        <p>Generate item movement reports</p>
                        <div class="btn-group">
                            <a href="movement_pdf.php" class="btn btn-danger" target="_blank">
                                <i class="bi bi-file-pdf"></i> <?php echo e(t('export_pdf')); ?>
                            </a>
                            <a href="movement_excel.php" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> <?php echo e(t('export_excel')); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-clipboard-check"></i> <?php echo e(t('request_report')); ?></h5>
                    </div>
                    <div class="card-body">
                        <p>Generate request reports</p>
                        <div class="btn-group">
                            <a href="request_pdf.php" class="btn btn-danger" target="_blank">
                                <i class="bi bi-file-pdf"></i> <?php echo e(t('export_pdf')); ?>
                            </a>
                            <a href="request_excel.php" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> <?php echo e(t('export_excel')); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-arrow-right-circle"></i> <?php echo e(t('issuance_report')); ?></h5>
                    </div>
                    <div class="card-body">
                        <p>Generate issuance reports</p>
                        <div class="btn-group">
                            <a href="issuance_pdf.php" class="btn btn-danger" target="_blank">
                                <i class="bi bi-file-pdf"></i> <?php echo e(t('export_pdf')); ?>
                            </a>
                            <a href="issuance_excel.php" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> <?php echo e(t('export_excel')); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
