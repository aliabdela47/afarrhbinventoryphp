<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireLogin();
$pageTitle = __('requests');

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;
$totalCount = Database::fetchOne("SELECT COUNT(*) as count FROM REQUESTS")['count'];
$totalPages = ceil($totalCount / $perPage);

$requests = Database::fetchAll(
    "SELECT r.*, e.name as requested_by_name, u.name as created_by_name
     FROM REQUESTS r
     LEFT JOIN EMPLIST e ON r.requestedby = e.id
     LEFT JOIN USERS u ON r.createdby = u.user_id
     ORDER BY r.created_at DESC LIMIT ? OFFSET ?",
    [$perPage, $offset]
);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0"><i class="bi bi-file-text"></i> <?php echo __('requests'); ?> (Model-20)</h1></div>
        <?php if (canCreate()): ?>
        <a href="<?php echo baseUrl('modules/requests/create.php'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> <?php echo __('add_request'); ?></a>
        <?php endif; ?>
    </div>
    
    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th><?php echo __('model_20_number'); ?></th>
                        <th><?php echo __('requested_by'); ?></th>
                        <th><?php echo __('directorate'); ?></th>
                        <th><?php echo __('request_date'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($requests) > 0): ?>
                        <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo e($request['requestid']); ?></td>
                            <td><code><?php echo e($request['model20number']); ?></code></td>
                            <td><?php echo e($request['requested_by_name'] ?? '-'); ?></td>
                            <td><?php echo e($request['directorate']); ?></td>
                            <td><?php echo formatDate($request['requestdate']); ?></td>
                            <td>
                                <?php
                                $statusColors = ['Pending' => 'warning', 'Approved' => 'success', 'Rejected' => 'danger', 'Fulfilled' => 'info'];
                                $color = $statusColors[$request['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo __(strtolower($request['status'])); ?></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo baseUrl('modules/requests/view.php?id=' . $request['requestid']); ?>" 
                                       class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
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
        <?php echo pagination($page, $totalPages, baseUrl('modules/requests/list.php')); ?>
    </div></div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
