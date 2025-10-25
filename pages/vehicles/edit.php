<?php
/**
 * Vehicle Edit
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();
requireRole(['admin', 'manager']);

$vehicleId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($vehicleId === 0) {
    flash('error', 'Invalid vehicle ID');
    redirect('list.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid request. Please try again.');
        redirect('edit.php?id=' . $vehicleId);
    }
    
    try {
        // Get old values for audit
        $stmt = $pdo->prepare("SELECT * FROM VEHICLES WHERE id = ?");
        $stmt->execute([$vehicleId]);
        $oldValues = $stmt->fetch();
        
        $stmt = $pdo->prepare("
            UPDATE VEHICLES SET 
                plate_number = ?, vehicle_type = ?, make = ?, model = ?, year = ?,
                color = ?, fuel_type = ?, capacity = ?, status = ?, mileage = ?,
                last_service_date = ?, next_service_date = ?, insurance_expiry = ?, notes = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['plate_number'],
            $_POST['vehicle_type'],
            $_POST['make'],
            $_POST['model'],
            $_POST['year'],
            $_POST['color'],
            $_POST['fuel_type'],
            $_POST['capacity'],
            $_POST['status'],
            $_POST['mileage'],
            $_POST['last_service_date'] ?: null,
            $_POST['next_service_date'] ?: null,
            $_POST['insurance_expiry'] ?: null,
            $_POST['notes'],
            $vehicleId
        ]);
        
        logAudit($pdo, 'UPDATE', 'VEHICLES', $vehicleId, $oldValues, $_POST);
        
        flash('success', 'Vehicle updated successfully');
        redirect('view.php?id=' . $vehicleId);
        
    } catch (PDOException $e) {
        error_log("Vehicle update error: " . $e->getMessage());
        flash('error', 'Error updating vehicle. Please try again.');
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM VEHICLES WHERE id = ?");
    $stmt->execute([$vehicleId]);
    $vehicle = $stmt->fetch();
    
    if (!$vehicle) {
        flash('error', 'Vehicle not found');
        redirect('list.php');
    }
} catch (PDOException $e) {
    error_log("Vehicle load error: " . $e->getMessage());
    flash('error', 'Error loading vehicle');
    redirect('list.php');
}

$pageTitle = 'Edit Vehicle - ' . APP_NAME;

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-pencil"></i> Edit Vehicle</h2>
            <div>
                <a href="view.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-info">
                    <i class="bi bi-eye"></i> View
                </a>
                <a href="list.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Plate Number <span class="text-danger">*</span></label>
                                    <input type="text" name="plate_number" class="form-control" value="<?php echo e($vehicle['plate_number']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                                    <select name="vehicle_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <?php foreach (['Sedan', 'SUV', 'Van', 'Pickup', 'Ambulance', 'Truck'] as $type): ?>
                                        <option value="<?php echo $type; ?>" <?php echo $vehicle['vehicle_type'] === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Make <span class="text-danger">*</span></label>
                                    <input type="text" name="make" class="form-control" value="<?php echo e($vehicle['make']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Model <span class="text-danger">*</span></label>
                                    <input type="text" name="model" class="form-control" value="<?php echo e($vehicle['model']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Year <span class="text-danger">*</span></label>
                                    <input type="number" name="year" class="form-control" value="<?php echo $vehicle['year']; ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Color</label>
                                    <input type="text" name="color" class="form-control" value="<?php echo e($vehicle['color']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fuel Type</label>
                                    <select name="fuel_type" class="form-select">
                                        <option value="">Select Fuel Type</option>
                                        <?php foreach (['Petrol', 'Diesel', 'Electric', 'Hybrid'] as $fuel): ?>
                                        <option value="<?php echo $fuel; ?>" <?php echo $vehicle['fuel_type'] === $fuel ? 'selected' : ''; ?>><?php echo $fuel; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Capacity (Passengers)</label>
                                    <input type="number" name="capacity" class="form-control" value="<?php echo $vehicle['capacity']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <?php foreach (['available', 'assigned', 'maintenance', 'inactive'] as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo $vehicle['status'] === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Current Mileage (km)</label>
                                    <input type="number" name="mileage" class="form-control" step="0.01" value="<?php echo $vehicle['mileage']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Insurance Expiry</label>
                                    <input type="date" name="insurance_expiry" class="form-control" value="<?php echo $vehicle['insurance_expiry']; ?>">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Last Service Date</label>
                                    <input type="date" name="last_service_date" class="form-control" value="<?php echo $vehicle['last_service_date']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Next Service Date</label>
                                    <input type="date" name="next_service_date" class="form-control" value="<?php echo $vehicle['next_service_date']; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"><?php echo e($vehicle['notes']); ?></textarea>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Vehicle
                                </button>
                                <a href="view.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
