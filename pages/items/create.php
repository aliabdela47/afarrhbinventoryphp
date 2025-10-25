<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();
requireRole(['admin', 'manager']);

$pageTitle = 'Add New Item - ' . APP_NAME;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid request');
        redirect('create.php');
    }
    
    try {
        $itemCode = generateUniqueCode('ITM', 3);
        
        $stmt = $pdo->prepare("
            INSERT INTO ITEMS (
                item_code, name, description, category_id, unit, reorder_level,
                warehouse_id, current_stock, unit_price, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([
            $itemCode,
            $_POST['name'],
            $_POST['description'],
            $_POST['category_id'] ?: null,
            $_POST['unit'],
            $_POST['reorder_level'],
            $_POST['warehouse_id'] ?: null,
            $_POST['current_stock'] ?? 0,
            $_POST['unit_price'] ?? 0
        ]);
        
        $itemId = $pdo->lastInsertId();
        
        logAudit($pdo, 'CREATE', 'ITEMS', $itemId, null, $_POST);
        
        flash('success', 'Item added successfully');
        redirect('view.php?id=' . $itemId);
        
    } catch (PDOException $e) {
        error_log("Item create error: " . $e->getMessage());
        flash('error', 'Error adding item');
    }
}

try {
    $categories = $pdo->query("SELECT id, name FROM CATEGORIES WHERE is_active = 1 ORDER BY name")->fetchAll();
    $warehouses = $pdo->query("SELECT id, name FROM WAREHOUSES WHERE is_active = 1 ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $warehouses = [];
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-plus-circle"></i> Add New Item</h2>
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
        
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Warehouse</label>
                                    <select name="warehouse_id" class="form-select">
                                        <option value="">Select Warehouse</option>
                                        <?php foreach ($warehouses as $wh): ?>
                                        <option value="<?php echo $wh['id']; ?>"><?php echo e($wh['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Unit <span class="text-danger">*</span></label>
                                    <input type="text" name="unit" class="form-control" value="piece" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Current Stock</label>
                                    <input type="number" name="current_stock" class="form-control" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reorder Level</label>
                                    <input type="number" name="reorder_level" class="form-control" value="10" min="0">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Unit Price (ETB)</label>
                                <input type="number" name="unit_price" class="form-control" step="0.01" value="0">
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Item
                                </button>
                                <a href="list.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
