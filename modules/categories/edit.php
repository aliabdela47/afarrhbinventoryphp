<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/audit.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireRole('staff');
$pageTitle = __('edit_category');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$category = Database::fetchOne("SELECT * FROM CATEGORIES WHERE id = ?", [$id]);

if (!$category) {
    setFlash('error', 'Category not found');
    redirect(baseUrl('modules/categories/list.php'));
}

$errors = [];
$name = $category['name'];
$description = $category['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    
    if (empty($name)) $errors[] = __('category_name') . ' ' . __('field_required');
    
    if (empty($errors)) {
        try {
            $oldData = $category;
            $newData = compact('name', 'description');
            Database::query("UPDATE CATEGORIES SET name = ?, description = ? WHERE id = ?", [$name, $description, $id]);
            auditLog('Update Category', 'CATEGORIES', $id, $oldData, $newData);
            setFlash('success', __('updated_success'));
            redirect(baseUrl('modules/categories/list.php'));
        } catch (PDOException $e) {
            $errors[] = __('error_occurred');
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-pencil"></i> <?php echo __('edit_category'); ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo baseUrl('index.php'); ?>"><?php echo __('dashboard'); ?></a></li>
                    <li class="breadcrumb-item"><a href="<?php echo baseUrl('modules/categories/list.php'); ?>"><?php echo __('categories'); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo __('edit'); ?></li>
                </ol>
            </nav>
        </div>
        <a href="<?php echo baseUrl('modules/categories/list.php'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> <?php echo __('back'); ?>
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <div class="mb-3">
                            <label for="name" class="form-label"><?php echo __('category_name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo e($name); ?>" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label"><?php echo __('description'); ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo e($description); ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?php echo __('save'); ?></button>
                            <a href="<?php echo baseUrl('modules/categories/list.php'); ?>" class="btn btn-secondary"><?php echo __('cancel'); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
