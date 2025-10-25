<?php
/**
 * Categories List
 * AfarRHB Inventory Management System
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// Require authentication
requireAuth();

$pageTitle = t('categories') . ' - ' . APP_NAME;

// Handle create/edit/delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'CSRF token validation failed');
        redirect('list.php');
    }
    
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create' && hasAnyRole(['admin', 'manager'])) {
            $stmt = $pdo->prepare("INSERT INTO CATEGORIES (name, description, parent_id) VALUES (?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['description'] ?? null,
                !empty($_POST['parent_id']) ? $_POST['parent_id'] : null
            ]);
            
            logAudit($pdo, 'CREATE', 'CATEGORIES', $pdo->lastInsertId(), null, $_POST);
            flash('success', t('save_success'));
            
        } elseif ($action === 'update' && hasAnyRole(['admin', 'manager'])) {
            $id = intval($_POST['id']);
            
            // Get old values
            $oldStmt = $pdo->prepare("SELECT * FROM CATEGORIES WHERE id = ?");
            $oldStmt->execute([$id]);
            $oldValues = $oldStmt->fetch();
            
            $stmt = $pdo->prepare("UPDATE CATEGORIES SET name = ?, description = ?, parent_id = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['description'] ?? null,
                !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
                $id
            ]);
            
            logAudit($pdo, 'UPDATE', 'CATEGORIES', $id, $oldValues, $_POST);
            flash('success', t('save_success'));
            
        } elseif ($action === 'delete' && hasRole('admin')) {
            $id = intval($_POST['id']);
            
            // Get old values
            $oldStmt = $pdo->prepare("SELECT * FROM CATEGORIES WHERE id = ?");
            $oldStmt->execute([$id]);
            $oldValues = $oldStmt->fetch();
            
            $stmt = $pdo->prepare("UPDATE CATEGORIES SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            logAudit($pdo, 'DELETE', 'CATEGORIES', $id, $oldValues, null);
            flash('success', t('delete_success'));
        }
    } catch (PDOException $e) {
        error_log("Category action error: " . $e->getMessage());
        flash('error', t('error_occurred'));
    }
    
    redirect('list.php');
}

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

try {
    // Count total records
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM CATEGORIES");
    $totalItems = $stmt->fetch()['count'];
    
    // Calculate pagination
    $pagination = paginate($totalItems, $page, $perPage);
    
    // Fetch categories
    $query = "
        SELECT c.*, p.name as parent_name
        FROM CATEGORIES c
        LEFT JOIN CATEGORIES p ON c.parent_id = p.id
        ORDER BY c.name ASC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$perPage, $pagination['offset']]);
    $categories = $stmt->fetchAll();
    
    // Get all categories for parent dropdown
    $allCategoriesStmt = $pdo->query("SELECT id, name FROM CATEGORIES WHERE is_active = 1 ORDER BY name");
    $allCategories = $allCategoriesStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Categories list error: " . $e->getMessage());
    $categories = [];
    $allCategories = [];
    $totalItems = 0;
    $pagination = paginate(0);
}

// Include header
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-grid-3x3-gap"></i> <?php echo e(t('categories')); ?></h2>
            <?php if (hasAnyRole(['admin', 'manager'])): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-circle"></i> <?php echo e(t('add')); ?> <?php echo e(t('category')); ?>
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Categories Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <p class="text-muted text-center py-4"><?php echo e(t('no_records')); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(t('name')); ?></th>
                                    <th><?php echo e(t('description')); ?></th>
                                    <th>Parent Category</th>
                                    <th><?php echo e(t('status')); ?></th>
                                    <th><?php echo e(t('created_at')); ?></th>
                                    <th><?php echo e(t('actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo e($category['name']); ?></td>
                                    <td><?php echo e($category['description']); ?></td>
                                    <td><?php echo e($category['parent_name'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($category['is_active']): ?>
                                            <span class="badge bg-success"><?php echo e(t('active')); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo e(t('inactive')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($category['created_at'], DISPLAY_DATE_FORMAT); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if (hasAnyRole(['admin', 'manager'])): ?>
                                            <button type="button" class="btn btn-warning" 
                                                    onclick="editCategory(<?php echo htmlspecialchars(json_encode($category), ENT_QUOTES, 'UTF-8'); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if (hasRole('admin')): ?>
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['has_previous']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>">
                                    <?php echo e(t('previous')); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>">
                                    <?php echo e(t('next')); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo e(t('add')); ?> <?php echo e(t('category')); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(t('name')); ?> *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(t('description')); ?></label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None</option>
                            <?php foreach ($allCategories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(t('cancel')); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo e(t('save')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo e(t('edit')); ?> <?php echo e(t('category')); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(t('name')); ?> *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(t('description')); ?></label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <select name="parent_id" id="edit_parent_id" class="form-select">
                            <option value="">None</option>
                            <?php foreach ($allCategories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(t('cancel')); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo e(t('save')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form method="POST" id="deleteForm" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function editCategory(category) {
    document.getElementById('edit_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_description').value = category.description || '';
    document.getElementById('edit_parent_id').value = category.parent_id || '';
    
    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}

function deleteCategory(id) {
    if (confirm('<?php echo e(t('delete_confirm')); ?>')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

<?php
$flash = getFlash();
if ($flash):
?>
Swal.fire({
    icon: '<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>',
    title: '<?php echo e($flash['message']); ?>',
    showConfirmButton: false,
    timer: 2000
});
<?php endif; ?>
</script>

<?php include '../../includes/footer.php'; ?>
