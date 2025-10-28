-- =====================================================
-- Migration 005: Vehicle Tracking and API Keys
-- =====================================================
-- This migration creates tables for vehicle tracking and API authentication
-- Idempotent: Can be run multiple times safely

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Create VEHICLETRACKING table
CREATE TABLE IF NOT EXISTS VEHICLETRACKING (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    speed DECIMAL(6, 2) COMMENT 'Speed in km/h',
    heading DECIMAL(5, 2) COMMENT 'Heading in degrees',
    altitude DECIMAL(8, 2) COMMENT 'Altitude in meters',
    accuracy DECIMAL(6, 2) COMMENT 'GPS accuracy in meters',
    recorded_at TIMESTAMP NOT NULL,
    source ENUM('manual', 'api', 'device') DEFAULT 'manual',
    created_by INT COMMENT 'User ID for manual entries',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES VEHICLES(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_recorded_at (recorded_at),
    INDEX idx_source (source),
    INDEX idx_composite (vehicle_id, recorded_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create VEHICLEAPIKEYS table for API authentication
CREATE TABLE IF NOT EXISTS VEHICLEAPIKEYS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) NOT NULL,
    api_key VARCHAR(64) UNIQUE NOT NULL,
    vehicle_id INT COMMENT 'If key is vehicle-specific',
    user_id INT COMMENT 'User who generated the key',
    is_active TINYINT(1) DEFAULT 1,
    expires_at TIMESTAMP NULL,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES VEHICLES(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_key (api_key),
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a sample API key (key: TEST_API_KEY_12345_DO_NOT_USE_IN_PROD)
INSERT IGNORE INTO VEHICLEAPIKEYS (key_name, api_key, user_id, is_active) VALUES
('Test API Key', 'TEST_API_KEY_12345_DO_NOT_USE_IN_PROD', 1, 1);

-- Insert sample tracking data for demo vehicle
INSERT IGNORE INTO VEHICLETRACKING (vehicle_id, latitude, longitude, speed, recorded_at, source) VALUES
(1, 11.5751, 39.8302, 45.5, DATE_SUB(NOW(), INTERVAL 5 HOUR), 'api'),
(1, 11.5755, 39.8310, 48.2, DATE_SUB(NOW(), INTERVAL 4 HOUR), 'api'),
(1, 11.5760, 39.8320, 52.0, DATE_SUB(NOW(), INTERVAL 3 HOUR), 'api'),
(1, 11.5765, 39.8330, 50.5, DATE_SUB(NOW(), INTERVAL 2 HOUR), 'api'),
(1, 11.5770, 39.8340, 47.8, DATE_SUB(NOW(), INTERVAL 1 HOUR), 'api');

COMMIT;
