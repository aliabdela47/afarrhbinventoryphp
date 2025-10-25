<?php
/**
 * Edit Warehouse
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication and admin/manager role
requireRole(['admin', 'manager']);

$pageTitle = t('edit') . ' ' . t('warehouse') . ' - ' . APP_NAME;

// Get warehouse ID
$warehouseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$warehouseId) {
    flash('error', 'Invalid warehouse ID');
    redirect('list.php');
}

// Get warehouse data
try {
    $stmt = $pdo->prepare("SELECT * FROM WAREHOUSES WHERE id = ?");
    $stmt->execute([$warehouseId]);
    $warehouse = $stmt->fetch();
    
    if (!$warehouse) {
        flash('error', 'Warehouse not found');
        redirect('list.php');
    }
} catch (PDOException $e) {
    error_log("Get warehouse error: " . $e->getMessage());
    flash('error', t('error_occurred'));
    redirect('list.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'CSRF token validation failed');
        redirect('edit.php?id=' . $warehouseId);
    }
    
    $errors = [];
    
    // Validate input
    if (empty($_POST['name'])) {
        $errors[] = t('name') . ' ' . t('required_field');
    }
    
    if (empty($errors)) {
        try {
            $oldValues = $warehouse;
            
            $stmt = $pdo->prepare("
                UPDATE WAREHOUSES 
                SET name = ?, location = ?, manager_id = ?, is_active = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['name'],
                $_POST['location'] ?? null,
                !empty($_POST['manager_id']) ? $_POST['manager_id'] : null,
                isset($_POST['is_active']) ? 1 : 0,
                $warehouseId
            ]);
            
            logAudit($pdo, 'UPDATE', 'WAREHOUSES', $warehouseId, $oldValues, $_POST);
            
            flash('success', t('save_success'));
            redirect('view.php?id=' . $warehouseId);
            
        } catch (PDOException $e) {
            error_log("Warehouse update error: " . $e->getMessage());
            $errors[] = t('error_occurred');
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            flash('error', $error);
        }
    }
}

// Get managers for dropdown
try {
    $managersStmt = $pdo->query("
        SELECT id, full_name 
        FROM USERS 
        WHERE role IN ('admin', 'manager') AND is_active = 1 
        ORDER BY full_name
    ");
    $managers = $managersStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Get managers error: " . $e->getMessage());
    $managers = [];
}

// Include header
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="mb-4">
            <h2><i class="bi bi-building"></i> <?php echo e(t('edit')); ?> <?php echo e(t('warehouse')); ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../dashboard.php"><?php echo e(t('dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="list.php"><?php echo e(t('warehouses')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo e(t('edit')); ?></li>
                </ol>
            </nav>
        </div>
        
        <!-- Form Card -->
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo e(t('name')); ?> *</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo e($_POST['name'] ?? $warehouse['name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo e(t('location')); ?></label>
                            <input type="text" name="location" class="form-control" 
                                   value="<?php echo e($_POST['location'] ?? $warehouse['location']); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo e(t('manager')); ?></label>
                            <select name="manager_id" class="form-select">
                                <option value="">Select Manager</option>
                                <?php foreach ($managers as $manager): ?>
                                <option value="<?php echo $manager['id']; ?>"
                                        <?php echo (isset($_POST['manager_id']) ? $_POST['manager_id'] : $warehouse['manager_id']) == $manager['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($manager['full_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo e(t('status')); ?></label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" 
                                       <?php echo (isset($_POST['is_active']) ? isset($_POST['is_active']) : $warehouse['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label"><?php echo e(t('active')); ?></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> <?php echo e(t('save')); ?>
                        </button>
                        <a href="view.php?id=<?php echo $warehouseId; ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> <?php echo e(t('cancel')); ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
