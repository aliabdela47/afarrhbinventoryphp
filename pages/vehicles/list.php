<?php
/**
 * Vehicle List
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication
requireAuth();

$pageTitle = 'Vehicles - ' . APP_NAME;

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$filter = $_GET['filter'] ?? '';

try {
    // Build query
    $countQuery = "SELECT COUNT(*) as count FROM VEHICLES WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (plate_number LIKE ? OR vehicle_code LIKE ? OR make LIKE ? OR model LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($status)) {
        $countQuery .= " AND status = ?";
        $params[] = $status;
    }
    
    if ($filter === 'service_due') {
        $countQuery .= " AND next_service_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND next_service_date >= CURDATE()";
    } elseif ($filter === 'insurance_due') {
        $countQuery .= " AND insurance_expiry <= DATE_ADD(CURDATE(), INTERVAL 60 DAY) AND insurance_expiry >= CURDATE()";
    }
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['count'];
    
    // Calculate pagination
    $pagination = paginate($totalItems, $page, $perPage);
    
    // Fetch vehicles
    $query = "SELECT * FROM VEHICLES WHERE 1=1";
    
    if (!empty($search)) {
        $query .= " AND (plate_number LIKE ? OR vehicle_code LIKE ? OR make LIKE ? OR model LIKE ?)";
    }
    
    if (!empty($status)) {
        $query .= " AND status = ?";
    }
    
    if ($filter === 'service_due') {
        $query .= " AND next_service_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND next_service_date >= CURDATE()";
    } elseif ($filter === 'insurance_due') {
        $query .= " AND insurance_expiry <= DATE_ADD(CURDATE(), INTERVAL 60 DAY) AND insurance_expiry >= CURDATE()";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($query);
    $params[] = $perPage;
    $params[] = $pagination['offset'];
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Vehicle list error: " . $e->getMessage());
    $vehicles = [];
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
            <h2><i class="bi bi-truck"></i> Vehicles</h2>
            <div>
                <a href="dashboard.php" class="btn btn-info">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <?php if (hasAnyRole(['admin', 'manager'])): ?>
                <a href="create.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Vehicle
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by plate, code, make, model..." 
                               value="<?php echo e($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="assigned" <?php echo $status === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                            <option value="maintenance" <?php echo $status === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Vehicles Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($vehicles)): ?>
                    <p class="text-muted text-center py-4">No vehicles found</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Plate Number</th>
                                    <th>Type</th>
                                    <th>Make/Model</th>
                                    <th>Year</th>
                                    <th>Status</th>
                                    <th>Mileage</th>
                                    <th>Next Service</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td><?php echo e($vehicle['vehicle_code']); ?></td>
                                    <td><strong><?php echo e($vehicle['plate_number']); ?></strong></td>
                                    <td><?php echo e($vehicle['vehicle_type']); ?></td>
                                    <td><?php echo e($vehicle['make'] . ' ' . $vehicle['model']); ?></td>
                                    <td><?php echo e($vehicle['year']); ?></td>
                                    <td>
                                        <?php 
                                        $badgeClass = [
                                            'available' => 'bg-success',
                                            'assigned' => 'bg-info',
                                            'maintenance' => 'bg-warning',
                                            'inactive' => 'bg-secondary'
                                        ][$vehicle['status']];
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo ucfirst($vehicle['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($vehicle['mileage'], 0); ?> km</td>
                                    <td>
                                        <?php 
                                        if ($vehicle['next_service_date']) {
                                            $serviceDays = (strtotime($vehicle['next_service_date']) - time()) / (60 * 60 * 24);
                                            $serviceClass = $serviceDays <= 30 ? 'text-danger' : 'text-muted';
                                            echo '<span class="' . $serviceClass . '">' . formatDate($vehicle['next_service_date'], DISPLAY_DATE_FORMAT) . '</span>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view.php?id=<?php echo $vehicle['id']; ?>" 
                                               class="btn btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (hasAnyRole(['admin', 'manager'])): ?>
                                            <a href="edit.php?id=<?php echo $vehicle['id']; ?>" 
                                               class="btn btn-warning" title="Edit">
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
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?>">
                                    Previous
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?>">
                                    Next
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
