<?php
/**
 * Vehicle Assignments List
 * AfarRHB Inventory Management System
 */

require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/auth.php';

requireAuth();

$pageTitle = t('assignments') . ' - ' . APP_NAME;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

$statusFilter = $_GET['status'] ?? '';

try {
    $countQuery = "SELECT COUNT(*) as count FROM VEHICLE_ASSIGNMENTS WHERE 1=1";
    $params = [];
    
    if (!empty($statusFilter)) {
        $countQuery .= " AND status = ?";
        $params[] = $statusFilter;
    }
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['count'];
    
    $pagination = paginate($totalItems, $page, $perPage);
    
    $query = "
        SELECT va.*, 
               v.plate_number, v.type as vehicle_type,
               e.full_name as employee_name, e.department,
               u.full_name as assigned_by_name
        FROM VEHICLE_ASSIGNMENTS va
        JOIN VEHICLES v ON va.vehicle_id = v.vehicle_id
        JOIN EMPLIST e ON va.emp_id = e.id
        LEFT JOIN USERS u ON va.assigned_by = u.id
        WHERE 1=1
    ";
    
    $queryParams = [];
    
    if (!empty($statusFilter)) {
        $query .= " AND va.status = ?";
        $queryParams[] = $statusFilter;
    }
    
    $query .= " ORDER BY va.departure_date DESC LIMIT ? OFFSET ?";
    $queryParams[] = $perPage;
    $queryParams[] = $pagination['offset'];
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($queryParams);
    $assignments = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Vehicle assignments error: " . $e->getMessage());
    $assignments = [];
    $totalItems = 0;
    $pagination = paginate(0);
}

include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-truck"></i> <?php echo e(t('vehicle')); ?> <?php echo e(t('assignments')); ?></h2>
            <?php if (hasAnyRole(['admin', 'manager'])): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?php echo e(t('assign_vehicle')); ?>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-9">
                        <select name="status" class="form-select">
                            <option value=""><?php echo e(t('all')); ?> <?php echo e(t('status')); ?></option>
                            <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>><?php echo e(t('on_field_work')); ?></option>
                            <option value="Completed" <?php echo $statusFilter === 'Completed' ? 'selected' : ''; ?>><?php echo e(t('completed')); ?></option>
                            <option value="Cancelled" <?php echo $statusFilter === 'Cancelled' ? 'selected' : ''; ?>><?php echo e(t('cancelled')); ?></option>
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
                <?php if (empty($assignments)): ?>
                    <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(t('vehicle')); ?></th>
                                    <th><?php echo e(t('assigned_to')); ?></th>
                                    <th><?php echo e(t('department')); ?></th>
                                    <th><?php echo e(t('purpose')); ?></th>
                                    <th><?php echo e(t('destination')); ?></th>
                                    <th><?php echo e(t('departure_date')); ?></th>
                                    <th><?php echo e(t('status')); ?></th>
                                    <th><?php echo e(t('actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($assignment['plate_number']); ?></strong><br>
                                        <small class="text-muted"><?php echo e($assignment['vehicle_type']); ?></small>
                                    </td>
                                    <td><?php echo e($assignment['employee_name']); ?></td>
                                    <td><?php echo e($assignment['department'] ?? '-'); ?></td>
                                    <td><?php echo e(substr($assignment['purpose'] ?? '', 0, 30)); ?><?php echo strlen($assignment['purpose'] ?? '') > 30 ? '...' : ''; ?></td>
                                    <td><?php echo e($assignment['destination'] ?? '-'); ?></td>
                                    <td><?php echo formatDate($assignment['departure_date'], DISPLAY_DATE_FORMAT); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'Active' => 'primary',
                                            'Completed' => 'success',
                                            'Cancelled' => 'danger'
                                        ];
                                        $class = $statusClass[$assignment['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $class; ?>">
                                            <?php echo $assignment['status'] === 'Active' ? e(t('on_field_work')) : e(t(strtolower($assignment['status']))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view.php?id=<?php echo $assignment['assignment_id']; ?>" 
                                               class="btn btn-info" title="<?php echo e(t('view')); ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (hasAnyRole(['admin', 'manager']) && $assignment['status'] === 'Active'): ?>
                                            <a href="return.php?id=<?php echo $assignment['assignment_id']; ?>" 
                                               class="btn btn-success" title="<?php echo e(t('return_vehicle')); ?>">
                                                <i class="bi bi-arrow-return-left"></i>
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
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>">
                                    <?php echo e(t('previous')); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>">
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

<?php include '../../../includes/footer.php'; ?>
