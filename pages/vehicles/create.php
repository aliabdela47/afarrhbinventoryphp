<?php
/**
 * Vehicle Create
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication and authorization
requireAuth();
requireRole(['admin', 'manager']);

$pageTitle = 'Add New Vehicle - ' . APP_NAME;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid request. Please try again.');
        redirect('create.php');
    }
    
    try {
        // Generate vehicle code
        $vehicleCode = generateUniqueCode('VEH', 3);
        
        $stmt = $pdo->prepare("
            INSERT INTO VEHICLES (
                vehicle_code, plate_number, vehicle_type, make, model, year, 
                color, fuel_type, capacity, status, mileage, last_service_date, 
                next_service_date, insurance_expiry, notes, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([
            $vehicleCode,
            $_POST['plate_number'],
            $_POST['vehicle_type'],
            $_POST['make'],
            $_POST['model'],
            $_POST['year'],
            $_POST['color'],
            $_POST['fuel_type'],
            $_POST['capacity'],
            $_POST['status'],
            $_POST['mileage'] ?? 0,
            $_POST['last_service_date'] ?: null,
            $_POST['next_service_date'] ?: null,
            $_POST['insurance_expiry'] ?: null,
            $_POST['notes'] ?? null
        ]);
        
        $vehicleId = $pdo->lastInsertId();
        
        // Log audit
        logAudit($pdo, 'CREATE', 'VEHICLES', $vehicleId, null, $_POST);
        
        flash('success', 'Vehicle added successfully');
        redirect('view.php?id=' . $vehicleId);
        
    } catch (PDOException $e) {
        error_log("Vehicle create error: " . $e->getMessage());
        flash('error', 'Error adding vehicle. Please try again.');
    }
}

// Include header
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-plus-circle"></i> Add New Vehicle</h2>
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
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
                                    <input type="text" name="plate_number" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                                    <select name="vehicle_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="Sedan">Sedan</option>
                                        <option value="SUV">SUV</option>
                                        <option value="Van">Van</option>
                                        <option value="Pickup">Pickup</option>
                                        <option value="Ambulance">Ambulance</option>
                                        <option value="Truck">Truck</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Make <span class="text-danger">*</span></label>
                                    <input type="text" name="make" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Model <span class="text-danger">*</span></label>
                                    <input type="text" name="model" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Year <span class="text-danger">*</span></label>
                                    <input type="number" name="year" class="form-control" min="1900" max="2100" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Color</label>
                                    <input type="text" name="color" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fuel Type</label>
                                    <select name="fuel_type" class="form-select">
                                        <option value="">Select Fuel Type</option>
                                        <option value="Petrol">Petrol</option>
                                        <option value="Diesel">Diesel</option>
                                        <option value="Electric">Electric</option>
                                        <option value="Hybrid">Hybrid</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Capacity (Passengers)</label>
                                    <input type="number" name="capacity" class="form-control" min="1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="available">Available</option>
                                        <option value="assigned">Assigned</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Current Mileage (km)</label>
                                    <input type="number" name="mileage" class="form-control" step="0.01" value="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Insurance Expiry</label>
                                    <input type="date" name="insurance_expiry" class="form-control">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Last Service Date</label>
                                    <input type="date" name="last_service_date" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Next Service Date</label>
                                    <input type="date" name="next_service_date" class="form-control">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Vehicle
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

<?php include '../../includes/footer.php'; ?>
