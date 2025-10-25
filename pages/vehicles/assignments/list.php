<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/auth.php';

requireAuth();
$pageTitle = 'Vehicle Assignments - ' . APP_NAME;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;
$status = $_GET['status'] ?? '';

try {
    $countQuery = "SELECT COUNT(*) as count FROM VEHICLEASSIGNMENTS WHERE 1=1";
    $params = [];
    
    if (!empty($status)) {
        $countQuery .= " AND status = ?";
        $params[] = $status;
    }
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['count'];
    
    $pagination = paginate($totalItems, $page, $perPage);
    
    $query = "
        SELECT va.*, v.plate_number, v.vehicle_type, e.full_name as driver_name, u.full_name as assigned_by_name
        FROM VEHICLEASSIGNMENTS va
        JOIN VEHICLES v ON va.vehicle_id = v.id
        JOIN EMPLIST e ON va.driver_id = e.id
        LEFT JOIN USERS u ON va.assigned_by = u.id
        WHERE 1=1
    ";
    
    if (!empty($status)) {
        $query .= " AND va.status = ?";
    }
    
    $query .= " ORDER BY va.assignment_date DESC LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($query);
    $params[] = $perPage;
    $params[] = $pagination['offset'];
    $stmt->execute($params);
    $assignments = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Assignments list error: " . $e->getMessage());
    $assignments = [];
    $pagination = paginate(0);
}

include '../../../includes/header.php';
include '../../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-calendar-check"></i> Vehicle Assignments</h2>
            <?php if (hasAnyRole(['admin', 'manager'])): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Assignment
            </a>
            <?php endif; ?>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if (empty($assignments)): ?>
                    <p class="text-muted text-center py-4">No assignments found</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Assignment #</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Date</th>
                                    <th>Destination</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td><?php echo e($assignment['assignment_number']); ?></td>
                                    <td><strong><?php echo e($assignment['plate_number']); ?></strong></td>
                                    <td><?php echo e($assignment['driver_name']); ?></td>
                                    <td><?php echo formatDate($assignment['assignment_date'], DISPLAY_DATE_FORMAT); ?></td>
                                    <td><?php echo e($assignment['destination']); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            echo ['active' => 'bg-info', 'completed' => 'bg-success', 'cancelled' => 'bg-secondary'][$assignment['status']]; 
                                        ?>">
                                            <?php echo ucfirst($assignment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view.php?id=<?php echo $assignment['id']; ?>" class="btn btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include '../../../includes/footer.php'; ?>
