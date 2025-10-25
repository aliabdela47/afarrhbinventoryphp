<?php
/**
 * Issuances List (Model-22)
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();

$pageTitle = t('issuances') . ' - ' . APP_NAME;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

$search = $_GET['search'] ?? '';

try {
    $countQuery = "SELECT COUNT(*) as count FROM ISSUANCES i WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (i.issuance_number LIKE ?)";
        $params[] = "%$search%";
    }
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['count'];
    
    $pagination = paginate($totalItems, $page, $perPage);
    
    $query = "
        SELECT i.*, 
               e.full_name as issued_to_name,
               u.full_name as issued_by_name,
               r.request_number
        FROM ISSUANCES i
        LEFT JOIN EMPLIST e ON i.issued_to = e.id
        LEFT JOIN USERS u ON i.issued_by = u.id
        LEFT JOIN REQUESTS r ON i.request_id = r.id
        WHERE 1=1
    ";
    
    $queryParams = [];
    
    if (!empty($search)) {
        $query .= " AND (i.issuance_number LIKE ?)";
        $queryParams[] = "%$search%";
    }
    
    $query .= " ORDER BY i.issue_date DESC LIMIT ? OFFSET ?";
    $queryParams[] = $perPage;
    $queryParams[] = $pagination['offset'];
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($queryParams);
    $issuances = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Issuances list error: " . $e->getMessage());
    $issuances = [];
    $totalItems = 0;
    $pagination = paginate(0);
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-arrow-right-circle"></i> <?php echo e(t('issuances')); ?> (Model-22)</h2>
            <?php if (hasAnyRole(['admin', 'manager', 'staff'])): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?php echo e(t('issue_items')); ?>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" 
                               placeholder="<?php echo e(t('search')); ?> by issuance number..." 
                               value="<?php echo e($search); ?>">
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
                <?php if (empty($issuances)): ?>
                    <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(t('issuance_number')); ?></th>
                                    <th><?php echo e(t('request_number')); ?></th>
                                    <th><?php echo e(t('issued_to')); ?></th>
                                    <th><?php echo e(t('issued_by')); ?></th>
                                    <th><?php echo e(t('issue_date')); ?></th>
                                    <th><?php echo e(t('actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($issuances as $issuance): ?>
                                <tr>
                                    <td><?php echo e($issuance['issuance_number']); ?></td>
                                    <td><?php echo e($issuance['request_number'] ?? '-'); ?></td>
                                    <td><?php echo e($issuance['issued_to_name'] ?? '-'); ?></td>
                                    <td><?php echo e($issuance['issued_by_name'] ?? '-'); ?></td>
                                    <td><?php echo formatDate($issuance['issue_date'], DISPLAY_DATE_FORMAT); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view.php?id=<?php echo $issuance['id']; ?>" 
                                               class="btn btn-info" title="<?php echo e(t('view')); ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="print.php?id=<?php echo $issuance['id']; ?>" 
                                               class="btn btn-secondary" title="<?php echo e(t('print')); ?>" target="_blank">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </div>
                                    </td>
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
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo e(t('previous')); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
