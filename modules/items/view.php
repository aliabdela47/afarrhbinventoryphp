<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireLogin();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$item = Database::fetchOne(
    "SELECT i.*, c.name as category_name, w.name as warehouse_name, u.name as registered_by_name
     FROM ITEMS i
     LEFT JOIN CATEGORIES c ON i.categoryid = c.id
     LEFT JOIN WAREHOUSES w ON i.warehouseid = w.warehouseid
     LEFT JOIN USERS u ON i.registeredby = u.user_id
     WHERE i.itemid = ?",
    [$id]
);

if (!$item) { setFlash('error', 'Item not found'); redirect(baseUrl('modules/items/list.php')); }

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0"><i class="bi bi-box"></i> <?php echo __('item_details'); ?></h1></div>
        <a href="<?php echo baseUrl('modules/items/list.php'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> <?php echo __('back'); ?></a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3"><strong><?php echo __('serial_number'); ?>:</strong> <?php echo e($item['serialnumber']); ?></div>
                <div class="col-md-6 mb-3"><strong><?php echo __('item_name'); ?>:</strong> <?php echo e($item['name']); ?></div>
                <div class="col-md-6 mb-3"><strong><?php echo __('category'); ?>:</strong> <?php echo e($item['category_name'] ?? '-'); ?></div>
                <div class="col-md-6 mb-3"><strong><?php echo __('warehouse'); ?>:</strong> <?php echo e($item['warehouse_name'] ?? '-'); ?></div>
                <div class="col-md-6 mb-3"><strong><?php echo __('quantity'); ?>:</strong> <?php echo formatNumber($item['quantity']); ?> <?php echo e($item['unit']); ?></div>
                <div class="col-md-6 mb-3"><strong><?php echo __('status'); ?>:</strong> 
                    <span class="badge bg-success"><?php echo __(strtolower($item['status'])); ?></span></div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
