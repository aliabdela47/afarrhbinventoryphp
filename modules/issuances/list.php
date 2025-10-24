<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireLogin();
$issuances = Database::fetchAll(
    "SELECT i.*, c.name as customer_name, u.name as issued_by_name
     FROM ISSUANCES i
     LEFT JOIN CUSTOMERS c ON i.customerid = c.customerid
     LEFT JOIN USERS u ON i.issuedby = u.user_id
     ORDER BY i.created_at DESC LIMIT 20"
);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0"><i class="bi bi-file-earmark-check"></i> <?php echo __('issuances'); ?> (Model-22)</h1></div>
        <?php if (canCreate()): ?>
        <a href="<?php echo baseUrl('modules/issuances/create.php'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> <?php echo __('add_issuance'); ?></a>
        <?php endif; ?>
    </div>
    
    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th><?php echo __('model_22_number'); ?></th>
                        <th><?php echo __('customer'); ?></th>
                        <th><?php echo __('issuance_date'); ?></th>
                        <th><?php echo __('status'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($issuances) > 0): ?>
                        <?php foreach ($issuances as $issuance): ?>
                        <tr>
                            <td><?php echo e($issuance['issuanceid']); ?></td>
                            <td><code><?php echo e($issuance['model22number']); ?></code></td>
                            <td><?php echo e($issuance['customer_name'] ?? '-'); ?></td>
                            <td><?php echo formatDate($issuance['issuancedate']); ?></td>
                            <td><span class="badge bg-success"><?php echo __(strtolower($issuance['status'])); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-1"></i><p><?php echo __('no_data'); ?></p></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div></div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
