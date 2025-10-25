<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireLogin();
$pageTitle = __('items');

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;
$totalCount = Database::fetchOne("SELECT COUNT(*) as count FROM ITEMS")['count'];
$totalPages = ceil($totalCount / $perPage);

$items = Database::fetchAll(
    "SELECT i.*, c.name as category_name, w.name as warehouse_name, u.name as registered_by_name
     FROM ITEMS i
     LEFT JOIN CATEGORIES c ON i.categoryid = c.id
     LEFT JOIN WAREHOUSES w ON i.warehouseid = w.warehouseid
     LEFT JOIN USERS u ON i.registeredby = u.user_id
     ORDER BY i.created_at DESC LIMIT ? OFFSET ?",
    [$perPage, $offset]
);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0"><i class="bi bi-box"></i> <?php echo __('items'); ?> (Model-19)</h1></div>
        <?php if (canCreate()): ?>
        <a href="<?php echo baseUrl('modules/items/create.php'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> <?php echo __('add_item'); ?></a>
        <?php endif; ?>
    </div>
    
    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th><?php echo __('serial_number'); ?></th>
                        <th><?php echo __('item_name'); ?></th>
                        <th><?php echo __('category'); ?></th>
                        <th><?php echo __('warehouse'); ?></th>
                        <th><?php echo __('quantity'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo e($item['itemid']); ?></td>
                            <td><code><?php echo e($item['serialnumber']); ?></code></td>
                            <td><strong><?php echo e($item['name']); ?></strong></td>
                            <td><?php echo e($item['category_name'] ?? '-'); ?></td>
                            <td><?php echo e($item['warehouse_name'] ?? '-'); ?></td>
                            <td><span class="badge bg-info"><?php echo formatNumber($item['quantity']); ?> <?php echo e($item['unit']); ?></span></td>
                            <td>
                                <?php
                                $statusColors = ['Available' => 'success', 'Issued' => 'warning', 'Damaged' => 'danger', 'Lost' => 'dark', 'Disposed' => 'secondary'];
                                $color = $statusColors[$item['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo __(strtolower($item['status'])); ?></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo baseUrl('modules/items/view.php?id=' . $item['itemid']); ?>" 
                                       class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                                    <?php if (canEdit()): ?>
                                    <a href="<?php echo baseUrl('modules/items/edit.php?id=' . $item['itemid']); ?>" 
                                       class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-1"></i><p><?php echo __('no_data'); ?></p></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo pagination($page, $totalPages, baseUrl('modules/items/list.php')); ?>
    </div></div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
