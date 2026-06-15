-- Table des lignes principales du budget de mission
CREATE TABLE mission_budget_main_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mission_id INT NOT NULL,
    tenant_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    label VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Table des lignes de détail du budget de mission
CREATE TABLE mission_budget_detail_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    main_line_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    label VARCHAR(255) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (main_line_id) REFERENCES mission_budget_main_lines(id) ON DELETE CASCADE
);
