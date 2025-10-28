-- =====================================================
-- Migration 002: Directorates Table
-- =====================================================
-- This migration creates the directorates table for organizational structure
-- Idempotent: Can be run multiple times safely

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Create DIRECTORATES table if not exists
CREATE TABLE IF NOT EXISTS DIRECTORATES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(50) UNIQUE,
    description TEXT,
    director_id INT,
    parent_id INT,
    phone VARCHAR(20),
    email VARCHAR(100),
    location VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (director_id) REFERENCES EMPLIST(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES DIRECTORATES(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key to EMPLIST if column doesn't exist
ALTER TABLE EMPLIST 
ADD COLUMN IF NOT EXISTS directorate_id INT,
ADD INDEX IF NOT EXISTS idx_directorate (directorate_id),
ADD CONSTRAINT fk_emp_directorate FOREIGN KEY IF NOT EXISTS (directorate_id) 
    REFERENCES DIRECTORATES(id) ON DELETE SET NULL;

-- Insert sample directorates
INSERT IGNORE INTO DIRECTORATES (name, code, description) VALUES
('Health Services Directorate', 'HSD', 'Manages all health service delivery programs'),
('Disease Prevention and Control', 'DPC', 'Handles disease prevention and control programs'),
('Planning and Resource Mobilization', 'PRM', 'Planning, M&E, and resource mobilization'),
('Human Resource Development', 'HRD', 'HR management and capacity building'),
('Pharmaceutical and Medical Supply', 'PMS', 'Medical supply chain management'),
('Finance and Administration', 'FA', 'Financial and administrative services');

COMMIT;
