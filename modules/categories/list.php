<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/audit.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireLogin();

$pageTitle = __('categories');

// Handle delete
if (isset($_GET['delete']) && canDelete()) {
    $id = (int)$_GET['delete'];
    
    // Check if category has items
    $itemsCount = Database::fetchOne("SELECT COUNT(*) as count FROM ITEMS WHERE categoryid = ?", [$id])['count'];
    
    if ($itemsCount > 0) {
        setFlash('error', 'Cannot delete category with existing items');
        redirect(baseUrl('modules/categories/list.php'));
    }
    
    $category = Database::fetchOne("SELECT * FROM CATEGORIES WHERE id = ?", [$id]);
    
    Database::query("DELETE FROM CATEGORIES WHERE id = ?", [$id]);
    auditLog('Delete Category', 'CATEGORIES', $id, $category, null);
    
    setFlash('success', __('deleted_success'));
    redirect(baseUrl('modules/categories/list.php'));
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Get total count
$totalCount = Database::fetchOne("SELECT COUNT(*) as count FROM CATEGORIES")['count'];
$totalPages = ceil($totalCount / $perPage);

// Get categories
$categories = Database::fetchAll(
    "SELECT c.*, 
     (SELECT COUNT(*) FROM ITEMS WHERE categoryid = c.id) as items_count
     FROM CATEGORIES c
     ORDER BY c.created_at DESC
     LIMIT ? OFFSET ?",
    [$perPage, $offset]
);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-tags"></i> <?php echo __('categories'); ?>
            </h1>
            <p class="text-muted"><?php echo __('categories'); ?> <?php echo __('list'); ?></p>
        </div>
        <?php if (canCreate()): ?>
        <a href="<?php echo baseUrl('modules/categories/create.php'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> <?php echo __('add_category'); ?>
        </a>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th><?php echo __('category_name'); ?></th>
                            <th><?php echo __('description'); ?></th>
                            <th><?php echo __('items'); ?></th>
                            <th><?php echo __('created_at'); ?></th>
                            <th><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo e($category['id']); ?></td>
                                <td><strong><?php echo e($category['name']); ?></strong></td>
                                <td><?php echo e($category['description']); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo formatNumber($category['items_count']); ?> items
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if (canEdit()): ?>
                                        <a href="<?php echo baseUrl('modules/categories/edit.php?id=' . $category['id']); ?>" 
                                           class="btn btn-outline-primary" title="<?php echo __('edit'); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (canDelete()): ?>
                                        <button onclick="confirmDelete('<?php echo baseUrl('modules/categories/list.php?delete=' . $category['id']); ?>')" 
                                                class="btn btn-outline-danger" title="<?php echo __('delete'); ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p><?php echo __('no_data'); ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php echo pagination($page, $totalPages, baseUrl('modules/categories/list.php')); ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
