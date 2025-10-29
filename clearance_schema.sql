-- Create Clearances Table
CREATE TABLE IF NOT EXISTS clearances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_no VARCHAR(100) NOT NULL,
    applicant VARCHAR(200) NOT NULL,
    address VARCHAR(255) NOT NULL,
    corporation_name VARCHAR(200) NULL,
    corporation_address VARCHAR(255) NULL,
    project_type VARCHAR(100) NOT NULL,
    area_location TEXT NOT NULL,
    right_over_land VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_application_no (application_no),
    INDEX idx_applicant (applicant),
    INDEX idx_right_over_land (right_over_land),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create uploads directory for clearances
-- Run this command in your server terminal:
-- mkdir -p uploads/clearances
-- chmod 755 uploads/clearances

CREATE TABLE IF NOT EXISTS clearances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_no VARCHAR(100) NOT NULL,
    applicant VARCHAR(200) NOT NULL,
    address VARCHAR(255) NOT NULL,
    corporation_name VARCHAR(200) NULL,
    corporation_address VARCHAR(255) NULL,
    project_type VARCHAR(100) NOT NULL,
    area_location TEXT NOT NULL,
    right_over_land VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_application_no (application_no),
    INDEX idx_applicant (applicant),
    INDEX idx_right_over_land (right_over_land),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;