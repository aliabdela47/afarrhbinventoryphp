<?php
/**
 * Employees List (EMPLIST)
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();

$pageTitle = t('employees') . ' - ' . APP_NAME;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

$search = $_GET['search'] ?? '';

try {
    $countQuery = "SELECT COUNT(*) as count FROM EMPLIST WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (full_name LIKE ? OR employee_code LIKE ? OR department LIKE ? OR position LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['count'];
    
    $pagination = paginate($totalItems, $page, $perPage);
    
    $query = "SELECT * FROM EMPLIST WHERE 1=1";
    
    $queryParams = [];
    
    if (!empty($search)) {
        $query .= " AND (full_name LIKE ? OR employee_code LIKE ? OR department LIKE ? OR position LIKE ?)";
        $queryParams[] = "%$search%";
        $queryParams[] = "%$search%";
        $queryParams[] = "%$search%";
        $queryParams[] = "%$search%";
    }
    
    $query .= " ORDER BY full_name ASC LIMIT ? OFFSET ?";
    $queryParams[] = $perPage;
    $queryParams[] = $pagination['offset'];
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($queryParams);
    $employees = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Employees list error: " . $e->getMessage());
    $employees = [];
    $totalItems = 0;
    $pagination = paginate(0);
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-person-badge"></i> <?php echo e(t('employees')); ?></h2>
            <?php if (hasAnyRole(['admin', 'manager'])): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?php echo e(t('add')); ?> <?php echo e(t('employee_name')); ?>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" 
                               placeholder="<?php echo e(t('search')); ?>..." 
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
                <?php if (empty($employees)): ?>
                    <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(t('employee_code')); ?></th>
                                    <th><?php echo e(t('name')); ?></th>
                                    <th><?php echo e(t('department')); ?> / <?php echo e(t('directorate')); ?></th>
                                    <th><?php echo e(t('position')); ?></th>
                                    <th><?php echo e(t('phone')); ?></th>
                                    <th><?php echo e(t('status')); ?></th>
                                    <th><?php echo e(t('actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td><?php echo e($employee['employee_code']); ?></td>
                                    <td><?php echo e($employee['full_name']); ?></td>
                                    <td><?php echo e($employee['department'] ?? '-'); ?></td>
                                    <td><?php echo e($employee['position'] ?? '-'); ?></td>
                                    <td><?php echo e($employee['phone'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($employee['is_active']): ?>
                                            <span class="badge bg-success"><?php echo e(t('active')); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo e(t('inactive')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view.php?id=<?php echo $employee['id']; ?>" 
                                               class="btn btn-info" title="<?php echo e(t('view')); ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (hasAnyRole(['admin', 'manager'])): ?>
                                            <a href="edit.php?id=<?php echo $employee['id']; ?>" 
                                               class="btn btn-warning" title="<?php echo e(t('edit')); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>
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
