<?php
/**
 * Warehouse List
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication
requireAuth();

$pageTitle = t('warehouses') . ' - ' . APP_NAME;

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

// Get search query
$search = $_GET['search'] ?? '';

try {
    // Count total records
    $countQuery = "SELECT COUNT(*) as count FROM WAREHOUSES WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (name LIKE ? OR location LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['count'];
    
    // Calculate pagination
    $pagination = paginate($totalItems, $page, $perPage);
    
    // Fetch warehouses
    $query = "
        SELECT w.*, u.full_name as manager_name
        FROM WAREHOUSES w
        LEFT JOIN USERS u ON w.manager_id = u.id
        WHERE 1=1
    ";
    
    if (!empty($search)) {
        $query .= " AND (w.name LIKE ? OR w.location LIKE ?)";
    }
    
    $query .= " ORDER BY w.name ASC LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($query);
    
    if (!empty($search)) {
        $stmt->execute(["%$search%", "%$search%", $perPage, $pagination['offset']]);
    } else {
        $stmt->execute([$perPage, $pagination['offset']]);
    }
    
    $warehouses = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Warehouse list error: " . $e->getMessage());
    $warehouses = [];
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
            <h2><i class="bi bi-building"></i> <?php echo e(t('warehouses')); ?></h2>
            <?php if (hasAnyRole(['admin', 'manager'])): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?php echo e(t('add')); ?> <?php echo e(t('warehouse')); ?>
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="<?php echo e(t('search')); ?>..." 
                               value="<?php echo e($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> <?php echo e(t('search')); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Warehouses Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($warehouses)): ?>
                    <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(t('name')); ?></th>
                                    <th><?php echo e(t('location')); ?></th>
                                    <th>Manager</th>
                                    <th><?php echo e(t('status')); ?></th>
                                    <th><?php echo e(t('created_at')); ?></th>
                                    <th><?php echo e(t('actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($warehouses as $warehouse): ?>
                                <tr>
                                    <td><?php echo e($warehouse['name']); ?></td>
                                    <td><?php echo e($warehouse['location']); ?></td>
                                    <td><?php echo e($warehouse['manager_name'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($warehouse['is_active']): ?>
                                            <span class="badge bg-success"><?php echo e(t('active')); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo e(t('inactive')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($warehouse['created_at'], DISPLAY_DATE_FORMAT); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view.php?id=<?php echo $warehouse['id']; ?>" 
                                               class="btn btn-info" title="<?php echo e(t('view')); ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (hasAnyRole(['admin', 'manager'])): ?>
                                            <a href="edit.php?id=<?php echo $warehouse['id']; ?>" 
                                               class="btn btn-warning" title="<?php echo e(t('edit')); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (hasRole('admin')): ?>
                                            <a href="delete.php?id=<?php echo $warehouse['id']; ?>" 
                                               class="btn btn-danger" title="<?php echo e(t('delete')); ?>"
                                               onclick="return confirmDelete(this.href)">
                                                <i class="bi bi-trash"></i>
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
