-- MPDO Database System Schema
-- Run this to create/update your database structure

CREATE DATABASE IF NOT EXISTS mpdo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mpdo_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'viewer') DEFAULT 'staff',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Documents Table
CREATE TABLE IF NOT EXISTS documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doc_type VARCHAR(50) NOT NULL,
    name VARCHAR(200) NULL,
    subject TEXT NULL,
    category VARCHAR(100) NULL,
    number VARCHAR(50) NULL,
    department VARCHAR(100) NULL,
    direction VARCHAR(20) NULL,
    file_path VARCHAR(255) NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_doc_type (doc_type),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at),
    FULLTEXT idx_search (name, subject, number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Logs Table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, fullname, password, role, status) 
VALUES ('admin', 'System Administrator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active')
ON DUPLICATE KEY UPDATE username=username;

-- Create uploads directory structure (execute these commands in your server)
-- mkdir -p uploads
-- chmod 755 uploads
-- mkdir -p logs
-- chmod 755 logs

-- Performance optimizations
-- Add indexes for better query performance
ALTER TABLE documents ADD INDEX idx_composite (doc_type, created_at DESC);
ALTER TABLE audit_logs ADD INDEX idx_composite (user_id, created_at DESC);

-- View for document statistics
CREATE OR REPLACE VIEW document_stats AS
SELECT 
    doc_type,
    COUNT(*) as total,
    COUNT(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) 
               AND YEAR(created_at) = YEAR(CURRENT_DATE()) THEN 1 END) as this_month,
    COUNT(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(CURRENT_DATE(), 1) THEN 1 END) as this_week
FROM documents
GROUP BY doc_type;

-- View for recent activity
CREATE OR REPLACE VIEW recent_activity AS
SELECT 
    a.id,
    a.action,
    a.details,
    a.ip_address,
    a.created_at,
    u.fullname as user_name,
    u.role as user_role
FROM audit_logs a
JOIN users u ON a.user_id = u.id
ORDER BY a.created_at DESC
LIMIT 100;