<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/audit.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireLogin();

$pageTitle = __('warehouses');

// Handle delete
if (isset($_GET['delete']) && canDelete()) {
    $id = (int)$_GET['delete'];
    
    // Check if warehouse has items
    $itemsCount = Database::fetchOne("SELECT COUNT(*) as count FROM ITEMS WHERE warehouseid = ?", [$id])['count'];
    
    if ($itemsCount > 0) {
        setFlash('error', 'Cannot delete warehouse with existing items');
        redirect(baseUrl('modules/warehouses/list.php'));
    }
    
    $warehouse = Database::fetchOne("SELECT * FROM WAREHOUSES WHERE warehouseid = ?", [$id]);
    
    Database::query("DELETE FROM WAREHOUSES WHERE warehouseid = ?", [$id]);
    auditLog('Delete Warehouse', 'WAREHOUSES', $id, $warehouse, null);
    
    setFlash('success', __('deleted_success'));
    redirect(baseUrl('modules/warehouses/list.php'));
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Get total count
$totalCount = Database::fetchOne("SELECT COUNT(*) as count FROM WAREHOUSES")['count'];
$totalPages = ceil($totalCount / $perPage);

// Get warehouses
$warehouses = Database::fetchAll(
    "SELECT w.*, 
     (SELECT COUNT(*) FROM ITEMS WHERE warehouseid = w.warehouseid) as items_count
     FROM WAREHOUSES w
     ORDER BY w.created_at DESC
     LIMIT ? OFFSET ?",
    [$perPage, $offset]
);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-building"></i> <?php echo __('warehouses'); ?>
            </h1>
            <p class="text-muted"><?php echo __('warehouses'); ?> <?php echo __('list'); ?></p>
        </div>
        <?php if (canCreate()): ?>
        <a href="<?php echo baseUrl('modules/warehouses/create.php'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> <?php echo __('add_warehouse'); ?>
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Warehouses Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th><?php echo __('warehouse_name'); ?></th>
                            <th><?php echo __('location'); ?></th>
                            <th><?php echo __('contact_person'); ?></th>
                            <th><?php echo __('items'); ?></th>
                            <th><?php echo __('created_at'); ?></th>
                            <th><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($warehouses) > 0): ?>
                            <?php foreach ($warehouses as $warehouse): ?>
                            <tr>
                                <td><?php echo e($warehouse['warehouseid']); ?></td>
                                <td><strong><?php echo e($warehouse['name']); ?></strong></td>
                                <td><?php echo e($warehouse['location']); ?></td>
                                <td><?php echo e($warehouse['contactperson']); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo formatNumber($warehouse['items_count']); ?> items
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($warehouse['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if (canEdit()): ?>
                                        <a href="<?php echo baseUrl('modules/warehouses/edit.php?id=' . $warehouse['warehouseid']); ?>" 
                                           class="btn btn-outline-primary" title="<?php echo __('edit'); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (canDelete()): ?>
                                        <button onclick="confirmDelete('<?php echo baseUrl('modules/warehouses/list.php?delete=' . $warehouse['warehouseid']); ?>')" 
                                                class="btn btn-outline-danger" title="<?php echo __('delete'); ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p><?php echo __('no_data'); ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php echo pagination($page, $totalPages, baseUrl('modules/warehouses/list.php')); ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
