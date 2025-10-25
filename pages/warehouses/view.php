<?php
/**
 * View Warehouse
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication
requireAuth();

$pageTitle = t('warehouse') . ' - ' . APP_NAME;

// Get warehouse ID
$warehouseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$warehouseId) {
    flash('error', 'Invalid warehouse ID');
    redirect('list.php');
}

// Get warehouse data
try {
    $stmt = $pdo->prepare("
        SELECT w.*, u.full_name as manager_name
        FROM WAREHOUSES w
        LEFT JOIN USERS u ON w.manager_id = u.id
        WHERE w.id = ?
    ");
    $stmt->execute([$warehouseId]);
    $warehouse = $stmt->fetch();
    
    if (!$warehouse) {
        flash('error', 'Warehouse not found');
        redirect('list.php');
    }
    
    // Get items in this warehouse
    $itemsStmt = $pdo->prepare("
        SELECT i.*, c.name as category_name
        FROM ITEMS i
        LEFT JOIN CATEGORIES c ON i.category_id = c.id
        WHERE i.warehouse_id = ? AND i.is_active = 1
        ORDER BY i.name
        LIMIT 10
    ");
    $itemsStmt->execute([$warehouseId]);
    $items = $itemsStmt->fetchAll();
    
    // Get item count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM ITEMS WHERE warehouse_id = ? AND is_active = 1");
    $countStmt->execute([$warehouseId]);
    $itemCount = $countStmt->fetch()['count'];
    
} catch (PDOException $e) {
    error_log("Get warehouse error: " . $e->getMessage());
    flash('error', t('error_occurred'));
    redirect('list.php');
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
            <div>
                <h2><i class="bi bi-building"></i> <?php echo e($warehouse['name']); ?></h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../../dashboard.php"><?php echo e(t('dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="list.php"><?php echo e(t('warehouses')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo e(t('view')); ?></li>
                    </ol>
                </nav>
            </div>
            <?php if (hasAnyRole(['admin', 'manager'])): ?>
            <div>
                <a href="edit.php?id=<?php echo $warehouseId; ?>" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> <?php echo e(t('edit')); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Warehouse Details -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-info-circle"></i> <?php echo e(t('warehouse')); ?> Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%"><?php echo e(t('name')); ?>:</th>
                                <td><?php echo e($warehouse['name']); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('location')); ?>:</th>
                                <td><?php echo e($warehouse['location'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('manager')); ?>:</th>
                                <td><?php echo e($warehouse['manager_name'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('status')); ?>:</th>
                                <td>
                                    <?php if ($warehouse['is_active']): ?>
                                        <span class="badge bg-success"><?php echo e(t('active')); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo e(t('inactive')); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('created_at')); ?>:</th>
                                <td><?php echo formatDate($warehouse['created_at'], DISPLAY_DATETIME_FORMAT); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('updated_at')); ?>:</th>
                                <td><?php echo formatDate($warehouse['updated_at'], DISPLAY_DATETIME_FORMAT); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-bar-chart"></i> Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="metric-card primary mb-3">
                            <h3><?php echo $itemCount; ?></h3>
                            <p class="mb-0"><?php echo e(t('total_items')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Items in Warehouse -->
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-box-seam"></i> <?php echo e(t('items')); ?> in <?php echo e($warehouse['name']); ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($items)): ?>
                    <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(t('item_code')); ?></th>
                                    <th><?php echo e(t('name')); ?></th>
                                    <th><?php echo e(t('category')); ?></th>
                                    <th><?php echo e(t('current_stock')); ?></th>
                                    <th><?php echo e(t('unit')); ?></th>
                                    <th><?php echo e(t('actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo e($item['item_code']); ?></td>
                                    <td><?php echo e($item['name']); ?></td>
                                    <td><?php echo e($item['category_name'] ?? '-'); ?></td>
                                    <td><?php echo e($item['current_stock']); ?></td>
                                    <td><?php echo e($item['unit']); ?></td>
                                    <td>
                                        <a href="../items/view.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($itemCount > 10): ?>
                    <div class="text-center mt-3">
                        <p class="text-muted">Showing 10 of <?php echo $itemCount; ?> items</p>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
