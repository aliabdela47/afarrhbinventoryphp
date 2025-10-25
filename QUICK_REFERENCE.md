# Quick Reference Guide

This guide provides quick code snippets and common patterns for developing features in the AfarRHB Inventory system.

## Table of Contents

- [Authentication](#authentication)
- [Database Operations](#database-operations)
- [Forms and CSRF](#forms-and-csrf)
- [File Uploads](#file-uploads)
- [Translations](#translations)
- [Ethiopian Calendar](#ethiopian-calendar)
- [Flash Messages](#flash-messages)
- [Pagination](#pagination)
- [Audit Logging](#audit-logging)

## Authentication

### Require Login

```php
require_once 'includes/auth.php';
requireAuth();
```

### Require Specific Role

```php
// Single role
requireRole('admin');

// Multiple roles
requireRole(['admin', 'manager']);
```

### Check User Role

```php
if (hasRole('admin')) {
    // Admin-only code
}

if (hasAnyRole(['admin', 'manager'])) {
    // Admin or manager code
}
```

### Get Current User Info

```php
$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['user_email'];
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['user_role'];

// Or use helpers
$name = getUserFullName();
$role = getUserRole();
```

## Database Operations

### Select Query

```php
try {
    $stmt = $pdo->prepare("SELECT * FROM ITEMS WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $items = [];
}
```

### Insert Query

```php
try {
    $stmt = $pdo->prepare("
        INSERT INTO ITEMS (item_code, name, category_id, warehouse_id, current_stock, unit_price)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $itemCode,
        $name,
        $categoryId,
        $warehouseId,
        $stock,
        $price
    ]);
    
    $itemId = $pdo->lastInsertId();
    
    // Log the action
    logAudit($pdo, 'CREATE', 'ITEMS', $itemId, null, [
        'item_code' => $itemCode,
        'name' => $name
    ]);
    
    flash('success', t('save_success'));
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    flash('error', t('save_failed'));
}
```

### Update Query

```php
try {
    // Get old values for audit
    $stmt = $pdo->prepare("SELECT * FROM ITEMS WHERE id = ?");
    $stmt->execute([$itemId]);
    $oldItem = $stmt->fetch();
    
    // Update
    $stmt = $pdo->prepare("
        UPDATE ITEMS 
        SET name = ?, current_stock = ?, unit_price = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$name, $stock, $price, $itemId]);
    
    // Log the action
    logAudit($pdo, 'UPDATE', 'ITEMS', $itemId, $oldItem, [
        'name' => $name,
        'current_stock' => $stock,
        'unit_price' => $price
    ]);
    
    flash('success', t('save_success'));
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    flash('error', t('save_failed'));
}
```

### Delete Query

```php
try {
    // Get item details for audit
    $stmt = $pdo->prepare("SELECT * FROM ITEMS WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
    
    // Delete
    $stmt = $pdo->prepare("DELETE FROM ITEMS WHERE id = ?");
    $stmt->execute([$itemId]);
    
    // Log the action
    logAudit($pdo, 'DELETE', 'ITEMS', $itemId, $item, null);
    
    flash('success', t('delete_success'));
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    flash('error', t('delete_failed'));
}
```

## Forms and CSRF

### Create Form with CSRF Token

```php
<form method="POST" action="process.php">
    <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
    
    <div class="mb-3">
        <label for="name" class="form-label"><?php echo e(t('name')); ?></label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    
    <button type="submit" class="btn btn-primary">
        <?php echo e(t('save')); ?>
    </button>
</form>
```

### Validate CSRF Token

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid request');
        redirect('form.php');
    }
    
    // Process form
    $name = trim($_POST['name'] ?? '');
    
    // Validation
    if (empty($name)) {
        flash('error', t('required_field'));
        redirect('form.php');
    }
    
    // Continue processing...
}
```

## File Uploads

### Upload File

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    // Validate file
    $errors = validateFileUpload($_FILES['document']);
    
    if (empty($errors)) {
        // Sanitize filename
        $originalName = $_FILES['document']['name'];
        $safeName = sanitizeFilename($originalName);
        
        // Move file
        $uploadPath = UPLOAD_PATH . '/' . $safeName;
        
        if (move_uploaded_file($_FILES['document']['tmp_name'], $uploadPath)) {
            // Save to database
            $stmt = $pdo->prepare("
                INSERT INTO ITEMDOCUMENTS (item_id, document_name, file_path, file_type, file_size, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $itemId,
                $originalName,
                $safeName,
                $_FILES['document']['type'],
                $_FILES['document']['size'],
                $_SESSION['user_id']
            ]);
            
            flash('success', 'File uploaded successfully');
        } else {
            flash('error', 'Failed to upload file');
        }
    } else {
        flash('error', implode(', ', $errors));
    }
}
```

## Translations

### Use Translation in PHP

```php
echo e(t('welcome_message'));
echo e(t('total_items'));
```

### Add New Translation

```php
// lang/en.php
'my_new_key' => 'My text in English',

// lang/am.php
'my_new_key' => 'የእኔ ጽሑፍ በአማርኛ',
```

## Ethiopian Calendar

### Convert Date (PHP)

```php
// Gregorian to Ethiopian
$gregDate = '2024-10-24';
list($year, $month, $day) = explode('-', $gregDate);
$ethDate = gregorianToEthiopian($year, $month, $day);
echo $ethDate['date']; // 2017-02-14

// Ethiopian to Gregorian
$gregDate = ethiopianToGregorian(2017, 2, 14);
echo $gregDate['date']; // 2024-10-24
```

### Convert Date (JavaScript)

```javascript
// Gregorian to Ethiopian
const gregDate = { year: 2024, month: 10, day: 24 };
const ethDate = gregorianToEthiopian(gregDate.year, gregDate.month, gregDate.day);
console.log(formatEthiopianDate(ethDate)); // "14 Tikimt 2017"

// Ethiopian to Gregorian
const ethDate = { year: 2017, month: 2, day: 14 };
const gregDate = ethiopianToGregorian(ethDate.year, ethDate.month, ethDate.day);
console.log(formatGregorianDate(gregDate)); // "24 October 2024"
```

## Flash Messages

### Set Flash Message

```php
flash('success', 'Operation completed successfully');
flash('error', 'An error occurred');
flash('warning', 'Please be careful');
flash('info', 'Information message');
```

### Display Flash Message (Automatic)

Flash messages are automatically displayed in the footer using SweetAlert2.

## Pagination

### Paginate Results

```php
// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = ITEMS_PER_PAGE;

// Count total items
$stmt = $pdo->query("SELECT COUNT(*) as count FROM ITEMS");
$totalItems = $stmt->fetch()['count'];

// Calculate pagination
$pagination = paginate($totalItems, $page, $perPage);

// Fetch items with limit and offset
$stmt = $pdo->prepare("SELECT * FROM ITEMS LIMIT ? OFFSET ?");
$stmt->execute([$pagination['per_page'], $pagination['offset']]);
$items = $stmt->fetchAll();
```

### Display Pagination Links

```php
<?php if ($pagination['total_pages'] > 1): ?>
<nav>
    <ul class="pagination">
        <?php if ($pagination['has_previous']): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>">
                Previous
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
                Next
            </a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
```

## Audit Logging

### Log Actions

```php
// Create
logAudit($pdo, 'CREATE', 'ITEMS', $itemId, null, $newData);

// Update
logAudit($pdo, 'UPDATE', 'ITEMS', $itemId, $oldData, $newData);

// Delete
logAudit($pdo, 'DELETE', 'ITEMS', $itemId, $oldData, null);

// Login/Logout
logAudit($pdo, 'LOGIN', 'USERS', $userId);
logAudit($pdo, 'LOGOUT', 'USERS', $userId);
```

## Common Patterns

### List Page Pattern

```php
<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();
$pageTitle = t('items') . ' - ' . APP_NAME;

// Fetch data
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = $_GET['search'] ?? '';

try {
    // Count
    $countQuery = "SELECT COUNT(*) as count FROM ITEMS WHERE 1=1";
    $params = [];
    if (!empty($search)) {
        $countQuery .= " AND (name LIKE ? OR item_code LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['count'];
    
    // Paginate
    $pagination = paginate($totalItems, $page);
    
    // Fetch
    $query = "SELECT * FROM ITEMS WHERE 1=1";
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR item_code LIKE ?)";
    }
    $query .= " ORDER BY name ASC LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($query);
    if (!empty($search)) {
        $stmt->execute(["%$search%", "%$search%", $pagination['per_page'], $pagination['offset']]);
    } else {
        $stmt->execute([$pagination['per_page'], $pagination['offset']]);
    }
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $items = [];
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <!-- Page content here -->
</main>

<?php include '../../includes/footer.php'; ?>
```

### Create/Edit Page Pattern

```php
<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireRole(['admin', 'manager']);
$pageTitle = t('add_item') . ' - ' . APP_NAME;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid request');
        redirect('list.php');
    }
    
    // Get and validate data
    $name = trim($_POST['name'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    
    if (empty($name)) {
        flash('error', t('required_field'));
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO ITEMS (name, category_id) VALUES (?, ?)");
            $stmt->execute([$name, $categoryId]);
            $itemId = $pdo->lastInsertId();
            
            logAudit($pdo, 'CREATE', 'ITEMS', $itemId, null, ['name' => $name]);
            
            flash('success', t('save_success'));
            redirect('list.php');
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
            flash('error', t('save_failed'));
        }
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="main-content" id="mainContent">
    <!-- Form here -->
</main>

<?php include '../../includes/footer.php'; ?>
```

---

For more details, see:
- `.github/copilot-instructions.md` - Complete coding guidelines
- `CONTRIBUTING.md` - Contribution guidelines
- `SETUP.md` - Installation instructions
