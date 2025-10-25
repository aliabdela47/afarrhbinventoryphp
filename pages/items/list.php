<?php
/**
 * Items List (Model-19)
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication
requireAuth();

$pageTitle = t('items') . ' - ' . APP_NAME;

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

// Get search query
$search = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$warehouseFilter = $_GET['warehouse'] ?? '';
$statusFilter = $_GET['status'] ?? '';

try {
    // Count total records
    $countQuery = "SELECT COUNT(*) as count FROM ITEMS i WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (i.name LIKE ? OR i.item_code LIKE ? OR i.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($categoryFilter)) {
        $countQuery .= " AND i.category_id = ?";
        $params[] = $categoryFilter;
    }
    
    if (!empty($warehouseFilter)) {
        $countQuery .= " AND i.warehouse_id = ?";
        $params[] = $warehouseFilter;
    }
    
    if ($statusFilter === 'low_stock') {
        $countQuery .= " AND i.current_stock <= i.reorder_level";
    } elseif ($statusFilter === 'active') {
        $countQuery .= " AND i.is_active = 1";
    } elseif ($statusFilter === 'inactive') {
        $countQuery .= " AND i.is_active = 0";
    }
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['count'];
    
    // Calculate pagination
    $pagination = paginate($totalItems, $page, $perPage);
    
    // Fetch items
    $query = "
        SELECT i.*, 
               c.name as category_name,
               w.name as warehouse_name
        FROM ITEMS i
        LEFT JOIN CATEGORIES c ON i.category_id = c.id
        LEFT JOIN WAREHOUSES w ON i.warehouse_id = w.id
        WHERE 1=1
    ";
    
    $queryParams = [];
    
    if (!empty($search)) {
        $query .= " AND (i.name LIKE ? OR i.item_code LIKE ? OR i.description LIKE ?)";
        $queryParams[] = "%$search%";
        $queryParams[] = "%$search%";
        $queryParams[] = "%$search%";
    }
    
    if (!empty($categoryFilter)) {
        $query .= " AND i.category_id = ?";
        $queryParams[] = $categoryFilter;
    }
    
    if (!empty($warehouseFilter)) {
        $query .= " AND i.warehouse_id = ?";
        $queryParams[] = $warehouseFilter;
    }
    
    if ($statusFilter === 'low_stock') {
        $query .= " AND i.current_stock <= i.reorder_level";
    } elseif ($statusFilter === 'active') {
        $query .= " AND i.is_active = 1";
    } elseif ($statusFilter === 'inactive') {
        $query .= " AND i.is_active = 0";
    }
    
    $query .= " ORDER BY i.name ASC LIMIT ? OFFSET ?";
    $queryParams[] = $perPage;
    $queryParams[] = $pagination['offset'];
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($queryParams);
    $items = $stmt->fetchAll();
    
    // Get categories for filter
    $categoriesStmt = $pdo->query("SELECT id, name FROM CATEGORIES WHERE is_active = 1 ORDER BY name");
    $categories = $categoriesStmt->fetchAll();
    
    // Get warehouses for filter
    $warehousesStmt = $pdo->query("SELECT id, name FROM WAREHOUSES WHERE is_active = 1 ORDER BY name");
    $warehouses = $warehousesStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Items list error: " . $e->getMessage());
    $items = [];
    $categories = [];
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
            <h2><i class="bi bi-box-seam"></i> <?php echo e(t('items')); ?> (Model-19)</h2>
            <?php if (hasAnyRole(['admin', 'manager', 'staff'])): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?php echo e(t('add')); ?> <?php echo e(t('item_name')); ?>
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" 
                               placeholder="<?php echo e(t('search')); ?>..." 
                               value="<?php echo e($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-select">
                            <option value=""><?php echo e(t('all')); ?> <?php echo e(t('categories')); ?></option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="warehouse" class="form-select">
                            <option value=""><?php echo e(t('all')); ?> <?php echo e(t('warehouses')); ?></option>
                            <?php foreach ($warehouses as $wh): ?>
                            <option value="<?php echo $wh['id']; ?>" <?php echo $warehouseFilter == $wh['id'] ? 'selected' : ''; ?>>
                                <?php echo e($wh['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value=""><?php echo e(t('all')); ?> <?php echo e(t('status')); ?></option>
                            <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>><?php echo e(t('active')); ?></option>
                            <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>><?php echo e(t('inactive')); ?></option>
                            <option value="low_stock" <?php echo $statusFilter === 'low_stock' ? 'selected' : ''; ?>><?php echo e(t('low_stock')); ?></option>
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
        
        <!-- Items Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($items)): ?>
                    <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(t('item_code')); ?></th>
                                    <th><?php echo e(t('name')); ?></th>
                                    <th><?php echo e(t('category')); ?></th>
                                    <th><?php echo e(t('warehouse')); ?></th>
                                    <th><?php echo e(t('current_stock')); ?></th>
                                    <th><?php echo e(t('unit')); ?></th>
                                    <th><?php echo e(t('unit_price')); ?></th>
                                    <th><?php echo e(t('status')); ?></th>
                                    <th><?php echo e(t('created_at')); ?></th>
                                    <th><?php echo e(t('actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo e($item['item_code']); ?></td>
                                    <td><?php echo e($item['name']); ?></td>
                                    <td><?php echo e($item['category_name'] ?? '-'); ?></td>
                                    <td><?php echo e($item['warehouse_name'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($item['current_stock'] <= $item['reorder_level']): ?>
                                            <span class="badge bg-danger"><?php echo e($item['current_stock']); ?></span>
                                        <?php else: ?>
                                            <?php echo e($item['current_stock']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($item['unit']); ?></td>
                                    <td><?php echo formatCurrency($item['unit_price']); ?></td>
                                    <td>
                                        <?php if ($item['is_active']): ?>
                                            <span class="badge bg-success"><?php echo e(t('active')); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo e(t('inactive')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($item['created_at'], DISPLAY_DATE_FORMAT); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view.php?id=<?php echo $item['id']; ?>" 
                                               class="btn btn-info" title="<?php echo e(t('view')); ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (hasAnyRole(['admin', 'manager', 'staff'])): ?>
                                            <a href="edit.php?id=<?php echo $item['id']; ?>" 
                                               class="btn btn-warning" title="<?php echo e(t('edit')); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (hasRole('admin')): ?>
                                            <a href="delete.php?id=<?php echo $item['id']; ?>" 
                                               class="btn btn-danger" title="<?php echo e(t('delete')); ?>"
                                               onclick="return confirmDelete()">
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
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($categoryFilter) ? '&category=' . $categoryFilter : ''; ?><?php echo !empty($warehouseFilter) ? '&warehouse=' . $warehouseFilter : ''; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>">
                                    <?php echo e(t('previous')); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($categoryFilter) ? '&category=' . $categoryFilter : ''; ?><?php echo !empty($warehouseFilter) ? '&warehouse=' . $warehouseFilter : ''; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($categoryFilter) ? '&category=' . $categoryFilter : ''; ?><?php echo !empty($warehouseFilter) ? '&warehouse=' . $warehouseFilter : ''; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>">
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

<script>
function confirmDelete() {
    return confirm('<?php echo e(t('delete_confirm')); ?>');
}
</script>

<?php include '../../includes/footer.php'; ?>
