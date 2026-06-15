CREATE TABLE employee_contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    tenant_id INT NOT NULL,
    type_contrat VARCHAR(50) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE,
    salaire_base DECIMAL(10, 2),
    statut VARCHAR(50) DEFAULT 'active',
    fichier_contrat VARCHAR(255),
    commentaire TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
