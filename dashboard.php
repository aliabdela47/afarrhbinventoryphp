<?php
/**
 * Dashboard
 * AfarRHB Inventory Management System
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';

// Require authentication
requireAuth();

$pageTitle = t('dashboard') . ' - ' . APP_NAME;

// Get dashboard statistics
try {
    // Total items
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM ITEMS WHERE is_active = 1");
    $totalItems = $stmt->fetch()['count'];
    
    // Low stock items
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM ITEMS WHERE current_stock <= reorder_level AND is_active = 1");
    $lowStockItems = $stmt->fetch()['count'];
    
    // Pending requests
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM REQUESTS WHERE status = 'pending'");
    $pendingRequests = $stmt->fetch()['count'];
    
    // Recent issuances (last 30 days)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM ISSUANCES WHERE issue_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $recentIssuances = $stmt->fetch()['count'];
    
    // Available vehicles
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM VEHICLES WHERE status = 'Available'");
    $availableVehicles = $stmt->fetch()['count'];
    
    // Vehicles on field work
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM VEHICLE_ASSIGNMENTS WHERE status = 'Active'");
    $vehiclesOnField = $stmt->fetch()['count'];
    
    // Recent activities (last 10)
    $stmt = $pdo->query("
        SELECT a.*, u.full_name as user_name
        FROM AUDITLOG a
        LEFT JOIN USERS u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $recentActivities = $stmt->fetchAll();
    
    // Low stock items list
    $stmt = $pdo->query("
        SELECT i.*, c.name as category_name, w.name as warehouse_name
        FROM ITEMS i
        LEFT JOIN CATEGORIES c ON i.category_id = c.id
        LEFT JOIN WAREHOUSES w ON i.warehouse_id = w.id
        WHERE i.current_stock <= i.reorder_level AND i.is_active = 1
        ORDER BY i.current_stock ASC
        LIMIT 5
    ");
    $lowStockItemsList = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalItems = $lowStockItems = $pendingRequests = $recentIssuances = 0;
    $availableVehicles = $vehiclesOnField = 0;
    $recentActivities = $lowStockItemsList = [];
}

// Include header
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-speedometer2"></i> <?php echo e(t('dashboard')); ?></h2>
            <div>
                <span class="badge bg-primary"><?php echo e(t($currentRole)); ?></span>
            </div>
        </div>
        
        <!-- Metric Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="metric-card primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75"><?php echo e(t('total_items')); ?></p>
                            <h3 class="mb-0"><?php echo e(number_format($totalItems)); ?></h3>
                        </div>
                        <div>
                            <i class="bi bi-box-seam" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="metric-card warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75"><?php echo e(t('low_stock_items')); ?></p>
                            <h3 class="mb-0"><?php echo e(number_format($lowStockItems)); ?></h3>
                        </div>
                        <div>
                            <i class="bi bi-exclamation-triangle" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="metric-card info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75"><?php echo e(t('pending_requests')); ?></p>
                            <h3 class="mb-0"><?php echo e(number_format($pendingRequests)); ?></h3>
                        </div>
                        <div>
                            <i class="bi bi-clipboard-check" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="metric-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75"><?php echo e(t('recent_issuances')); ?></p>
                            <h3 class="mb-0"><?php echo e(number_format($recentIssuances)); ?></h3>
                        </div>
                        <div>
                            <i class="bi bi-arrow-right-circle" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Vehicle Metric Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="metric-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75"><?php echo e(t('available_vehicles')); ?></p>
                            <h3 class="mb-0"><?php echo e(number_format($availableVehicles)); ?></h3>
                        </div>
                        <div>
                            <i class="bi bi-truck" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="pages/vehicles/list.php?status=Available" class="text-white text-decoration-none">
                            <small><?php echo e(t('view')); ?> <?php echo e(t('all')); ?> <i class="bi bi-arrow-right"></i></small>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="metric-card primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75"><?php echo e(t('vehicles_on_field')); ?></p>
                            <h3 class="mb-0"><?php echo e(number_format($vehiclesOnField)); ?></h3>
                        </div>
                        <div>
                            <i class="bi bi-geo-alt" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="pages/vehicles/assignments/list.php?status=Active" class="text-white text-decoration-none">
                            <small><?php echo e(t('view')); ?> <?php echo e(t('all')); ?> <i class="bi bi-arrow-right"></i></small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <?php if (hasAnyRole(['admin', 'manager', 'staff'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning-charge"></i> <?php echo e(t('quick_actions')); ?></h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="pages/requests/create.php" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> <?php echo e(t('new_request')); ?>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="pages/issuances/create.php" class="btn btn-success w-100">
                            <i class="bi bi-arrow-right-circle"></i> <?php echo e(t('issue_items')); ?>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="pages/items/create.php" class="btn btn-info w-100">
                            <i class="bi bi-box-seam"></i> <?php echo e(t('add_item')); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="row g-4">
            <!-- Low Stock Items -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-warning"></i> <?php echo e(t('low_stock_items')); ?></h5>
                        <a href="pages/items/list.php?filter=low_stock" class="btn btn-sm btn-outline-primary">
                            <?php echo e(t('view')); ?> <?php echo e(t('all')); ?>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lowStockItemsList)): ?>
                            <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php echo e(t('item_code')); ?></th>
                                            <th><?php echo e(t('name')); ?></th>
                                            <th><?php echo e(t('current_stock')); ?></th>
                                            <th><?php echo e(t('reorder_level')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lowStockItemsList as $item): ?>
                                        <tr>
                                            <td><?php echo e($item['item_code']); ?></td>
                                            <td><?php echo e($item['name']); ?></td>
                                            <td><span class="badge bg-danger"><?php echo e($item['current_stock']); ?></span></td>
                                            <td><?php echo e($item['reorder_level']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-activity"></i> <?php echo e(t('recent_activity')); ?></h5>
                        <?php if (hasRole('admin')): ?>
                        <a href="pages/audit_logs/list.php" class="btn btn-sm btn-outline-primary">
                            <?php echo e(t('view')); ?> <?php echo e(t('all')); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <?php if (empty($recentActivities)): ?>
                            <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentActivities as $activity): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <?php 
                                            $actionIcon = 'circle-fill';
                                            $actionColor = 'primary';
                                            if ($activity['action'] === 'DELETE') {
                                                $actionIcon = 'trash';
                                                $actionColor = 'danger';
                                            } elseif ($activity['action'] === 'CREATE') {
                                                $actionIcon = 'plus-circle';
                                                $actionColor = 'success';
                                            } elseif ($activity['action'] === 'UPDATE') {
                                                $actionIcon = 'pencil';
                                                $actionColor = 'warning';
                                            }
                                            ?>
                                            <i class="bi bi-<?php echo $actionIcon; ?> text-<?php echo $actionColor; ?>"></i>
                                            <?php echo e($activity['action']); ?> - <?php echo e($activity['table_name']); ?>
                                        </h6>
                                        <small class="text-muted"><?php echo formatDate($activity['created_at'], DISPLAY_DATETIME_FORMAT); ?></small>
                                    </div>
                                    <p class="mb-1">
                                        <small><?php echo e(t('by')); ?>: <?php echo e($activity['user_name'] ?? 'System'); ?></small>
                                    </p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
