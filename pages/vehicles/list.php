<?php
/**
 * Vehicles List
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();

$pageTitle = t('vehicles') . ' - ' . APP_NAME;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';

try {
    $countQuery = "SELECT COUNT(*) as count FROM VEHICLES WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (plate_number LIKE ? OR model LIKE ? OR manufacturer LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($statusFilter)) {
        $countQuery .= " AND status = ?";
        $params[] = $statusFilter;
    }
    
    if (!empty($typeFilter)) {
        $countQuery .= " AND type = ?";
        $params[] = $typeFilter;
    }
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['count'];
    
    $pagination = paginate($totalItems, $page, $perPage);
    
    $query = "SELECT * FROM VEHICLES WHERE 1=1";
    
    $queryParams = [];
    
    if (!empty($search)) {
        $query .= " AND (plate_number LIKE ? OR model LIKE ? OR manufacturer LIKE ?)";
        $queryParams[] = "%$search%";
        $queryParams[] = "%$search%";
        $queryParams[] = "%$search%";
    }
    
    if (!empty($statusFilter)) {
        $query .= " AND status = ?";
        $queryParams[] = $statusFilter;
    }
    
    if (!empty($typeFilter)) {
        $query .= " AND type = ?";
        $queryParams[] = $typeFilter;
    }
    
    $query .= " ORDER BY plate_number ASC LIMIT ? OFFSET ?";
    $queryParams[] = $perPage;
    $queryParams[] = $pagination['offset'];
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($queryParams);
    $vehicles = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Vehicles list error: " . $e->getMessage());
    $vehicles = [];
    $totalItems = 0;
    $pagination = paginate(0);
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-truck"></i> <?php echo e(t('vehicles')); ?></h2>
            <?php if (hasAnyRole(['admin', 'manager'])): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?php echo e(t('add')); ?> <?php echo e(t('vehicle')); ?>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="<?php echo e(t('search')); ?>..." 
                               value="<?php echo e($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value=""><?php echo e(t('all')); ?> <?php echo e(t('type')); ?>s</option>
                            <option value="Car" <?php echo $typeFilter === 'Car' ? 'selected' : ''; ?>><?php echo e(t('car')); ?></option>
                            <option value="Pickup" <?php echo $typeFilter === 'Pickup' ? 'selected' : ''; ?>><?php echo e(t('pickup')); ?></option>
                            <option value="Truck" <?php echo $typeFilter === 'Truck' ? 'selected' : ''; ?>><?php echo e(t('truck')); ?></option>
                            <option value="Motorcycle" <?php echo $typeFilter === 'Motorcycle' ? 'selected' : ''; ?>><?php echo e(t('motorcycle')); ?></option>
                            <option value="Ambulance" <?php echo $typeFilter === 'Ambulance' ? 'selected' : ''; ?>><?php echo e(t('ambulance')); ?></option>
                            <option value="Other" <?php echo $typeFilter === 'Other' ? 'selected' : ''; ?>><?php echo e(t('other')); ?></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value=""><?php echo e(t('all')); ?> <?php echo e(t('status')); ?></option>
                            <option value="Available" <?php echo $statusFilter === 'Available' ? 'selected' : ''; ?>><?php echo e(t('available')); ?></option>
                            <option value="Assigned" <?php echo $statusFilter === 'Assigned' ? 'selected' : ''; ?>><?php echo e(t('assigned')); ?></option>
                            <option value="In Maintenance" <?php echo $statusFilter === 'In Maintenance' ? 'selected' : ''; ?>><?php echo e(t('in_maintenance')); ?></option>
                            <option value="Out of Service" <?php echo $statusFilter === 'Out of Service' ? 'selected' : ''; ?>><?php echo e(t('out_of_service')); ?></option>
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
                <?php if (empty($vehicles)): ?>
                    <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(t('plate_number')); ?></th>
                                    <th><?php echo e(t('type')); ?></th>
                                    <th><?php echo e(t('model')); ?></th>
                                    <th><?php echo e(t('manufacturer')); ?></th>
                                    <th><?php echo e(t('year')); ?></th>
                                    <th><?php echo e(t('status')); ?></th>
                                    <th><?php echo e(t('current_location')); ?></th>
                                    <th><?php echo e(t('actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td><strong><?php echo e($vehicle['plate_number']); ?></strong></td>
                                    <td><?php echo e(t(strtolower($vehicle['type']))); ?></td>
                                    <td><?php echo e($vehicle['model']); ?></td>
                                    <td><?php echo e($vehicle['manufacturer']); ?></td>
                                    <td><?php echo e($vehicle['year']); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'Available' => 'success',
                                            'Assigned' => 'primary',
                                            'In Maintenance' => 'warning',
                                            'Out of Service' => 'danger'
                                        ];
                                        $class = $statusClass[$vehicle['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $class; ?>"><?php echo e(t(str_replace(' ', '_', strtolower($vehicle['status'])))); ?></span>
                                    </td>
                                    <td><?php echo e($vehicle['current_location'] ?? '-'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view.php?id=<?php echo $vehicle['vehicle_id']; ?>" 
                                               class="btn btn-info" title="<?php echo e(t('view')); ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (hasAnyRole(['admin', 'manager'])): ?>
                                            <a href="edit.php?id=<?php echo $vehicle['vehicle_id']; ?>" 
                                               class="btn btn-warning" title="<?php echo e(t('edit')); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($vehicle['status'] === 'Available'): ?>
                                            <a href="assignments/create.php?vehicle_id=<?php echo $vehicle['vehicle_id']; ?>" 
                                               class="btn btn-primary" title="<?php echo e(t('assign_vehicle')); ?>">
                                                <i class="bi bi-person-plus"></i>
                                            </a>
                                            <?php endif; ?>
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
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?><?php echo !empty($typeFilter) ? '&type=' . $typeFilter : ''; ?>">
                                    <?php echo e(t('previous')); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?><?php echo !empty($typeFilter) ? '&type=' . $typeFilter : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?><?php echo !empty($typeFilter) ? '&type=' . $typeFilter : ''; ?>">
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
