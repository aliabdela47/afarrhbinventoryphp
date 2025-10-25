<?php
/**
 * Create Warehouse
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication and admin/manager role
requireRole(['admin', 'manager']);

$pageTitle = t('add') . ' ' . t('warehouse') . ' - ' . APP_NAME;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'CSRF token validation failed');
        redirect('create.php');
    }
    
    $errors = [];
    
    // Validate input
    if (empty($_POST['name'])) {
        $errors[] = t('name') . ' ' . t('required_field');
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO WAREHOUSES (name, location, manager_id) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['name'],
                $_POST['location'] ?? null,
                !empty($_POST['manager_id']) ? $_POST['manager_id'] : null
            ]);
            
            $warehouseId = $pdo->lastInsertId();
            
            logAudit($pdo, 'CREATE', 'WAREHOUSES', $warehouseId, null, $_POST);
            
            flash('success', t('save_success'));
            redirect('view.php?id=' . $warehouseId);
            
        } catch (PDOException $e) {
            error_log("Warehouse creation error: " . $e->getMessage());
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
            <h2><i class="bi bi-building"></i> <?php echo e(t('add')); ?> <?php echo e(t('warehouse')); ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../dashboard.php"><?php echo e(t('dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="list.php"><?php echo e(t('warehouses')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo e(t('add')); ?></li>
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
                                   value="<?php echo e($_POST['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo e(t('location')); ?></label>
                            <input type="text" name="location" class="form-control" 
                                   value="<?php echo e($_POST['location'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo e(t('manager')); ?></label>
                            <select name="manager_id" class="form-select">
                                <option value="">Select Manager</option>
                                <?php foreach ($managers as $manager): ?>
                                <option value="<?php echo $manager['id']; ?>"
                                        <?php echo (isset($_POST['manager_id']) && $_POST['manager_id'] == $manager['id']) ? 'selected' : ''; ?>>
                                    <?php echo e($manager['full_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> <?php echo e(t('save')); ?>
                        </button>
                        <a href="list.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> <?php echo e(t('cancel')); ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
