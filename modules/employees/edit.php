<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/audit.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireRole('staff');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$employee = Database::fetchOne("SELECT * FROM EMPLIST WHERE id = ?", [$id]);
if (!$employee) { setFlash('error', 'Employee not found'); redirect(baseUrl('modules/employees/list.php')); }

$errors = [];
$name = $employee['name'];
$nameam = $employee['nameam'];
$salary = $employee['salary'];
$taamagoli = $employee['taamagoli'];
$directorate = $employee['directorate'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    $name = sanitize($_POST['name'] ?? '');
    $nameam = sanitize($_POST['nameam'] ?? '');
    $salary = sanitize($_POST['salary'] ?? '');
    $taamagoli = sanitize($_POST['taamagoli'] ?? '');
    $directorate = sanitize($_POST['directorate'] ?? '');
    
    if (empty($name)) $errors[] = __('employee_name') . ' ' . __('field_required');
    
    if (empty($errors)) {
        try {
            $oldData = $employee;
            $newData = compact('name', 'nameam', 'salary', 'taamagoli', 'directorate');
            Database::query("UPDATE EMPLIST SET name = ?, nameam = ?, salary = ?, taamagoli = ?, directorate = ? WHERE id = ?",
                [$name, $nameam, $salary, $taamagoli, $directorate, $id]);
            auditLog('Update Employee', 'EMPLIST', $id, $oldData, $newData);
            setFlash('success', __('updated_success'));
            redirect(baseUrl('modules/employees/list.php'));
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
        <div><h1 class="h3 mb-0"><i class="bi bi-pencil"></i> <?php echo __('edit_employee'); ?></h1></div>
        <a href="<?php echo baseUrl('modules/employees/list.php'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> <?php echo __('back'); ?></a>
    </div>
    
    <div class="row"><div class="col-lg-8"><div class="card"><div class="card-body">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        
        <form method="POST">
            <?php echo csrfField(); ?>
            <div class="mb-3"><label class="form-label"><?php echo __('employee_name'); ?> <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?php echo e($name); ?>" required autofocus></div>
            <div class="mb-3"><label class="form-label"><?php echo __('amharic_name'); ?></label>
                <input type="text" class="form-control" name="nameam" value="<?php echo e($nameam); ?>"></div>
            <div class="mb-3"><label class="form-label"><?php echo __('taamagoli'); ?></label>
                <input type="text" class="form-control" name="taamagoli" value="<?php echo e($taamagoli); ?>"></div>
            <div class="mb-3"><label class="form-label"><?php echo __('directorate'); ?></label>
                <input type="text" class="form-control" name="directorate" value="<?php echo e($directorate); ?>"></div>
            <div class="mb-3"><label class="form-label"><?php echo __('salary'); ?></label>
                <input type="number" step="0.01" class="form-control" name="salary" value="<?php echo e($salary); ?>"></div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?php echo __('save'); ?></button>
                <a href="<?php echo baseUrl('modules/employees/list.php'); ?>" class="btn btn-secondary"><?php echo __('cancel'); ?></a>
            </div>
        </form>
    </div></div></div></div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
