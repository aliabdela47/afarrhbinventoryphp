-- =====================================================
-- Migration 003: Issuance Items Attachments
-- =====================================================
-- This migration adds attachment support for issuances
-- Idempotent: Can be run multiple times safely

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Create ATTACHMENTS table for general use
CREATE TABLE IF NOT EXISTS ATTACHMENTS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL COMMENT 'Type of entity (issuance, request, vehicle, etc)',
    entity_id INT NOT NULL COMMENT 'ID of the related entity',
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    uploaded_by INT,
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES USERS(id) ON DELETE SET NULL,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_date (uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add recipient_type field to ISSUANCES if it doesn't exist
-- This allows issuances to be made to employees or external recipients
ALTER TABLE ISSUANCES 
ADD COLUMN IF NOT EXISTS recipient_type ENUM('internal', 'external') DEFAULT 'internal',
ADD COLUMN IF NOT EXISTS external_recipient_name VARCHAR(200),
ADD COLUMN IF NOT EXISTS external_recipient_org VARCHAR(200),
ADD COLUMN IF NOT EXISTS external_recipient_phone VARCHAR(20),
ADD INDEX IF NOT EXISTS idx_recipient_type (recipient_type);

COMMIT;
