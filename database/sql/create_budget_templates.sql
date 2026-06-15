-- Table des modèles de budget
CREATE TABLE IF NOT EXISTS budget_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des lignes principales des modèles
CREATE TABLE IF NOT EXISTS budget_template_main_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    label VARCHAR(255) NOT NULL,
    FOREIGN KEY (template_id) REFERENCES budget_templates(id) ON DELETE CASCADE
);

-- Table des lignes de détail des modèles
CREATE TABLE IF NOT EXISTS budget_template_detail_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    main_line_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    label VARCHAR(255) NOT NULL,
    amount DECIMAL(15, 2) DEFAULT 0.00,
    FOREIGN KEY (main_line_id) REFERENCES budget_template_main_lines(id) ON DELETE CASCADE
);
