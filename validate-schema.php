<?php
/**
 * Database Schema Validator
 * Tests if the database schema from init.sql is correctly implemented
 */

require_once 'config/config.php';
require_once 'config/database.php';

echo "AfarRHB Inventory - Database Schema Validator\n";
echo "==============================================\n\n";

$requiredTables = [
    'USERS',
    'WAREHOUSES',
    'CATEGORIES',
    'EMPLIST',
    'ITEMS',
    'ITEMDOCUMENTS',
    'CUSTOMERS',
    'REQUESTS',
    'REQUESTITEMS',
    'ISSUANCES',
    'ISSUANCEITEMS',
    'ITEMMOVEMENTS',
    'AUDITLOG'
];

$errors = 0;
$success = 0;

try {
    // Check if database connection works
    echo "✓ Database connection successful\n\n";
    
    echo "Checking tables:\n";
    echo "----------------\n";
    
    foreach ($requiredTables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->rowCount();
            echo "✓ Table $table exists ($columns columns)\n";
            $success++;
        } catch (PDOException $e) {
            echo "✗ Table $table is missing\n";
            $errors++;
        }
    }
    
    echo "\nChecking seed data:\n";
    echo "-------------------\n";
    
    // Check admin user exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM USERS WHERE email = 'admin@example.com'");
    $count = $stmt->fetch()['count'];
    if ($count > 0) {
        echo "✓ Admin user exists\n";
        $success++;
    } else {
        echo "✗ Admin user not found\n";
        $errors++;
    }
    
    // Check categories exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM CATEGORIES");
    $count = $stmt->fetch()['count'];
    if ($count > 0) {
        echo "✓ Categories exist ($count)\n";
        $success++;
    } else {
        echo "✗ No categories found\n";
        $errors++;
    }
    
    // Check warehouses exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM WAREHOUSES");
    $count = $stmt->fetch()['count'];
    if ($count > 0) {
        echo "✓ Warehouses exist ($count)\n";
        $success++;
    } else {
        echo "✗ No warehouses found\n";
        $errors++;
    }
    
    // Check items exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM ITEMS");
    $count = $stmt->fetch()['count'];
    if ($count > 0) {
        echo "✓ Items exist ($count)\n";
        $success++;
    } else {
        echo "✗ No items found\n";
        $errors++;
    }
    
    echo "\nSummary:\n";
    echo "--------\n";
    echo "Passed: $success\n";
    echo "Failed: $errors\n";
    
    if ($errors === 0) {
        echo "\n✓ All checks passed! Database is properly configured.\n";
        exit(0);
    } else {
        echo "\n✗ Some checks failed. Please import init.sql\n";
        exit(1);
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. MySQL service is running\n";
    echo "2. Database credentials in config/database.php are correct\n";
    echo "3. Database 'afarrhb_inventory' exists\n";
    exit(1);
}
