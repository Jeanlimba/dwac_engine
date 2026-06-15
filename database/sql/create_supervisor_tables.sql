-- Table des partenaires
CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(255),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Table des missions
CREATE TABLE missions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    partner_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    date_start DATE NOT NULL,
    date_end DATE NOT NULL,
    estimated_revenue DECIMAL(15, 2) DEFAULT 0.00,
    status VARCHAR(50) DEFAULT 'En attente', -- En attente, En cours, Terminée, Annulée
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE
);

-- Table des membres de l'équipe de mission
CREATE TABLE mission_team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mission_id INT NOT NULL,
    employee_id INT NOT NULL,
    role_in_mission VARCHAR(100) NOT NULL, -- ex: Auditeur, Team Leader
    hourly_rate DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- Table des dépenses
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    mission_id INT DEFAULT NULL, -- NULL si dépense administrative courante
    employee_id INT NOT NULL, -- Qui a effectué/déclaré la dépense
    category VARCHAR(100) NOT NULL, -- Administration, Transport, Logement, etc.
    amount DECIMAL(15, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    description TEXT,
    expense_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'En attente', -- En attente, Approuvée, Rejetée
    receipt_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE SET NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);
