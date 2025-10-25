<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();

$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($itemId === 0) {
    flash('error', 'Invalid item ID');
    redirect('list.php');
}

try {
    $stmt = $pdo->prepare("
        SELECT i.*, c.name as category_name, w.name as warehouse_name
        FROM ITEMS i
        LEFT JOIN CATEGORIES c ON i.category_id = c.id
        LEFT JOIN WAREHOUSES w ON i.warehouse_id = w.id
        WHERE i.id = ?
    ");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        flash('error', 'Item not found');
        redirect('list.php');
    }
    
    // Get documents
    $stmt = $pdo->prepare("SELECT * FROM ITEMDOCUMENTS WHERE item_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$itemId]);
    $documents = $stmt->fetchAll();
    
    // Get recent movements
    $stmt = $pdo->prepare("
        SELECT im.*, u.full_name as moved_by_name
        FROM ITEMMOVEMENTS im
        LEFT JOIN USERS u ON im.moved_by = u.id
        WHERE im.item_id = ?
        ORDER BY im.movement_date DESC
        LIMIT 10
    ");
    $stmt->execute([$itemId]);
    $movements = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Item view error: " . $e->getMessage());
    flash('error', 'Error loading item');
    redirect('list.php');
}

$pageTitle = $item['name'] . ' - Items - ' . APP_NAME;

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-box-seam"></i> Item Details</h2>
            <div>
                <a href="list.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <?php if (hasAnyRole(['admin', 'manager'])): ?>
                <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Item Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Item Code</label>
                                <p class="mb-0"><strong><?php echo e($item['item_code']); ?></strong></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Name</label>
                                <p class="mb-0"><strong><?php echo e($item['name']); ?></strong></p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-muted small">Description</label>
                                <p class="mb-0"><?php echo nl2br(e($item['description'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Category</label>
                                <p class="mb-0"><?php echo e($item['category_name'] ?? '-'); ?></p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Unit</label>
                                <p class="mb-0"><?php echo e($item['unit']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Warehouse</label>
                                <p class="mb-0"><?php echo e($item['warehouse_name'] ?? '-'); ?></p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Current Stock</label>
                                <p class="mb-0">
                                    <strong class="<?php echo $item['current_stock'] <= $item['reorder_level'] ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo number_format($item['current_stock']); ?> <?php echo e($item['unit']); ?>
                                    </strong>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Reorder Level</label>
                                <p class="mb-0"><?php echo number_format($item['reorder_level']); ?> <?php echo e($item['unit']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Unit Price</label>
                                <p class="mb-0"><?php echo formatCurrency($item['unit_price']); ?></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted small">Status</label>
                                <p class="mb-0">
                                    <span class="badge <?php echo $item['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Created At</label>
                                <p class="mb-0"><?php echo formatDate($item['created_at'], DISPLAY_DATETIME_FORMAT); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Recent Movements</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($movements)): ?>
                            <p class="text-muted text-center py-4">No movements found</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Quantity</th>
                                            <th>Reference</th>
                                            <th>Moved By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($movements as $movement): ?>
                                        <tr>
                                            <td><?php echo formatDate($movement['movement_date'], DISPLAY_DATE_FORMAT); ?></td>
                                            <td>
                                                <span class="badge <?php echo $movement['movement_type'] === 'IN' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo $movement['movement_type']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($movement['quantity']); ?> <?php echo e($item['unit']); ?></td>
                                            <td><?php echo e($movement['reference_type']); ?></td>
                                            <td><?php echo e($movement['moved_by_name'] ?? 'System'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <?php if ($item['current_stock'] <= $item['reorder_level']): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Low Stock Alert!</strong><br>
                    Current stock is at or below reorder level.
                </div>
                <?php endif; ?>
                
                <?php if (!empty($documents)): ?>
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Documents</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($documents as $doc): ?>
                        <div class="mb-2">
                            <a href="<?php echo e($doc['file_path']); ?>" target="_blank">
                                <i class="bi bi-file-earmark"></i> <?php echo e($doc['document_name']); ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
