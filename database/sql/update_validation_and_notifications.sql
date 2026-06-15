-- Table pour le système de notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tenant_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info', -- info, success, warning, danger
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255) NULL, -- URL vers laquelle rediriger au clic
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Renommer rejection_reason en validation_comment pour plus de généralité
ALTER TABLE expenses CHANGE rejection_reason validation_comment TEXT NULL;

-- S'assurer que le statut "Modification demandée" est possible (via le varchar(50) existant)
