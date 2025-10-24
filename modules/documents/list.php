<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireLogin();
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Documents Module - Coming Soon
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
