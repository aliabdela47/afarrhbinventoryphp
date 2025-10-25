<?php
/**
 * Vehicles Dashboard
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication
requireAuth();

$pageTitle = 'Vehicles Dashboard - ' . APP_NAME;

try {
    // Get vehicle statistics
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total_vehicles,
        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned,
        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
    FROM VEHICLES WHERE is_active = 1");
    $stats = $stmt->fetch();
    
    // Get active assignments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM VEHICLEASSIGNMENTS WHERE status = 'active'");
    $activeAssignments = $stmt->fetch()['count'];
    
    // Get vehicles needing service soon (within 30 days)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM VEHICLES 
        WHERE next_service_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
        AND next_service_date >= CURDATE()
        AND is_active = 1");
    $serviceDue = $stmt->fetch()['count'];
    
    // Get vehicles with expiring insurance (within 60 days)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM VEHICLES 
        WHERE insurance_expiry <= DATE_ADD(CURDATE(), INTERVAL 60 DAY) 
        AND insurance_expiry >= CURDATE()
        AND is_active = 1");
    $insuranceDue = $stmt->fetch()['count'];
    
    // Get recent vehicles
    $stmt = $pdo->query("SELECT * FROM VEHICLES WHERE is_active = 1 ORDER BY created_at DESC LIMIT 5");
    $recentVehicles = $stmt->fetchAll();
    
    // Get active assignments with details
    $stmt = $pdo->query("
        SELECT va.*, v.plate_number, v.vehicle_type, e.full_name as driver_name
        FROM VEHICLEASSIGNMENTS va
        JOIN VEHICLES v ON va.vehicle_id = v.id
        JOIN EMPLIST e ON va.driver_id = e.id
        WHERE va.status = 'active'
        ORDER BY va.assignment_date DESC
        LIMIT 5
    ");
    $activeAssignmentsList = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = ['total_vehicles' => 0, 'available' => 0, 'assigned' => 0, 'maintenance' => 0, 'inactive' => 0];
    $activeAssignments = 0;
    $serviceDue = 0;
    $insuranceDue = 0;
    $recentVehicles = [];
    $activeAssignmentsList = [];
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
            <h2><i class="bi bi-truck"></i> Vehicles Dashboard</h2>
            <div>
                <a href="create.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Vehicle
                </a>
                <a href="map.php" class="btn btn-info">
                    <i class="bi bi-geo-alt"></i> Track Vehicles
                </a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card metric-card primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-white-50 mb-2">Total Vehicles</h6>
                                <h2 class="mb-0"><?php echo $stats['total_vehicles']; ?></h2>
                            </div>
                            <i class="bi bi-truck" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card metric-card success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-white-50 mb-2">Available</h6>
                                <h2 class="mb-0"><?php echo $stats['available']; ?></h2>
                            </div>
                            <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card metric-card info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-white-50 mb-2">Assigned</h6>
                                <h2 class="mb-0"><?php echo $stats['assigned']; ?></h2>
                            </div>
                            <i class="bi bi-calendar-check" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card metric-card warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-white-50 mb-2">In Maintenance</h6>
                                <h2 class="mb-0"><?php echo $stats['maintenance']; ?></h2>
                            </div>
                            <i class="bi bi-tools" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alerts Row -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <i class="bi bi-exclamation-triangle"></i> Service Due Soon
                        </h5>
                        <p class="card-text mb-0">
                            <strong class="fs-3"><?php echo $serviceDue; ?></strong> vehicle(s) need service within 30 days
                        </p>
                        <?php if ($serviceDue > 0): ?>
                        <a href="list.php?filter=service_due" class="btn btn-sm btn-warning mt-2">View Details</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card border-danger">
                    <div class="card-body">
                        <h5 class="card-title text-danger">
                            <i class="bi bi-shield-exclamation"></i> Insurance Expiring
                        </h5>
                        <p class="card-text mb-0">
                            <strong class="fs-3"><?php echo $insuranceDue; ?></strong> vehicle(s) insurance expiring within 60 days
                        </p>
                        <?php if ($insuranceDue > 0): ?>
                        <a href="list.php?filter=insurance_due" class="btn btn-sm btn-danger mt-2">View Details</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card border-info">
                    <div class="card-body">
                        <h5 class="card-title text-info">
                            <i class="bi bi-calendar-check"></i> Active Assignments
                        </h5>
                        <p class="card-text mb-0">
                            <strong class="fs-3"><?php echo $activeAssignments; ?></strong> vehicle(s) currently assigned
                        </p>
                        <?php if ($activeAssignments > 0): ?>
                        <a href="assignments/list.php?status=active" class="btn btn-sm btn-info mt-2">View Details</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content Row -->
        <div class="row">
            <!-- Recent Vehicles -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recently Added Vehicles</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentVehicles)): ?>
                            <p class="text-muted text-center py-4">No vehicles found</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentVehicles as $vehicle): ?>
                                <a href="view.php?id=<?php echo $vehicle['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo e($vehicle['plate_number']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo e($vehicle['make'] . ' ' . $vehicle['model']); ?> 
                                                (<?php echo e($vehicle['year']); ?>)
                                            </small>
                                        </div>
                                        <div>
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
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="list.php" class="btn btn-sm btn-link">View All Vehicles</a>
                    </div>
                </div>
            </div>
            
            <!-- Active Assignments -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Active Assignments</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($activeAssignmentsList)): ?>
                            <p class="text-muted text-center py-4">No active assignments</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($activeAssignmentsList as $assignment): ?>
                                <a href="assignments/view.php?id=<?php echo $assignment['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo e($assignment['plate_number']); ?></h6>
                                            <small class="text-muted">
                                                Driver: <?php echo e($assignment['driver_name']); ?><br>
                                                Destination: <?php echo e($assignment['destination']); ?>
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo formatDate($assignment['assignment_date'], DISPLAY_DATE_FORMAT); ?>
                                        </small>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="assignments/list.php" class="btn btn-sm btn-link">View All Assignments</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chart Section -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Vehicle Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="vehicleStatusChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Vehicle Status Chart
    const ctx = document.getElementById('vehicleStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Assigned', 'Maintenance', 'Inactive'],
            datasets: [{
                label: 'Vehicles',
                data: [
                    <?php echo $stats['available']; ?>,
                    <?php echo $stats['assigned']; ?>,
                    <?php echo $stats['maintenance']; ?>,
                    <?php echo $stats['inactive']; ?>
                ],
                backgroundColor: [
                    'rgba(17, 153, 142, 0.8)',
                    'rgba(79, 172, 254, 0.8)',
                    'rgba(245, 87, 108, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: false
                }
            }
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>
