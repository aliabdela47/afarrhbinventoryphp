-- =====================================================
-- Migration 001: Create Core Tables
-- =====================================================
-- This migration creates the foundational tables for the inventory system
-- Idempotent: Can be run multiple times safely

-- Set character set
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Create USERS table if not exists
CREATE TABLE IF NOT EXISTS USERS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'manager', 'staff', 'viewer') DEFAULT 'staff',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create WAREHOUSES table if not exists
CREATE TABLE IF NOT EXISTS WAREHOUSES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    manager_id INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_active (is_active),
    INDEX idx_manager (manager_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create CATEGORIES table if not exists
CREATE TABLE IF NOT EXISTS CATEGORIES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES CATEGORIES(id) ON DELETE SET NULL,
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create EMPLIST table (Employee List)
CREATE TABLE IF NOT EXISTS EMPLIST (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    position VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    directorate_id INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (employee_code),
    INDEX idx_department (department),
    INDEX idx_active (is_active),
    INDEX idx_directorate (directorate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ITEMS table if not exists
CREATE TABLE IF NOT EXISTS ITEMS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT,
    unit VARCHAR(20) DEFAULT 'piece',
    reorder_level INT DEFAULT 10,
    warehouse_id INT,
    current_stock INT DEFAULT 0,
    unit_price DECIMAL(10, 2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES CATEGORIES(id) ON DELETE SET NULL,
    FOREIGN KEY (warehouse_id) REFERENCES WAREHOUSES(id) ON DELETE SET NULL,
    INDEX idx_code (item_code),
    INDEX idx_category (category_id),
    INDEX idx_warehouse (warehouse_id),
    INDEX idx_stock (current_stock),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create CUSTOMERS table if not exists
CREATE TABLE IF NOT EXISTS CUSTOMERS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (customer_code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create REQUESTS table if not exists
CREATE TABLE IF NOT EXISTS REQUESTS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(50) UNIQUE NOT NULL,
    requester_id INT,
    department VARCHAR(100),
    request_date DATE NOT NULL,
    purpose TEXT,
    status ENUM('pending', 'approved', 'rejected', 'fulfilled') DEFAULT 'pending',
    approved_by INT,
    approved_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requester_id) REFERENCES EMPLIST(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_number (request_number),
    INDEX idx_status (status),
    INDEX idx_date (request_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create REQUESTITEMS table if not exists
CREATE TABLE IF NOT EXISTS REQUESTITEMS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    item_id INT NOT NULL,
    requested_quantity INT NOT NULL,
    approved_quantity INT DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (request_id) REFERENCES REQUESTS(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES ITEMS(id) ON DELETE CASCADE,
    INDEX idx_request (request_id),
    INDEX idx_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ISSUANCES table if not exists
CREATE TABLE IF NOT EXISTS ISSUANCES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issuance_number VARCHAR(50) UNIQUE NOT NULL,
    request_id INT,
    issued_to INT,
    issued_by INT NOT NULL,
    issue_date DATE NOT NULL,
    receiver_signature VARCHAR(255),
    issuer_signature VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES REQUESTS(id) ON DELETE SET NULL,
    FOREIGN KEY (issued_to) REFERENCES EMPLIST(id) ON DELETE SET NULL,
    FOREIGN KEY (issued_by) REFERENCES USERS(id) ON DELETE RESTRICT,
    INDEX idx_number (issuance_number),
    INDEX idx_date (issue_date),
    INDEX idx_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ISSUANCEITEMS table if not exists
CREATE TABLE IF NOT EXISTS ISSUANCEITEMS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issuance_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) DEFAULT 0.00,
    notes TEXT,
    FOREIGN KEY (issuance_id) REFERENCES ISSUANCES(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES ITEMS(id) ON DELETE CASCADE,
    INDEX idx_issuance (issuance_id),
    INDEX idx_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ITEMMOVEMENTS table if not exists
CREATE TABLE IF NOT EXISTS ITEMMOVEMENTS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    movement_type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    warehouse_id INT,
    moved_by INT,
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES ITEMS(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES WAREHOUSES(id) ON DELETE SET NULL,
    FOREIGN KEY (moved_by) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_item (item_id),
    INDEX idx_type (movement_type),
    INDEX idx_date (movement_date),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ITEMDOCUMENTS table if not exists
CREATE TABLE IF NOT EXISTS ITEMDOCUMENTS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES ITEMS(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create AUDITLOG table if not exists
CREATE TABLE IF NOT EXISTS AUDITLOG (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_value JSON,
    new_value JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_table (table_name),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add seed data for admin user (password: Admin@123)
INSERT IGNORE INTO USERS (username, email, password_hash, full_name, role) 
VALUES ('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- Add sample categories
INSERT IGNORE INTO CATEGORIES (name, description) VALUES
('Medical Supplies', 'Medical equipment and supplies'),
('Office Supplies', 'Office stationery and equipment'),
('Equipment', 'General equipment'),
('Cleaning Supplies', 'Cleaning materials and tools'),
('IT Equipment', 'Computer and networking equipment'),
('Furniture', 'Office and medical furniture'),
('Safety Equipment', 'Safety gear and equipment'),
('Others', 'Miscellaneous items');

-- Add sample warehouse
INSERT IGNORE INTO WAREHOUSES (name, location) VALUES
('Main Warehouse', 'Afar Regional Health Bureau HQ'),
('Regional Office', 'Semera Office Building');

COMMIT;
