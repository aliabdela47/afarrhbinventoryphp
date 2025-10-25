<?php
/**
 * Audit Logs List
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireRole('admin');

$pageTitle = t('audit_logs') . ' - ' . APP_NAME;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

$search = $_GET['search'] ?? '';
$actionFilter = $_GET['action'] ?? '';

try {
    $countQuery = "SELECT COUNT(*) as count FROM AUDITLOG WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (table_name LIKE ?)";
        $params[] = "%$search%";
    }
    
    if (!empty($actionFilter)) {
        $countQuery .= " AND action = ?";
        $params[] = $actionFilter;
    }
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['count'];
    
    $pagination = paginate($totalItems, $page, $perPage);
    
    $query = "
        SELECT a.*, u.full_name as user_name
        FROM AUDITLOG a
        LEFT JOIN USERS u ON a.user_id = u.id
        WHERE 1=1
    ";
    
    $queryParams = [];
    
    if (!empty($search)) {
        $query .= " AND (a.table_name LIKE ?)";
        $queryParams[] = "%$search%";
    }
    
    if (!empty($actionFilter)) {
        $query .= " AND a.action = ?";
        $queryParams[] = $actionFilter;
    }
    
    $query .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
    $queryParams[] = $perPage;
    $queryParams[] = $pagination['offset'];
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($queryParams);
    $logs = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Audit logs error: " . $e->getMessage());
    $logs = [];
    $totalItems = 0;
    $pagination = paginate(0);
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="mb-4">
            <h2><i class="bi bi-shield-check"></i> <?php echo e(t('audit_logs')); ?></h2>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" 
                               placeholder="<?php echo e(t('search')); ?> by table name..." 
                               value="<?php echo e($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="action" class="form-select">
                            <option value=""><?php echo e(t('all')); ?> <?php echo e(t('action')); ?>s</option>
                            <option value="CREATE" <?php echo $actionFilter === 'CREATE' ? 'selected' : ''; ?>>CREATE</option>
                            <option value="UPDATE" <?php echo $actionFilter === 'UPDATE' ? 'selected' : ''; ?>>UPDATE</option>
                            <option value="DELETE" <?php echo $actionFilter === 'DELETE' ? 'selected' : ''; ?>>DELETE</option>
                            <option value="LOGIN" <?php echo $actionFilter === 'LOGIN' ? 'selected' : ''; ?>>LOGIN</option>
                            <option value="LOGOUT" <?php echo $actionFilter === 'LOGOUT' ? 'selected' : ''; ?>>LOGOUT</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> <?php echo e(t('search')); ?>
                        </button>
                        <a href="list.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> <?php echo e(t('clear')); ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th><?php echo e(t('user')); ?></th>
                                    <th><?php echo e(t('action')); ?></th>
                                    <th><?php echo e(t('table')); ?></th>
                                    <th><?php echo e(t('affected_id')); ?></th>
                                    <th><?php echo e(t('ip_address')); ?></th>
                                    <th><?php echo e(t('timestamp')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo e($log['user_name'] ?? 'System'); ?></td>
                                    <td><span class="badge bg-info"><?php echo e($log['action']); ?></span></td>
                                    <td><?php echo e($log['table_name']); ?></td>
                                    <td><?php echo e($log['record_id'] ?? '-'); ?></td>
                                    <td><?php echo e($log['ip_address']); ?></td>
                                    <td><?php echo formatDate($log['created_at'], DISPLAY_DATETIME_FORMAT); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($pagination['total_pages'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['has_previous']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($actionFilter) ? '&action=' . $actionFilter : ''; ?>">
                                    <?php echo e(t('previous')); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($actionFilter) ? '&action=' . $actionFilter : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($actionFilter) ? '&action=' . $actionFilter : ''; ?>">
                                    <?php echo e(t('next')); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
