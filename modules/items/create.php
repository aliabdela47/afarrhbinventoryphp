<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireRole('staff');
$pageTitle = __('add_item');

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0"><i class="bi bi-plus-circle"></i> <?php echo __('add_item'); ?> (Model-19)</h1></div>
        <a href="<?php echo baseUrl('modules/items/list.php'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> <?php echo __('back'); ?></a>
    </div>
    
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Create item form - Coming Soon
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
