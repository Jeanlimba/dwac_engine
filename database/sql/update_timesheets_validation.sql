ALTER TABLE timesheets 
ADD COLUMN status ENUM('soumis', 'valide', 'rejete') NOT NULL DEFAULT 'soumis',
ADD COLUMN rating TINYINT DEFAULT NULL,
ADD COLUMN rejection_reason TEXT DEFAULT NULL,
ADD COLUMN validated_by INT DEFAULT NULL,
ADD COLUMN validated_at TIMESTAMP NULL DEFAULT NULL,
ADD CONSTRAINT fk_timesheets_validated_by FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL;
