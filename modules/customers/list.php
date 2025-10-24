<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireLogin();
$pageTitle = __('customers');

if (isset($_GET['delete']) && canDelete()) {
    $id = (int)$_GET['delete'];
    Database::query("DELETE FROM CUSTOMERS WHERE customerid = ?", [$id]);
    setFlash('success', __('deleted_success'));
    redirect(baseUrl('modules/customers/list.php'));
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;
$totalCount = Database::fetchOne("SELECT COUNT(*) as count FROM CUSTOMERS")['count'];
$totalPages = ceil($totalCount / $perPage);
$customers = Database::fetchAll(
    "SELECT c.*, e.name as employee_name FROM CUSTOMERS c LEFT JOIN EMPLIST e ON c.empid = e.id ORDER BY c.created_at DESC LIMIT ? OFFSET ?",
    [$perPage, $offset]
);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0"><i class="bi bi-person-badge"></i> <?php echo __('customers'); ?></h1></div>
        <?php if (canCreate()): ?>
        <a href="<?php echo baseUrl('modules/customers/create.php'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> <?php echo __('add_customer'); ?></a>
        <?php endif; ?>
    </div>
    
    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th><?php echo __('customer_name'); ?></th>
                        <th><?php echo __('customer_type'); ?></th>
                        <th><?php echo __('employee_name'); ?></th>
                        <th><?php echo __('purpose'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($customers) > 0): ?>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo e($customer['customerid']); ?></td>
                            <td><strong><?php echo e($customer['name']); ?></strong></td>
                            <td><span class="badge <?php echo $customer['type'] == 'Internal' ? 'bg-primary' : 'bg-success'; ?>">
                                <?php echo __(strtolower($customer['type'])); ?></span></td>
                            <td><?php echo e($customer['employee_name'] ?? '-'); ?></td>
                            <td><?php echo e(substr($customer['purpose'], 0, 50)); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if (canEdit()): ?>
                                    <a href="<?php echo baseUrl('modules/customers/edit.php?id=' . $customer['customerid']); ?>" 
                                       class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <?php endif; ?>
                                    <?php if (canDelete()): ?>
                                    <button onclick="confirmDelete('<?php echo baseUrl('modules/customers/list.php?delete=' . $customer['customerid']); ?>')" 
                                            class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-1"></i><p><?php echo __('no_data'); ?></p></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo pagination($page, $totalPages, baseUrl('modules/customers/list.php')); ?>
    </div></div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
