<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/auth.php';

requireAuth();
requireRole(['admin', 'manager']);

$pageTitle = 'New Assignment - ' . APP_NAME;
$vehicleId = $_GET['vehicle_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid request');
        redirect('create.php');
    }
    
    try {
        $assignmentNumber = generateUniqueCode('VAS', 3);
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO VEHICLEASSIGNMENTS (
                assignment_number, vehicle_id, driver_id, assigned_by, assignment_date,
                purpose, destination, starting_mileage, fuel_issued, status, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)
        ");
        
        $stmt->execute([
            $assignmentNumber,
            $_POST['vehicle_id'],
            $_POST['driver_id'],
            $_SESSION['user_id'],
            $_POST['assignment_date'],
            $_POST['purpose'],
            $_POST['destination'],
            $_POST['starting_mileage'] ?? 0,
            $_POST['fuel_issued'] ?? 0,
            $_POST['notes'] ?? null
        ]);
        
        $assignmentId = $pdo->lastInsertId();
        
        // Update vehicle status to assigned
        $stmt = $pdo->prepare("UPDATE VEHICLES SET status = 'assigned' WHERE id = ?");
        $stmt->execute([$_POST['vehicle_id']]);
        
        logAudit($pdo, 'CREATE', 'VEHICLEASSIGNMENTS', $assignmentId, null, $_POST);
        
        $pdo->commit();
        
        flash('success', 'Assignment created successfully');
        redirect('view.php?id=' . $assignmentId);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Assignment create error: " . $e->getMessage());
        flash('error', 'Error creating assignment');
    }
}

try {
    $stmt = $pdo->query("SELECT id, plate_number, make, model FROM VEHICLES WHERE status = 'available' AND is_active = 1");
    $vehicles = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT id, full_name, employee_code FROM EMPLIST WHERE is_active = 1");
    $drivers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Load data error: " . $e->getMessage());
    $vehicles = [];
    $drivers = [];
}

include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-plus-circle"></i> New Assignment</h2>
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
        
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Vehicle <span class="text-danger">*</span></label>
                                    <select name="vehicle_id" class="form-select" required>
                                        <option value="">Select Vehicle</option>
                                        <?php foreach ($vehicles as $v): ?>
                                        <option value="<?php echo $v['id']; ?>" <?php echo $v['id'] == $vehicleId ? 'selected' : ''; ?>>
                                            <?php echo e($v['plate_number'] . ' - ' . $v['make'] . ' ' . $v['model']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Driver <span class="text-danger">*</span></label>
                                    <select name="driver_id" class="form-select" required>
                                        <option value="">Select Driver</option>
                                        <?php foreach ($drivers as $d): ?>
                                        <option value="<?php echo $d['id']; ?>">
                                            <?php echo e($d['full_name'] . ' (' . $d['employee_code'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Assignment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="assignment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Starting Mileage (km)</label>
                                    <input type="number" name="starting_mileage" class="form-control" step="0.01">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Destination <span class="text-danger">*</span></label>
                                <input type="text" name="destination" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea name="purpose" class="form-control" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Fuel Issued (Liters)</label>
                                <input type="number" name="fuel_issued" class="form-control" step="0.01">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Create Assignment
                                </button>
                                <a href="list.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../../includes/footer.php'; ?>
