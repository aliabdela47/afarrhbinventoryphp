-- AfarRHB Inventory Management System
-- Database Schema - MySQL 8.0+

CREATE DATABASE IF NOT EXISTS afarrhb_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE afarrhb_inventory;

-- Users table
CREATE TABLE IF NOT EXISTS USERS (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'staff', 'viewer') NOT NULL DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Warehouses table
CREATE TABLE IF NOT EXISTS WAREHOUSES (
    warehouseid INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    contactperson VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS CATEGORIES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Employee List
CREATE TABLE IF NOT EXISTS EMPLIST (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    nameam VARCHAR(255),
    salary DECIMAL(10,2),
    taamagoli VARCHAR(100) UNIQUE,
    directorate VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_taamagoli (taamagoli)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Items (Model-19)
CREATE TABLE IF NOT EXISTS ITEMS (
    itemid INT AUTO_INCREMENT PRIMARY KEY,
    model19number VARCHAR(100),
    serialnumber VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    unit VARCHAR(50),
    quantity INT NOT NULL DEFAULT 0,
    categoryid INT,
    warehouseid INT,
    shelfcode VARCHAR(100),
    deliverername VARCHAR(255),
    sourceofitem ENUM('Purchase', 'Donation', 'Transfer', 'Other') NOT NULL,
    receiveddate DATE,
    registeredby INT,
    status ENUM('Available', 'Issued', 'Damaged', 'Lost', 'Disposed') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoryid) REFERENCES CATEGORIES(id) ON DELETE SET NULL,
    FOREIGN KEY (warehouseid) REFERENCES WAREHOUSES(warehouseid) ON DELETE SET NULL,
    FOREIGN KEY (registeredby) REFERENCES USERS(user_id) ON DELETE SET NULL,
    INDEX idx_serialnumber (serialnumber),
    INDEX idx_status (status),
    INDEX idx_categoryid (categoryid),
    INDEX idx_warehouseid (warehouseid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Item Documents
CREATE TABLE IF NOT EXISTS ITEMDOCUMENTS (
    documentid INT AUTO_INCREMENT PRIMARY KEY,
    itemid INT NOT NULL,
    documenttype ENUM('Model-22', 'Purchase Receipt', 'Donation Letter', 'Other') NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    uploadedby INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (itemid) REFERENCES ITEMS(itemid) ON DELETE CASCADE,
    FOREIGN KEY (uploadedby) REFERENCES USERS(user_id) ON DELETE SET NULL,
    INDEX idx_itemid (itemid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customers
CREATE TABLE IF NOT EXISTS CUSTOMERS (
    customerid INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('Internal', 'External') NOT NULL,
    empid INT,
    purpose TEXT,
    durationstart DATE,
    duration_end DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empid) REFERENCES EMPLIST(id) ON DELETE SET NULL,
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Requests (Model-20)
CREATE TABLE IF NOT EXISTS REQUESTS (
    requestid INT AUTO_INCREMENT PRIMARY KEY,
    model20number VARCHAR(100) UNIQUE NOT NULL,
    requestedby INT,
    directorate VARCHAR(255),
    requestdate DATE NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected', 'Fulfilled') NOT NULL DEFAULT 'Pending',
    createdby INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requestedby) REFERENCES EMPLIST(id) ON DELETE SET NULL,
    FOREIGN KEY (createdby) REFERENCES USERS(user_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_model20number (model20number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Request Items
CREATE TABLE IF NOT EXISTS REQUESTITEMS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requestid INT NOT NULL,
    itemid INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (requestid) REFERENCES REQUESTS(requestid) ON DELETE CASCADE,
    FOREIGN KEY (itemid) REFERENCES ITEMS(itemid) ON DELETE CASCADE,
    UNIQUE KEY unique_request_item (requestid, itemid),
    INDEX idx_requestid (requestid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Issuances (Model-22)
CREATE TABLE IF NOT EXISTS ISSUANCES (
    issuanceid INT AUTO_INCREMENT PRIMARY KEY,
    model22number VARCHAR(100) UNIQUE NOT NULL,
    customerid INT,
    issuedby INT,
    approvedby INT,
    issuancedate DATE NOT NULL,
    status ENUM('Draft', 'Issued', 'Returned', 'Cancelled') NOT NULL DEFAULT 'Draft',
    createdby INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customerid) REFERENCES CUSTOMERS(customerid) ON DELETE SET NULL,
    FOREIGN KEY (issuedby) REFERENCES USERS(user_id) ON DELETE SET NULL,
    FOREIGN KEY (approvedby) REFERENCES USERS(user_id) ON DELETE SET NULL,
    FOREIGN KEY (createdby) REFERENCES USERS(user_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_model22number (model22number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Issuance Items
CREATE TABLE IF NOT EXISTS ISSUANCEITEMS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issuanceid INT NOT NULL,
    itemid INT NOT NULL,
    quantity INT NOT NULL,
    returndate DATE,
    condition ENUM('Good', 'Damaged', 'Lost'),
    FOREIGN KEY (issuanceid) REFERENCES ISSUANCES(issuanceid) ON DELETE CASCADE,
    FOREIGN KEY (itemid) REFERENCES ITEMS(itemid) ON DELETE CASCADE,
    UNIQUE KEY unique_issuance_item (issuanceid, itemid),
    INDEX idx_issuanceid (issuanceid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Item Movements
CREATE TABLE IF NOT EXISTS ITEMMOVEMENTS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itemid INT NOT NULL,
    movementtype ENUM('IN', 'OUT', 'ADJUST') NOT NULL,
    quantity INT NOT NULL,
    performedby INT,
    movementdate DATE NOT NULL,
    createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (itemid) REFERENCES ITEMS(itemid) ON DELETE CASCADE,
    FOREIGN KEY (performedby) REFERENCES USERS(user_id) ON DELETE SET NULL,
    INDEX idx_itemid (itemid),
    INDEX idx_movementtype (movementtype)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Log
CREATE TABLE IF NOT EXISTS AUDITLOG (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userid INT,
    action VARCHAR(255) NOT NULL,
    affectedtable VARCHAR(100),
    affectedid INT,
    oldvalue JSON,
    newvalue JSON,
    ipaddress VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userid) REFERENCES USERS(user_id) ON DELETE SET NULL,
    INDEX idx_userid (userid),
    INDEX idx_affectedtable (affectedtable),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Data

-- Insert default admin user (password: Admin@123)
INSERT INTO USERS (name, email, password, role) VALUES
('System Administrator', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample categories
INSERT INTO CATEGORIES (name, description) VALUES
('Medical Equipment', 'Medical devices and equipment'),
('Office Supplies', 'General office supplies and stationery'),
('IT Equipment', 'Computers, printers, and IT accessories'),
('Furniture', 'Office furniture and fixtures'),
('Vehicles', 'Transportation vehicles');

-- Insert sample warehouses
INSERT INTO WAREHOUSES (name, location, contactperson) VALUES
('Main Warehouse', 'Semera, Building A', 'Ahmed Hassan'),
('Medical Storage', 'Semera, Building B', 'Fatuma Ali'),
('IT Storage', 'Semera, Building C', 'Mohammed Yusuf');

-- Insert sample employees
INSERT INTO EMPLIST (name, nameam, salary, taamagoli, directorate) VALUES
('Ahmed Ibrahim', 'አህመድ ኢብራሂም', 15000.00, 'EMP001', 'Health Services'),
('Fatuma Hassan', 'ፋጡማ ሃሰን', 12000.00, 'EMP002', 'Administration'),
('Mohammed Ali', 'መሐመድ አሊ', 13000.00, 'EMP003', 'Procurement');
