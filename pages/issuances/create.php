<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();
requireRole(['admin', 'manager']);

$pageTitle = 'New Issuance - ' . APP_NAME;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid request');
        redirect('create.php');
    }
    
    try {
        $issuanceNumber = generateUniqueCode('ISS', 3);
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO ISSUANCES (
                issuance_number, request_id, issued_to, issued_by, issue_date, notes
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $issuanceNumber,
            $_POST['request_id'] ?: null,
            $_POST['issued_to'],
            $_SESSION['user_id'],
            $_POST['issue_date'],
            $_POST['notes'] ?? null
        ]);
        
        $issuanceId = $pdo->lastInsertId();
        
        // Add issuance items and update stock
        if (!empty($_POST['items'])) {
            $stmt = $pdo->prepare("
                INSERT INTO ISSUANCEITEMS (issuance_id, item_id, quantity, unit_price, notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $moveStmt = $pdo->prepare("
                INSERT INTO ITEMMOVEMENTS (item_id, movement_type, quantity, reference_type, reference_id, moved_by, movement_date)
                VALUES (?, 'OUT', ?, 'ISSUANCE', ?, ?, ?)
            ");
            
            $stockStmt = $pdo->prepare("UPDATE ITEMS SET current_stock = current_stock - ? WHERE id = ?");
            
            foreach ($_POST['items'] as $item) {
                $stmt->execute([
                    $issuanceId,
                    $item['item_id'],
                    $item['quantity'],
                    $item['unit_price'] ?? 0,
                    $item['notes'] ?? null
                ]);
                
                $moveStmt->execute([
                    $item['item_id'],
                    $item['quantity'],
                    $issuanceId,
                    $_SESSION['user_id'],
                    $_POST['issue_date']
                ]);
                
                $stockStmt->execute([$item['quantity'], $item['item_id']]);
            }
        }
        
        logAudit($pdo, 'CREATE', 'ISSUANCES', $issuanceId, null, $_POST);
        
        $pdo->commit();
        
        flash('success', 'Issuance created successfully');
        redirect('view.php?id=' . $issuanceId);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Issuance create error: " . $e->getMessage());
        flash('error', 'Error creating issuance');
    }
}

try {
    $employees = $pdo->query("SELECT id, full_name, department FROM EMPLIST WHERE is_active = 1 ORDER BY full_name")->fetchAll();
    $items = $pdo->query("SELECT id, item_code, name, unit, unit_price, current_stock FROM ITEMS WHERE is_active = 1 AND current_stock > 0 ORDER BY name")->fetchAll();
    $requests = $pdo->query("SELECT id, request_number FROM REQUESTS WHERE status = 'approved' ORDER BY request_date DESC")->fetchAll();
} catch (PDOException $e) {
    $employees = [];
    $items = [];
    $requests = [];
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-plus-circle"></i> New Issuance</h2>
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
        
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Related Request (Optional)</label>
                                    <select name="request_id" class="form-select">
                                        <option value="">None</option>
                                        <?php foreach ($requests as $req): ?>
                                        <option value="<?php echo $req['id']; ?>"><?php echo e($req['request_number']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Issued To <span class="text-danger">*</span></label>
                                    <select name="issued_to" class="form-select" required>
                                        <option value="">Select Employee</option>
                                        <?php foreach ($employees as $emp): ?>
                                        <option value="<?php echo $emp['id']; ?>">
                                            <?php echo e($emp['full_name'] . ' - ' . $emp['department']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                                <input type="date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <h5 class="mb-3">Items to Issue</h5>
                            <div id="itemsList">
                                <div class="row mb-2 item-row">
                                    <div class="col-md-5">
                                        <select name="items[0][item_id]" class="form-select" required>
                                            <option value="">Select Item</option>
                                            <?php foreach ($items as $item): ?>
                                            <option value="<?php echo $item['id']; ?>" data-price="<?php echo $item['unit_price']; ?>" data-stock="<?php echo $item['current_stock']; ?>">
                                                <?php echo e($item['name'] . ' (Stock: ' . $item['current_stock'] . ')'); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="items[0][unit_price]" class="form-control" placeholder="Price" step="0.01">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="items[0][notes]" class="form-control" placeholder="Notes">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="addItemRow()">
                                <i class="bi bi-plus"></i> Add Item
                            </button>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Create Issuance
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

<script>
let itemIndex = 1;
function addItemRow() {
    const html = `
        <div class="row mb-2 item-row">
            <div class="col-md-5">
                <select name="items[${itemIndex}][item_id]" class="form-select" required>
                    <option value="">Select Item</option>
                    <?php foreach ($items as $item): ?>
                    <option value="<?php echo $item['id']; ?>" data-price="<?php echo $item['unit_price']; ?>">
                        <?php echo e($item['name'] . ' (Stock: ' . $item['current_stock'] . ')'); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Qty" min="1" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control" placeholder="Price" step="0.01">
            </div>
            <div class="col-md-3">
                <input type="text" name="items[${itemIndex}][notes]" class="form-control" placeholder="Notes">
            </div>
        </div>
    `;
    document.getElementById('itemsList').insertAdjacentHTML('beforeend', html);
    itemIndex++;
}
</script>

<?php include '../../includes/footer.php'; ?>
