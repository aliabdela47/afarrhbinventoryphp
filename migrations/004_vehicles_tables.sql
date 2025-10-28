-- =====================================================
-- Migration 004: Vehicles Tables
-- =====================================================
-- This migration creates tables for vehicle management
-- Idempotent: Can be run multiple times safely

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Create VEHICLES table
CREATE TABLE IF NOT EXISTS VEHICLES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_code VARCHAR(50) UNIQUE NOT NULL,
    plate_number VARCHAR(50) UNIQUE NOT NULL,
    make VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT,
    color VARCHAR(50),
    vin VARCHAR(100),
    fuel_type ENUM('petrol', 'diesel', 'electric', 'hybrid') DEFAULT 'diesel',
    capacity INT COMMENT 'Passenger capacity',
    status ENUM('available', 'in-use', 'maintenance', 'retired') DEFAULT 'available',
    current_mileage INT DEFAULT 0,
    purchase_date DATE,
    purchase_price DECIMAL(12, 2),
    insurance_expiry DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (vehicle_code),
    INDEX idx_plate (plate_number),
    INDEX idx_status (status),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create VEHICLEGARAGES table
CREATE TABLE IF NOT EXISTS VEHICLEGARAGES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(50) UNIQUE,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    contract_file VARCHAR(500) COMMENT 'Path to contract document',
    contract_start_date DATE,
    contract_end_date DATE,
    agreed_rates JSON COMMENT 'Service rates as JSON',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create VEHICLESERVICES table
CREATE TABLE IF NOT EXISTS VEHICLESERVICES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    garage_id INT,
    service_type ENUM('maintenance', 'repair', 'inspection', 'other') NOT NULL,
    service_date DATE NOT NULL,
    description TEXT,
    parts_replaced JSON COMMENT 'Parts replaced as JSON array',
    cost DECIMAL(10, 2) DEFAULT 0.00,
    payment_method VARCHAR(50),
    next_service_date DATE,
    mileage_at_service INT,
    agreement_file VARCHAR(500) COMMENT 'Path to agreement/invoice',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES VEHICLES(id) ON DELETE CASCADE,
    FOREIGN KEY (garage_id) REFERENCES VEHICLEGARAGES(id) ON DELETE SET NULL,
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_garage (garage_id),
    INDEX idx_date (service_date),
    INDEX idx_type (service_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create VEHICLEASSIGNMENTS table
CREATE TABLE IF NOT EXISTS VEHICLEASSIGNMENTS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    assigned_to INT COMMENT 'Employee ID',
    driver_id INT COMMENT 'Driver employee ID if different from assigned_to',
    assignment_date DATE NOT NULL,
    return_date DATE,
    purpose TEXT,
    destination VARCHAR(255),
    start_mileage INT,
    end_mileage INT,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES VEHICLES(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES EMPLIST(id) ON DELETE SET NULL,
    FOREIGN KEY (driver_id) REFERENCES EMPLIST(id) ON DELETE SET NULL,
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_assigned (assigned_to),
    INDEX idx_driver (driver_id),
    INDEX idx_status (status),
    INDEX idx_date (assignment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample vehicles
INSERT IGNORE INTO VEHICLES (vehicle_code, plate_number, make, model, year, fuel_type, status) VALUES
('VH-001', 'AA-12345', 'Toyota', 'Land Cruiser', 2020, 'diesel', 'available'),
('VH-002', 'AA-12346', 'Toyota', 'Hilux', 2019, 'diesel', 'available'),
('VH-003', 'AA-12347', 'Nissan', 'Patrol', 2021, 'diesel', 'available'),
('VH-004', 'AA-12348', 'Ford', 'Ranger', 2018, 'diesel', 'maintenance'),
('VH-005', 'AA-12349', 'Isuzu', 'D-Max', 2020, 'diesel', 'available');

-- Insert sample garages
INSERT IGNORE INTO VEHICLEGARAGES (name, code, phone, address) VALUES
('Semera Auto Service', 'SAS-001', '+251911234567', 'Semera, Near Main Market'),
('Afar Motors', 'AFM-001', '+251911234568', 'Semera, Industrial Zone'),
('Quick Fix Garage', 'QFG-001', '+251911234569', 'Logia, Main Road');

COMMIT;
