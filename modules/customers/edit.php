<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireRole('staff');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customer = Database::fetchOne("SELECT * FROM CUSTOMERS WHERE customerid = ?", [$id]);
if (!$customer) { setFlash('error', 'Customer not found'); redirect(baseUrl('modules/customers/list.php')); }

$employees = Database::fetchAll("SELECT id, name FROM EMPLIST ORDER BY name");
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    $name = sanitize($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'Internal';
    $empid = !empty($_POST['empid']) ? (int)$_POST['empid'] : null;
    $purpose = sanitize($_POST['purpose'] ?? '');
    $durationstart = $_POST['durationstart'] ?? null;
    $duration_end = $_POST['duration_end'] ?? null;
    
    if (empty($name)) $errors[] = __('customer_name') . ' ' . __('field_required');
    
    if (empty($errors)) {
        Database::query("UPDATE CUSTOMERS SET name = ?, type = ?, empid = ?, purpose = ?, durationstart = ?, duration_end = ? WHERE customerid = ?",
            [$name, $type, $empid, $purpose, $durationstart, $duration_end, $id]);
        setFlash('success', __('updated_success'));
        redirect(baseUrl('modules/customers/list.php'));
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0"><i class="bi bi-pencil"></i> <?php echo __('edit_customer'); ?></h1></div>
        <a href="<?php echo baseUrl('modules/customers/list.php'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> <?php echo __('back'); ?></a>
    </div>
    
    <div class="row"><div class="col-lg-8"><div class="card"><div class="card-body">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        
        <form method="POST">
            <?php echo csrfField(); ?>
            <div class="mb-3"><label class="form-label"><?php echo __('customer_name'); ?> <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?php echo e($customer['name']); ?>" required autofocus></div>
            <div class="mb-3"><label class="form-label"><?php echo __('customer_type'); ?></label>
                <select class="form-select" name="type">
                    <option value="Internal" <?php echo $customer['type'] == 'Internal' ? 'selected' : ''; ?>><?php echo __('internal'); ?></option>
                    <option value="External" <?php echo $customer['type'] == 'External' ? 'selected' : ''; ?>><?php echo __('external'); ?></option>
                </select></div>
            <div class="mb-3"><label class="form-label"><?php echo __('employee_name'); ?></label>
                <select class="form-select" name="empid">
                    <option value="">-- Select --</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?php echo $emp['id']; ?>" <?php echo $customer['empid'] == $emp['id'] ? 'selected' : ''; ?>><?php echo e($emp['name']); ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="mb-3"><label class="form-label"><?php echo __('purpose'); ?></label>
                <textarea class="form-control" name="purpose" rows="3"><?php echo e($customer['purpose']); ?></textarea></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label"><?php echo __('duration_start'); ?></label>
                    <input type="date" class="form-control" name="durationstart" value="<?php echo e($customer['durationstart']); ?>"></div>
                <div class="col-md-6 mb-3"><label class="form-label"><?php echo __('duration_end'); ?></label>
                    <input type="date" class="form-control" name="duration_end" value="<?php echo e($customer['duration_end']); ?>"></div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?php echo __('save'); ?></button>
                <a href="<?php echo baseUrl('modules/customers/list.php'); ?>" class="btn btn-secondary"><?php echo __('cancel'); ?></a>
            </div>
        </form>
    </div></div></div></div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
