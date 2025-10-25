<?php
/**
 * Requests List (Model-20)
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication
requireAuth();

$pageTitle = t('requests') . ' - ' . APP_NAME;

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

// Get filters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

try {
    // Count total records
    $countQuery = "SELECT COUNT(*) as count FROM REQUESTS r WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (r.request_number LIKE ? OR r.department LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($statusFilter)) {
        $countQuery .= " AND r.status = ?";
        $params[] = $statusFilter;
    }
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['count'];
    
    // Calculate pagination
    $pagination = paginate($totalItems, $page, $perPage);
    
    // Fetch requests
    $query = "
        SELECT r.*, 
               e.full_name as requester_name,
               u.full_name as approver_name
        FROM REQUESTS r
        LEFT JOIN EMPLIST e ON r.requester_id = e.id
        LEFT JOIN USERS u ON r.approved_by = u.id
        WHERE 1=1
    ";
    
    $queryParams = [];
    
    if (!empty($search)) {
        $query .= " AND (r.request_number LIKE ? OR r.department LIKE ?)";
        $queryParams[] = "%$search%";
        $queryParams[] = "%$search%";
    }
    
    if (!empty($statusFilter)) {
        $query .= " AND r.status = ?";
        $queryParams[] = $statusFilter;
    }
    
    $query .= " ORDER BY r.request_date DESC, r.created_at DESC LIMIT ? OFFSET ?";
    $queryParams[] = $perPage;
    $queryParams[] = $pagination['offset'];
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($queryParams);
    $requests = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Requests list error: " . $e->getMessage());
    $requests = [];
    $totalItems = 0;
    $pagination = paginate(0);
}

// Include header
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-clipboard-check"></i> <?php echo e(t('requests')); ?> (Model-20)</h2>
            <?php if (hasAnyRole(['admin', 'manager', 'staff'])): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?php echo e(t('new_request')); ?>
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" 
                               placeholder="<?php echo e(t('search')); ?> by request number or department..." 
                               value="<?php echo e($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value=""><?php echo e(t('all')); ?> <?php echo e(t('status')); ?></option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>><?php echo e(t('pending')); ?></option>
                            <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>><?php echo e(t('approved')); ?></option>
                            <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>><?php echo e(t('rejected')); ?></option>
                            <option value="issued" <?php echo $statusFilter === 'issued' ? 'selected' : ''; ?>><?php echo e(t('issued')); ?></option>
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
        
        <!-- Requests Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($requests)): ?>
                    <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(t('request_number')); ?></th>
                                    <th><?php echo e(t('requester')); ?></th>
                                    <th><?php echo e(t('department')); ?> / <?php echo e(t('directorate')); ?></th>
                                    <th><?php echo e(t('request_date')); ?></th>
                                    <th><?php echo e(t('purpose')); ?></th>
                                    <th><?php echo e(t('status')); ?></th>
                                    <th><?php echo e(t('actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?php echo e($request['request_number']); ?></td>
                                    <td><?php echo e($request['requester_name'] ?? '-'); ?></td>
                                    <td><?php echo e($request['department'] ?? '-'); ?></td>
                                    <td><?php echo formatDate($request['request_date'], DISPLAY_DATE_FORMAT); ?></td>
                                    <td><?php echo e(substr($request['purpose'] ?? '', 0, 50)); ?><?php echo strlen($request['purpose'] ?? '') > 50 ? '...' : ''; ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'issued' => 'info'
                                        ];
                                        $class = $statusClass[$request['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $class; ?>"><?php echo e(t($request['status'])); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view.php?id=<?php echo $request['id']; ?>" 
                                               class="btn btn-info" title="<?php echo e(t('view')); ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (hasAnyRole(['admin', 'manager']) && $request['status'] === 'pending'): ?>
                                            <a href="approve.php?id=<?php echo $request['id']; ?>" 
                                               class="btn btn-success" title="<?php echo e(t('approve')); ?>">
                                                <i class="bi bi-check-circle"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (hasAnyRole(['admin', 'manager', 'staff']) && in_array($request['status'], ['pending', 'approved'])): ?>
                                            <a href="edit.php?id=<?php echo $request['id']; ?>" 
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
                    
                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['has_previous']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>">
                                    <?php echo e(t('previous')); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>">
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
