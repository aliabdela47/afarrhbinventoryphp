<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

requireLogin();

$pageTitle = __('dashboard');

// Get dashboard metrics
$totalItems = Database::fetchOne("SELECT COUNT(*) as count FROM ITEMS")['count'] ?? 0;
$totalWarehouses = Database::fetchOne("SELECT COUNT(*) as count FROM WAREHOUSES")['count'] ?? 0;
$pendingRequests = Database::fetchOne("SELECT COUNT(*) as count FROM REQUESTS WHERE status = 'Pending'")['count'] ?? 0;
$recentIssuances = Database::fetchOne("SELECT COUNT(*) as count FROM ISSUANCES WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")['count'] ?? 0;

// Get inventory by status
$itemsByStatus = Database::fetchAll("
    SELECT status, COUNT(*) as count 
    FROM ITEMS 
    GROUP BY status
");

// Get recent activity
$recentActivity = Database::fetchAll("
    SELECT a.*, u.name as user_name 
    FROM AUDITLOG a
    LEFT JOIN USERS u ON a.userid = u.user_id
    ORDER BY a.created_at DESC
    LIMIT 10
");

// Get low stock items (items with quantity < 10)
$lowStockItems = Database::fetchAll("
    SELECT i.*, c.name as category_name, w.name as warehouse_name
    FROM ITEMS i
    LEFT JOIN CATEGORIES c ON i.categoryid = c.id
    LEFT JOIN WAREHOUSES w ON i.warehouseid = w.warehouseid
    WHERE i.quantity < 10 AND i.status = 'Available'
    ORDER BY i.quantity ASC
    LIMIT 5
");

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><?php echo __('dashboard'); ?></h1>
            <p class="text-muted"><?php echo __('welcome'); ?>, <?php echo e(currentUser()['name']); ?>!</p>
        </div>
        <div>
            <span class="text-muted">
                <i class="bi bi-calendar"></i> 
                <?php echo date('F d, Y'); ?>
            </span>
        </div>
    </div>
    
    <!-- Metric Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card metric-card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1"><?php echo __('total_items'); ?></h6>
                            <h2 class="mb-0"><?php echo formatNumber($totalItems); ?></h2>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-box"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card metric-card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1"><?php echo __('total_warehouses'); ?></h6>
                            <h2 class="mb-0"><?php echo formatNumber($totalWarehouses); ?></h2>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card metric-card border-0 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1"><?php echo __('pending_requests'); ?></h6>
                            <h2 class="mb-0"><?php echo formatNumber($pendingRequests); ?></h2>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-file-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card metric-card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1"><?php echo __('recent_issuances'); ?></h6>
                            <h2 class="mb-0"><?php echo formatNumber($recentIssuances); ?></h2>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-file-earmark-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions & Inventory Status -->
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning"></i> <?php echo __('quick_actions'); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if (canCreate()): ?>
                        <a href="<?php echo baseUrl('modules/items/create.php'); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> <?php echo __('add_item'); ?>
                        </a>
                        <a href="<?php echo baseUrl('modules/requests/create.php'); ?>" class="btn btn-outline-success">
                            <i class="bi bi-file-text"></i> <?php echo __('add_request'); ?>
                        </a>
                        <a href="<?php echo baseUrl('modules/issuances/create.php'); ?>" class="btn btn-outline-info">
                            <i class="bi bi-file-earmark-check"></i> <?php echo __('add_issuance'); ?>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo baseUrl('modules/reports/index.php'); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-graph-up"></i> <?php echo __('generate_report'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pie-chart"></i> <?php echo __('inventory_status'); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($itemsByStatus as $stat): ?>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h3 class="mb-0"><?php echo formatNumber($stat['count']); ?></h3>
                                <p class="text-muted mb-0"><?php echo __(strtolower($stat['status'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Items & Recent Activity -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle text-warning"></i> Low Stock Items
                    </h5>
                    <a href="<?php echo baseUrl('modules/items/list.php'); ?>" class="btn btn-sm btn-outline-primary">
                        <?php echo __('view'); ?> All
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($lowStockItems) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo __('item_name'); ?></th>
                                    <th><?php echo __('category'); ?></th>
                                    <th><?php echo __('quantity'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockItems as $item): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo baseUrl('modules/items/view.php?id=' . $item['itemid']); ?>">
                                            <?php echo e($item['name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo e($item['category_name'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <?php echo formatNumber($item['quantity']); ?> <?php echo e($item['unit']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle fs-1"></i>
                        <p class="mb-0">All items are well stocked</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history"></i> <?php echo __('recent_activity'); ?>
                    </h5>
                    <?php if (isAdmin()): ?>
                    <a href="<?php echo baseUrl('modules/admin/audit.php'); ?>" class="btn btn-sm btn-outline-primary">
                        <?php echo __('view'); ?> All
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recentActivity) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentActivity as $activity): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo e($activity['action']); ?></h6>
                                <small class="text-muted">
                                    <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                </small>
                            </div>
                            <p class="mb-1 small text-muted">
                                <?php if ($activity['user_name']): ?>
                                By: <?php echo e($activity['user_name']); ?>
                                <?php endif; ?>
                                <?php if ($activity['affectedtable']): ?>
                                | Table: <?php echo e($activity['affectedtable']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mb-0"><?php echo __('no_data'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
