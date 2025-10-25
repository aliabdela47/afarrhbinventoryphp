-- AfarRHB Inventory Database Initialization Script
-- MySQL 5.7+ / MariaDB 10.2+
-- Character Set: UTF-8 (utf8mb4)

-- Drop existing database if exists
DROP DATABASE IF EXISTS afarrhb_inventory;

-- Create database
CREATE DATABASE afarrhb_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE afarrhb_inventory;

-- =====================================================
-- USERS TABLE
-- =====================================================
CREATE TABLE USERS (
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
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- WAREHOUSES TABLE
-- =====================================================
CREATE TABLE WAREHOUSES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    manager_id INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CATEGORIES TABLE
-- =====================================================
CREATE TABLE CATEGORIES (
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

-- =====================================================
-- EMPLIST TABLE (Employee List)
-- =====================================================
CREATE TABLE EMPLIST (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    position VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (employee_code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ITEMS TABLE
-- =====================================================
CREATE TABLE ITEMS (
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

-- =====================================================
-- ITEMDOCUMENTS TABLE
-- =====================================================
CREATE TABLE ITEMDOCUMENTS (
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

-- =====================================================
-- CUSTOMERS TABLE
-- =====================================================
CREATE TABLE CUSTOMERS (
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

-- =====================================================
-- REQUESTS TABLE
-- =====================================================
CREATE TABLE REQUESTS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(50) UNIQUE NOT NULL,
    requester_id INT,
    department VARCHAR(100),
    request_date DATE NOT NULL,
    purpose TEXT,
    status ENUM('pending', 'approved', 'rejected', 'issued') DEFAULT 'pending',
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

-- =====================================================
-- REQUESTITEMS TABLE
-- =====================================================
CREATE TABLE REQUESTITEMS (
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

-- =====================================================
-- ISSUANCES TABLE
-- =====================================================
CREATE TABLE ISSUANCES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issuance_number VARCHAR(50) UNIQUE NOT NULL,
    request_id INT,
    issued_to INT,
    issued_by INT,
    issue_date DATE NOT NULL,
    receiver_signature VARCHAR(255),
    issuer_signature VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES REQUESTS(id) ON DELETE SET NULL,
    FOREIGN KEY (issued_to) REFERENCES EMPLIST(id) ON DELETE SET NULL,
    FOREIGN KEY (issued_by) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_number (issuance_number),
    INDEX idx_date (issue_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ISSUANCEITEMS TABLE
-- =====================================================
CREATE TABLE ISSUANCEITEMS (
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

-- =====================================================
-- ITEMMOVEMENTS TABLE
-- =====================================================
CREATE TABLE ITEMMOVEMENTS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    movement_type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    warehouse_id INT,
    moved_by INT,
    movement_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES ITEMS(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES WAREHOUSES(id) ON DELETE SET NULL,
    FOREIGN KEY (moved_by) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_item (item_id),
    INDEX idx_type (movement_type),
    INDEX idx_date (movement_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- AUDITLOG TABLE
-- =====================================================
CREATE TABLE AUDITLOG (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_value TEXT,
    new_value TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_table (table_name),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SEED DATA
-- =====================================================

-- Insert admin user (password: Admin@123)
INSERT INTO USERS (username, email, password_hash, full_name, role, is_active) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 1),
('manager', 'manager@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Warehouse Manager', 'manager', 1),
('staff', 'staff@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Member', 'staff', 1),
('viewer', 'viewer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Report Viewer', 'viewer', 1);

-- Insert warehouses
INSERT INTO WAREHOUSES (name, location, manager_id, is_active) VALUES
('Main Warehouse', 'Semera, Afar Regional State', 2, 1),
('Regional Office Store', 'Semera Central Office', 2, 1),
('Mobile Medical Unit Storage', 'Field Unit Base', 2, 1);

-- Insert categories
INSERT INTO CATEGORIES (name, description, parent_id, is_active) VALUES
('Medical Supplies', 'Medical equipment and consumables', NULL, 1),
('Office Supplies', 'Office stationery and equipment', NULL, 1),
('Equipment', 'Medical and office equipment', NULL, 1),
('Pharmaceuticals', 'Medicine and drugs', 1, 1),
('Diagnostics', 'Diagnostic tools and kits', 1, 1),
('Stationery', 'Paper, pens, and office supplies', 2, 1),
('Electronics', 'Computers and electronics', 2, 1),
('Medical Devices', 'Medical equipment and devices', 3, 1),
('Furniture', 'Office and medical furniture', 3, 1);

-- Insert employees
INSERT INTO EMPLIST (employee_code, full_name, department, position, phone, email, is_active) VALUES
('EMP001', 'Ahmed Mohammed', 'Health Services', 'Health Officer', '+251-911-123456', 'ahmed.m@afarrhb.gov.et', 1),
('EMP002', 'Fatima Ali', 'Administration', 'Administrative Assistant', '+251-911-234567', 'fatima.a@afarrhb.gov.et', 1),
('EMP003', 'Hassan Ibrahim', 'Logistics', 'Supply Chain Manager', '+251-911-345678', 'hassan.i@afarrhb.gov.et', 1),
('EMP004', 'Amina Yusuf', 'Pharmacy', 'Pharmacist', '+251-911-456789', 'amina.y@afarrhb.gov.et', 1),
('EMP005', 'Omar Abdullahi', 'Medical Services', 'Medical Doctor', '+251-911-567890', 'omar.a@afarrhb.gov.et', 1);

-- Insert sample items
INSERT INTO ITEMS (item_code, name, description, category_id, unit, reorder_level, warehouse_id, current_stock, unit_price, is_active) VALUES
('ITM001', 'Disposable Syringes 5ml', 'Sterile disposable syringes', 4, 'box', 50, 1, 120, 25.50, 1),
('ITM002', 'Medical Gloves (Large)', 'Latex examination gloves', 4, 'box', 100, 1, 250, 15.00, 1),
('ITM003', 'Digital Thermometer', 'Electronic body thermometer', 5, 'piece', 20, 1, 45, 85.00, 1),
('ITM004', 'Blood Pressure Monitor', 'Digital BP monitor', 5, 'piece', 10, 1, 15, 450.00, 1),
('ITM005', 'Paracetamol 500mg', 'Pain relief tablets', 4, 'box', 200, 1, 500, 12.00, 1),
('ITM006', 'A4 Paper Ream', 'White copy paper 500 sheets', 6, 'ream', 50, 2, 80, 95.00, 1),
('ITM007', 'Ballpoint Pen Blue', 'Blue ink ballpoint pen', 6, 'box', 30, 2, 45, 18.00, 1),
('ITM008', 'Office Chair', 'Ergonomic office chair', 9, 'piece', 5, 2, 8, 1200.00, 1),
('ITM009', 'Stethoscope', 'Professional stethoscope', 8, 'piece', 5, 1, 12, 850.00, 1),
('ITM010', 'Hand Sanitizer 500ml', 'Alcohol-based hand sanitizer', 4, 'bottle', 100, 1, 200, 35.00, 1);

-- Insert customers
INSERT INTO CUSTOMERS (customer_code, name, contact_person, phone, email, address, is_active) VALUES
('CUST001', 'Semera Health Center', 'Dr. Ahmed Hassan', '+251-911-111111', 'semera.hc@health.gov.et', 'Semera, Afar', 1),
('CUST002', 'Dubti District Hospital', 'Dr. Fatima Osman', '+251-911-222222', 'dubti.hospital@health.gov.et', 'Dubti, Afar', 1),
('CUST003', 'Asayita Health Post', 'Nurse Ibrahim Ali', '+251-911-333333', 'asayita.hp@health.gov.et', 'Asayita, Afar', 1),
('CUST004', 'Mobile Health Unit 1', 'Dr. Amina Yusuf', '+251-911-444444', 'mhu1@afarrhb.gov.et', 'Mobile Unit', 1);

-- Insert sample request
INSERT INTO REQUESTS (request_number, requester_id, department, request_date, purpose, status, notes) VALUES
('REQ-2024-001', 1, 'Health Services', CURDATE(), 'Monthly medical supplies for health centers', 'pending', 'Urgent requirement for field operations');

-- Insert request items
INSERT INTO REQUESTITEMS (request_id, item_id, requested_quantity, approved_quantity) VALUES
(1, 1, 50, 0),
(1, 2, 100, 0),
(1, 5, 200, 0),
(1, 10, 50, 0);

-- Insert initial stock movements
INSERT INTO ITEMMOVEMENTS (item_id, movement_type, quantity, reference_type, reference_id, warehouse_id, moved_by, movement_date, notes) VALUES
(1, 'IN', 120, 'INITIAL_STOCK', NULL, 1, 1, CURDATE(), 'Initial stock entry'),
(2, 'IN', 250, 'INITIAL_STOCK', NULL, 1, 1, CURDATE(), 'Initial stock entry'),
(3, 'IN', 45, 'INITIAL_STOCK', NULL, 1, 1, CURDATE(), 'Initial stock entry'),
(4, 'IN', 15, 'INITIAL_STOCK', NULL, 1, 1, CURDATE(), 'Initial stock entry'),
(5, 'IN', 500, 'INITIAL_STOCK', NULL, 1, 1, CURDATE(), 'Initial stock entry'),
(6, 'IN', 80, 'INITIAL_STOCK', NULL, 2, 1, CURDATE(), 'Initial stock entry'),
(7, 'IN', 45, 'INITIAL_STOCK', NULL, 2, 1, CURDATE(), 'Initial stock entry'),
(8, 'IN', 8, 'INITIAL_STOCK', NULL, 2, 1, CURDATE(), 'Initial stock entry'),
(9, 'IN', 12, 'INITIAL_STOCK', NULL, 1, 1, CURDATE(), 'Initial stock entry'),
(10, 'IN', 200, 'INITIAL_STOCK', NULL, 1, 1, CURDATE(), 'Initial stock entry');

-- =====================================================
-- COMPLETED
-- =====================================================
SELECT 'Database initialization completed successfully!' AS Status;
