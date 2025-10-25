<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();

$pageTitle = 'New Request - ' . APP_NAME;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid request');
        redirect('create.php');
    }
    
    try {
        $requestNumber = generateUniqueCode('REQ', 3);
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO REQUESTS (
                request_number, requester_id, department, request_date, purpose, status, notes
            ) VALUES (?, ?, ?, ?, ?, 'pending', ?)
        ");
        
        $stmt->execute([
            $requestNumber,
            $_POST['requester_id'],
            $_POST['department'],
            $_POST['request_date'],
            $_POST['purpose'],
            $_POST['notes'] ?? null
        ]);
        
        $requestId = $pdo->lastInsertId();
        
        // Add request items
        if (!empty($_POST['items'])) {
            $stmt = $pdo->prepare("
                INSERT INTO REQUESTITEMS (request_id, item_id, requested_quantity, notes)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($_POST['items'] as $item) {
                $stmt->execute([
                    $requestId,
                    $item['item_id'],
                    $item['quantity'],
                    $item['notes'] ?? null
                ]);
            }
        }
        
        logAudit($pdo, 'CREATE', 'REQUESTS', $requestId, null, $_POST);
        
        $pdo->commit();
        
        flash('success', 'Request created successfully');
        redirect('view.php?id=' . $requestId);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Request create error: " . $e->getMessage());
        flash('error', 'Error creating request');
    }
}

try {
    $employees = $pdo->query("SELECT id, full_name, department FROM EMPLIST WHERE is_active = 1 ORDER BY full_name")->fetchAll();
    $items = $pdo->query("SELECT id, item_code, name, unit FROM ITEMS WHERE is_active = 1 ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $employees = [];
    $items = [];
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-plus-circle"></i> New Request</h2>
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
        
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="requestForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Requester <span class="text-danger">*</span></label>
                                    <select name="requester_id" class="form-select" required>
                                        <option value="">Select Requester</option>
                                        <?php foreach ($employees as $emp): ?>
                                        <option value="<?php echo $emp['id']; ?>">
                                            <?php echo e($emp['full_name'] . ' - ' . $emp['department']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Department</label>
                                    <input type="text" name="department" class="form-control">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Request Date <span class="text-danger">*</span></label>
                                <input type="date" name="request_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea name="purpose" class="form-control" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <h5 class="mb-3">Request Items</h5>
                            <div id="itemsList">
                                <div class="row mb-2 item-row">
                                    <div class="col-md-6">
                                        <select name="items[0][item_id]" class="form-select" required>
                                            <option value="">Select Item</option>
                                            <?php foreach ($items as $item): ?>
                                            <option value="<?php echo $item['id']; ?>">
                                                <?php echo e($item['name'] . ' (' . $item['item_code'] . ')'); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="Quantity" min="1" required>
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
                                    <i class="bi bi-save"></i> Create Request
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
            <div class="col-md-6">
                <select name="items[${itemIndex}][item_id]" class="form-select" required>
                    <option value="">Select Item</option>
                    <?php foreach ($items as $item): ?>
                    <option value="<?php echo $item['id']; ?>">
                        <?php echo e($item['name'] . ' (' . $item['item_code'] . ')'); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Quantity" min="1" required>
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
