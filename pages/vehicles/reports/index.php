<?php
/**
 * Vehicle Reports Dashboard
 * AfarRHB Inventory Management System
 */

require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/auth.php';

requireAuth();

$pageTitle = t('vehicle') . ' ' . t('reports') . ' - ' . APP_NAME;

include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="mb-4">
            <h2><i class="bi bi-truck"></i> <?php echo e(t('vehicle')); ?> <?php echo e(t('reports')); ?></h2>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo e(t('vehicle')); ?> <?php echo e(t('inventory_report')); ?></h5>
                    </div>
                    <div class="card-body">
                        <p>Generate vehicle inventory reports</p>
                        <div class="btn-group">
                            <a href="vehicles_pdf.php" class="btn btn-danger" target="_blank">
                                <i class="bi bi-file-pdf"></i> <?php echo e(t('export_pdf')); ?>
                            </a>
                            <a href="vehicles_excel.php" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> <?php echo e(t('export_excel')); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo e(t('utilization_report')); ?></h5>
                    </div>
                    <div class="card-body">
                        <p>Generate vehicle utilization and assignment reports</p>
                        <div class="btn-group">
                            <a href="utilization_pdf.php" class="btn btn-danger" target="_blank">
                                <i class="bi bi-file-pdf"></i> <?php echo e(t('export_pdf')); ?>
                            </a>
                            <a href="utilization_excel.php" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> <?php echo e(t('export_excel')); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../../includes/footer.php'; ?>
