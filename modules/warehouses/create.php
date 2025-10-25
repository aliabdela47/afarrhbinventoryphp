<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/audit.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireRole('staff');

$pageTitle = __('add_warehouse');

$errors = [];
$name = '';
$location = '';
$contactperson = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    
    $name = sanitize($_POST['name'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $contactperson = sanitize($_POST['contactperson'] ?? '');
    
    // Validation
    if (empty($name)) {
        $errors[] = __('warehouse_name') . ' ' . __('field_required');
    }
    
    if (empty($errors)) {
        try {
            Database::query(
                "INSERT INTO WAREHOUSES (name, location, contactperson) VALUES (?, ?, ?)",
                [$name, $location, $contactperson]
            );
            
            $id = Database::lastInsertId();
            auditLog('Create Warehouse', 'WAREHOUSES', $id, null, compact('name', 'location', 'contactperson'));
            
            setFlash('success', __('created_success'));
            redirect(baseUrl('modules/warehouses/list.php'));
        } catch (PDOException $e) {
            $errors[] = __('error_occurred');
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-plus-circle"></i> <?php echo __('add_warehouse'); ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo baseUrl('index.php'); ?>"><?php echo __('dashboard'); ?></a></li>
                    <li class="breadcrumb-item"><a href="<?php echo baseUrl('modules/warehouses/list.php'); ?>"><?php echo __('warehouses'); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo __('create'); ?></li>
                </ol>
            </nav>
        </div>
        <a href="<?php echo baseUrl('modules/warehouses/list.php'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> <?php echo __('back'); ?>
        </a>
    </div>
    
    <!-- Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <?php echo csrfField(); ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <?php echo __('warehouse_name'); ?> <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo e($name); ?>" required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">
                                <?php echo __('location'); ?>
                            </label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?php echo e($location); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="contactperson" class="form-label">
                                <?php echo __('contact_person'); ?>
                            </label>
                            <input type="text" class="form-control" id="contactperson" name="contactperson" 
                                   value="<?php echo e($contactperson); ?>">
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> <?php echo __('save'); ?>
                            </button>
                            <a href="<?php echo baseUrl('modules/warehouses/list.php'); ?>" class="btn btn-secondary">
                                <?php echo __('cancel'); ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
