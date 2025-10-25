<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/audit.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireLogin();
$pageTitle = __('employees');

if (isset($_GET['delete']) && canDelete()) {
    $id = (int)$_GET['delete'];
    $employee = Database::fetchOne("SELECT * FROM EMPLIST WHERE id = ?", [$id]);
    Database::query("DELETE FROM EMPLIST WHERE id = ?", [$id]);
    auditLog('Delete Employee', 'EMPLIST', $id, $employee, null);
    setFlash('success', __('deleted_success'));
    redirect(baseUrl('modules/employees/list.php'));
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;
$totalCount = Database::fetchOne("SELECT COUNT(*) as count FROM EMPLIST")['count'];
$totalPages = ceil($totalCount / $perPage);
$employees = Database::fetchAll("SELECT * FROM EMPLIST ORDER BY created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset]);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-people"></i> <?php echo __('employees'); ?></h1>
        </div>
        <?php if (canCreate()): ?>
        <a href="<?php echo baseUrl('modules/employees/create.php'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> <?php echo __('add_employee'); ?>
        </a>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th><?php echo __('employee_name'); ?></th>
                            <th><?php echo __('amharic_name'); ?></th>
                            <th><?php echo __('taamagoli'); ?></th>
                            <th><?php echo __('directorate'); ?></th>
                            <th><?php echo __('salary'); ?></th>
                            <th><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($employees) > 0): ?>
                            <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><?php echo e($emp['id']); ?></td>
                                <td><strong><?php echo e($emp['name']); ?></strong></td>
                                <td><?php echo e($emp['nameam']); ?></td>
                                <td><code><?php echo e($emp['taamagoli']); ?></code></td>
                                <td><?php echo e($emp['directorate']); ?></td>
                                <td><?php echo formatCurrency($emp['salary']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if (canEdit()): ?>
                                        <a href="<?php echo baseUrl('modules/employees/edit.php?id=' . $emp['id']); ?>" 
                                           class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <?php endif; ?>
                                        <?php if (canDelete()): ?>
                                        <button onclick="confirmDelete('<?php echo baseUrl('modules/employees/list.php?delete=' . $emp['id']); ?>')" 
                                                class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-1"></i><p><?php echo __('no_data'); ?></p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo pagination($page, $totalPages, baseUrl('modules/employees/list.php')); ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
