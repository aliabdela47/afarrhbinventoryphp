<?php
/**
 * Database Smoke Test
 * Verifies database connection and core table structure
 */

// Configuration
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';

$errors = [];
$success = [];

echo "==========================================\n";
echo "Database Smoke Test\n";
echo "==========================================\n\n";

// Check if database config exists
$dbConfigFile = BASE_PATH . '/config/database.php';
if (!file_exists($dbConfigFile)) {
    echo "✗ Database config file not found: $dbConfigFile\n";
    echo "\nPlease create config/database.php with PDO connection.\n";
    echo "See README.md for configuration instructions.\n";
    exit(1);
}

echo "✓ Database config file found\n";

// Try to include and connect
try {
    require_once $dbConfigFile;
    
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("PDO instance not created in database.php");
    }
    
    echo "✓ Database connection established\n";
    $success[] = "Database connection";
    
    // Get database name
    $stmt = $pdo->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    echo "  Database: $dbName\n\n";
    
} catch (Exception $e) {
    echo "✗ Database connection failed\n";
    echo "  Error: " . $e->getMessage() . "\n\n";
    echo "Configuration Instructions:\n";
    echo "--------------------------\n";
    echo "1. Create database: CREATE DATABASE afarrhb_inventory;\n";
    echo "2. Update config/database.php with correct credentials\n";
    echo "3. Run migrations: mysql -u root -p afarrhb_inventory < migrations/001_create_core_tables.sql\n";
    echo "4. Run subsequent migrations in order\n";
    exit(1);
}

// Check core tables
echo "Checking core tables...\n\n";

$coreTables = [
    'USERS',
    'WAREHOUSES',
    'CATEGORIES',
    'EMPLIST',
    'ITEMS',
    'CUSTOMERS',
    'REQUESTS',
    'REQUESTITEMS',
    'ISSUANCES',
    'ISSUANCEITEMS',
    'ITEMMOVEMENTS',
    'ITEMDOCUMENTS',
    'AUDITLOG',
    'DIRECTORATES',
    'ATTACHMENTS',
    'VEHICLES',
    'VEHICLEGARAGES',
    'VEHICLESERVICES',
    'VEHICLEASSIGNMENTS',
    'VEHICLETRACKING',
    'VEHICLEAPIKEYS'
];

foreach ($coreTables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            // Get row count
            $countStmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $countStmt->fetchColumn();
            echo "✓ Table exists: $table ($count rows)\n";
            $success[] = "Table: $table";
        } else {
            $errors[] = "Table missing: $table";
            echo "✗ Table MISSING: $table\n";
        }
    } catch (PDOException $e) {
        $errors[] = "Error checking table $table: " . $e->getMessage();
        echo "✗ Error checking table $table\n";
    }
}

// Test basic queries
echo "\n\nTesting basic queries...\n\n";

try {
    // Test SELECT on USERS
    $stmt = $pdo->query("SELECT COUNT(*) FROM USERS");
    $userCount = $stmt->fetchColumn();
    echo "✓ SELECT query works (USERS table has $userCount users)\n";
    $success[] = "SELECT query";
    
    // Test admin user exists
    $stmt = $pdo->prepare("SELECT * FROM USERS WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✓ Admin user exists: {$admin['username']} ({$admin['email']})\n";
        $success[] = "Admin user";
    } else {
        $errors[] = "No admin user found. Run migrations to create default admin.";
        echo "✗ No admin user found\n";
    }
    
    // Test prepared statement
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ITEMS WHERE is_active = ?");
    $stmt->execute([1]);
    $activeItems = $stmt->fetchColumn();
    echo "✓ Prepared statement works ($activeItems active items)\n";
    $success[] = "Prepared statement";
    
    // Test JOIN
    $stmt = $pdo->query("
        SELECT i.name, c.name as category 
        FROM ITEMS i 
        LEFT JOIN CATEGORIES c ON i.category_id = c.id 
        LIMIT 1
    ");
    $result = $stmt->fetch();
    echo "✓ JOIN query works\n";
    $success[] = "JOIN query";
    
} catch (PDOException $e) {
    $errors[] = "Query test failed: " . $e->getMessage();
    echo "✗ Query test failed: " . $e->getMessage() . "\n";
}

// Test foreign keys
echo "\n\nTesting foreign key constraints...\n\n";

try {
    // Try to insert invalid foreign key (should fail)
    $stmt = $pdo->prepare("INSERT INTO ITEMS (item_code, name, category_id) VALUES (?, ?, ?)");
    $stmt->execute(['TEST-999', 'Test Item', 99999]);
    echo "⚠ Foreign key constraint not enforced (this may be intentional)\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'foreign key constraint') !== false || 
        strpos($e->getMessage(), 'Cannot add or update') !== false) {
        echo "✓ Foreign key constraints working\n";
        $success[] = "Foreign key constraints";
    } else {
        echo "⚠ Unexpected error: " . $e->getMessage() . "\n";
    }
}

// Summary
echo "\n\n==========================================\n";
echo "Test Summary\n";
echo "==========================================\n";
echo "✓ Passed: " . count($success) . "\n";
echo "✗ Failed: " . count($errors) . "\n\n";

if (count($errors) > 0) {
    echo "Errors:\n";
    foreach ($errors as $error) {
        echo "  ✗ $error\n";
    }
    echo "\n";
    echo "RESULT: FAILED\n";
    echo "\nMigration Instructions:\n";
    echo "----------------------\n";
    echo "Run migrations in order:\n";
    echo "1. mysql -u root -p afarrhb_inventory < migrations/001_create_core_tables.sql\n";
    echo "2. mysql -u root -p afarrhb_inventory < migrations/002_directorates.sql\n";
    echo "3. mysql -u root -p afarrhb_inventory < migrations/003_issuance_items_attachments.sql\n";
    echo "4. mysql -u root -p afarrhb_inventory < migrations/004_vehicles_tables.sql\n";
    echo "5. mysql -u root -p afarrhb_inventory < migrations/005_vehicle_tracking_apikeys.sql\n";
    exit(1);
}

echo "RESULT: PASSED\n";
echo "\nDatabase is properly configured and all core tables exist.\n";
echo "You can now use the application.\n";
exit(0);
