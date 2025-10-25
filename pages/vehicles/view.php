<?php
/**
 * Vehicle View
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication
requireAuth();

$vehicleId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($vehicleId === 0) {
    flash('error', 'Invalid vehicle ID');
    redirect('list.php');
}

try {
    // Fetch vehicle details
    $stmt = $pdo->prepare("SELECT * FROM VEHICLES WHERE id = ?");
    $stmt->execute([$vehicleId]);
    $vehicle = $stmt->fetch();
    
    if (!$vehicle) {
        flash('error', 'Vehicle not found');
        redirect('list.php');
    }
    
    // Get latest location
    $stmt = $pdo->prepare("
        SELECT * FROM VEHICLE_LOCATIONS 
        WHERE vehicle_id = ? 
        ORDER BY recorded_at DESC LIMIT 1
    ");
    $stmt->execute([$vehicleId]);
    $location = $stmt->fetch();
    
    // Get active assignment
    $stmt = $pdo->prepare("
        SELECT va.*, e.full_name as driver_name, e.phone as driver_phone
        FROM VEHICLEASSIGNMENTS va
        JOIN EMPLIST e ON va.driver_id = e.id
        WHERE va.vehicle_id = ? AND va.status = 'active'
        ORDER BY va.assignment_date DESC
        LIMIT 1
    ");
    $stmt->execute([$vehicleId]);
    $activeAssignment = $stmt->fetch();
    
    // Get assignment history
    $stmt = $pdo->prepare("
        SELECT va.*, e.full_name as driver_name, u.full_name as assigned_by_name
        FROM VEHICLEASSIGNMENTS va
        JOIN EMPLIST e ON va.driver_id = e.id
        LEFT JOIN USERS u ON va.assigned_by = u.id
        WHERE va.vehicle_id = ?
        ORDER BY va.assignment_date DESC
        LIMIT 10
    ");
    $stmt->execute([$vehicleId]);
    $assignmentHistory = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Vehicle view error: " . $e->getMessage());
    flash('error', 'Error loading vehicle details');
    redirect('list.php');
}

$pageTitle = $vehicle['plate_number'] . ' - Vehicles - ' . APP_NAME;

// Include header
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    #vehicleMap {
        height: 300px;
        border-radius: 8px;
    }
</style>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-truck"></i> Vehicle Details</h2>
            <div>
                <a href="list.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
                <?php if (hasAnyRole(['admin', 'manager'])): ?>
                <a href="edit.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row">
            <!-- Vehicle Information -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Vehicle Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Vehicle Code</label>
                                <p class="mb-0"><strong><?php echo e($vehicle['vehicle_code']); ?></strong></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Plate Number</label>
                                <p class="mb-0"><strong><?php echo e($vehicle['plate_number']); ?></strong></p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Make</label>
                                <p class="mb-0"><?php echo e($vehicle['make']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Model</label>
                                <p class="mb-0"><?php echo e($vehicle['model']); ?></p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Year</label>
                                <p class="mb-0"><?php echo e($vehicle['year']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Color</label>
                                <p class="mb-0"><?php echo e($vehicle['color']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Vehicle Type</label>
                                <p class="mb-0"><?php echo e($vehicle['vehicle_type']); ?></p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Fuel Type</label>
                                <p class="mb-0"><?php echo e($vehicle['fuel_type']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Capacity</label>
                                <p class="mb-0"><?php echo e($vehicle['capacity']); ?> passengers</p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Status</label>
                                <p class="mb-0">
                                    <?php 
                                    $badgeClass = [
                                        'available' => 'bg-success',
                                        'assigned' => 'bg-info',
                                        'maintenance' => 'bg-warning',
                                        'inactive' => 'bg-secondary'
                                    ][$vehicle['status']];
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo ucfirst($vehicle['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Current Mileage</label>
                                <p class="mb-0"><?php echo number_format($vehicle['mileage'], 0); ?> km</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Insurance Expiry</label>
                                <p class="mb-0">
                                    <?php 
                                    if ($vehicle['insurance_expiry']) {
                                        $expiryDays = (strtotime($vehicle['insurance_expiry']) - time()) / (60 * 60 * 24);
                                        $expiryClass = $expiryDays <= 60 ? 'text-danger' : 'text-muted';
                                        echo '<span class="' . $expiryClass . '">' . formatDate($vehicle['insurance_expiry'], DISPLAY_DATE_FORMAT) . '</span>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Last Service Date</label>
                                <p class="mb-0"><?php echo $vehicle['last_service_date'] ? formatDate($vehicle['last_service_date'], DISPLAY_DATE_FORMAT) : '-'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Next Service Date</label>
                                <p class="mb-0">
                                    <?php 
                                    if ($vehicle['next_service_date']) {
                                        $serviceDays = (strtotime($vehicle['next_service_date']) - time()) / (60 * 60 * 24);
                                        $serviceClass = $serviceDays <= 30 ? 'text-danger' : 'text-muted';
                                        echo '<span class="' . $serviceClass . '">' . formatDate($vehicle['next_service_date'], DISPLAY_DATE_FORMAT) . '</span>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if ($vehicle['notes']): ?>
                        <div class="row">
                            <div class="col-12">
                                <label class="text-muted small">Notes</label>
                                <p class="mb-0"><?php echo nl2br(e($vehicle['notes'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Assignment History -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Assignment History</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($assignmentHistory)): ?>
                            <p class="text-muted text-center py-4">No assignment history</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Assignment #</th>
                                            <th>Driver</th>
                                            <th>Date</th>
                                            <th>Destination</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assignmentHistory as $assignment): ?>
                                        <tr>
                                            <td><?php echo e($assignment['assignment_number']); ?></td>
                                            <td><?php echo e($assignment['driver_name']); ?></td>
                                            <td><?php echo formatDate($assignment['assignment_date'], DISPLAY_DATE_FORMAT); ?></td>
                                            <td><?php echo e($assignment['destination']); ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo ['active' => 'bg-info', 'completed' => 'bg-success', 'cancelled' => 'bg-secondary'][$assignment['status']]; 
                                                ?>">
                                                    <?php echo ucfirst($assignment['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-md-4 mb-4">
                <!-- Active Assignment -->
                <?php if ($activeAssignment): ?>
                <div class="card mb-4 border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Active Assignment</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Assignment #:</strong> <?php echo e($activeAssignment['assignment_number']); ?></p>
                        <p class="mb-2"><strong>Driver:</strong> <?php echo e($activeAssignment['driver_name']); ?></p>
                        <p class="mb-2"><strong>Phone:</strong> <?php echo e($activeAssignment['driver_phone']); ?></p>
                        <p class="mb-2"><strong>Destination:</strong> <?php echo e($activeAssignment['destination']); ?></p>
                        <p class="mb-2"><strong>Purpose:</strong> <?php echo nl2br(e($activeAssignment['purpose'])); ?></p>
                        <a href="assignments/view.php?id=<?php echo $activeAssignment['id']; ?>" class="btn btn-sm btn-info w-100">
                            View Details
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Location Map -->
                <?php if ($location): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-geo-alt"></i> Current Location</h6>
                    </div>
                    <div class="card-body p-0">
                        <div id="vehicleMap"></div>
                        <div class="p-3">
                            <p class="mb-1 small"><strong>Coordinates:</strong></p>
                            <p class="mb-1 small text-muted">
                                Lat: <?php echo $location['latitude']; ?>, 
                                Lng: <?php echo $location['longitude']; ?>
                            </p>
                            <?php if ($location['speed']): ?>
                            <p class="mb-1 small"><strong>Speed:</strong> <?php echo number_format($location['speed'], 1); ?> km/h</p>
                            <?php endif; ?>
                            <p class="mb-0 small text-muted">
                                Updated: <?php echo formatDate($location['recorded_at'], DISPLAY_DATETIME_FORMAT); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <a href="assignments/create.php?vehicle_id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-primary w-100 mb-2">
                            <i class="bi bi-plus-circle"></i> New Assignment
                        </a>
                        <a href="map.php" class="btn btn-sm btn-info w-100">
                            <i class="bi bi-geo-alt"></i> Track on Map
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<?php if ($location): ?>
<script>
    // Initialize map
    const map = L.map('vehicleMap').setView([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Add marker
    const icon = L.divIcon({
        className: 'vehicle-marker',
        html: `<div style="background-color: <?php 
            echo ['available' => '#11998e', 'assigned' => '#4facfe', 
                  'maintenance' => '#f5576c', 'inactive' => '#6c757d'][$vehicle['status']]; 
        ?>; width: 30px; height: 30px; border-radius: 50%; 
               border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);
               display: flex; align-items: center; justify-content: center;">
            <i class="bi bi-truck" style="color: white; font-size: 14px;"></i>
           </div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });
    
    L.marker([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>], { icon: icon })
        .addTo(map)
        .bindPopup('<strong><?php echo e($vehicle['plate_number']); ?></strong>');
</script>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
