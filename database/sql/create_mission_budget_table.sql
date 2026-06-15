-- Table des rubriques budgétaires de mission
CREATE TABLE mission_budget_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mission_id INT NOT NULL,
    tenant_id INT NOT NULL,
    label VARCHAR(255) NOT NULL, -- Libellé
    unit VARCHAR(50), -- Unité (ex: Jours, Km, Forfait)
    budget_line VARCHAR(100), -- Ligne budgétaire (ex: 1.1 Transport)
    unit_amount DECIMAL(15, 2) DEFAULT 0.00, -- Equivalent (Montant par défaut)
    quantity INT DEFAULT 1, -- Optionnel: pour calculer le budget total
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
