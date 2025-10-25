<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/auth.php';

requireAuth();

$assignmentId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($assignmentId === 0) {
    flash('error', 'Invalid assignment ID');
    redirect('list.php');
}

try {
    $stmt = $pdo->prepare("
        SELECT va.*, v.plate_number, v.make, v.model, v.vehicle_type,
               e.full_name as driver_name, e.phone as driver_phone, e.employee_code,
               u.full_name as assigned_by_name
        FROM VEHICLEASSIGNMENTS va
        JOIN VEHICLES v ON va.vehicle_id = v.id
        JOIN EMPLIST e ON va.driver_id = e.id
        LEFT JOIN USERS u ON va.assigned_by = u.id
        WHERE va.id = ?
    ");
    $stmt->execute([$assignmentId]);
    $assignment = $stmt->fetch();
    
    if (!$assignment) {
        flash('error', 'Assignment not found');
        redirect('list.php');
    }
} catch (PDOException $e) {
    error_log("Assignment view error: " . $e->getMessage());
    flash('error', 'Error loading assignment');
    redirect('list.php');
}

$pageTitle = 'Assignment Details - ' . APP_NAME;

include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-calendar-check"></i> Assignment Details</h2>
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Assignment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Assignment Number</label>
                                <p class="mb-0"><strong><?php echo e($assignment['assignment_number']); ?></strong></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Status</label>
                                <p class="mb-0">
                                    <span class="badge <?php 
                                        echo ['active' => 'bg-info', 'completed' => 'bg-success', 'cancelled' => 'bg-secondary'][$assignment['status']]; 
                                    ?>">
                                        <?php echo ucfirst($assignment['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Vehicle</label>
                                <p class="mb-0">
                                    <strong><?php echo e($assignment['plate_number']); ?></strong><br>
                                    <small class="text-muted"><?php echo e($assignment['make'] . ' ' . $assignment['model']); ?></small>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Driver</label>
                                <p class="mb-0">
                                    <strong><?php echo e($assignment['driver_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo e($assignment['employee_code']); ?> | <?php echo e($assignment['driver_phone']); ?></small>
                                </p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Assignment Date</label>
                                <p class="mb-0"><?php echo formatDate($assignment['assignment_date'], DISPLAY_DATE_FORMAT); ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Return Date</label>
                                <p class="mb-0"><?php echo $assignment['return_date'] ? formatDate($assignment['return_date'], DISPLAY_DATE_FORMAT) : '-'; ?></p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted small">Destination</label>
                            <p class="mb-0"><?php echo e($assignment['destination']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted small">Purpose</label>
                            <p class="mb-0"><?php echo nl2br(e($assignment['purpose'])); ?></p>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Starting Mileage</label>
                                <p class="mb-0"><?php echo $assignment['starting_mileage'] ? number_format($assignment['starting_mileage'], 0) . ' km' : '-'; ?></p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Ending Mileage</label>
                                <p class="mb-0"><?php echo $assignment['ending_mileage'] ? number_format($assignment['ending_mileage'], 0) . ' km' : '-'; ?></p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Fuel Issued</label>
                                <p class="mb-0"><?php echo $assignment['fuel_issued'] ? number_format($assignment['fuel_issued'], 2) . ' L' : '-'; ?></p>
                            </div>
                        </div>
                        
                        <?php if ($assignment['notes']): ?>
                        <div class="mb-3">
                            <label class="text-muted small">Notes</label>
                            <p class="mb-0"><?php echo nl2br(e($assignment['notes'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-12">
                                <label class="text-muted small">Assigned By</label>
                                <p class="mb-0"><?php echo e($assignment['assigned_by_name'] ?? 'System'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <a href="../view.php?id=<?php echo $assignment['vehicle_id']; ?>" class="btn btn-sm btn-info w-100 mb-2">
                            <i class="bi bi-truck"></i> View Vehicle
                        </a>
                        <a href="../map.php" class="btn btn-sm btn-secondary w-100">
                            <i class="bi bi-geo-alt"></i> Track on Map
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../../includes/footer.php'; ?>
