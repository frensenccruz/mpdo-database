-- Enhanced Documents Table with Better Structure
CREATE TABLE IF NOT EXISTS documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doc_type ENUM(
        'Certification',
        'Reclassification', 
        'Endorsement',
        'Resolution',
        'Ordinance',
        'Reprogramming',
        '20%',
        'Correspondence'
    ) NOT NULL,
    
    -- Common fields
    subject TEXT,
    file_path VARCHAR(255) NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Type-specific fields stored as JSON for flexibility
    metadata JSON,
    
    -- Indexes for performance
    INDEX idx_doc_type (doc_type),
    INDEX idx_created_at (created_at),
    INDEX idx_created_by (created_by),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example JSON structure for metadata field:
-- Certification: {"name": "Juan Dela Cruz", "subject": "..."}
-- Reclassification: {"name": "...", "subject": "...", "category": "Residential"}
-- Endorsement: {"name": "...", "subject": "...", "category": "Hosting"}
-- Resolution/Ordinance/Reprogramming/20%: {"number": "123", "subject": "..."}
-- Correspondence: {"direction": "Incoming", "name": "...", "department": "...", "subject": "..."}