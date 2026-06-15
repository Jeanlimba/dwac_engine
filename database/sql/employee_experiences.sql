CREATE TABLE employee_experiences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    tenant_id INT NOT NULL,
    entreprise VARCHAR(150) NOT NULL,
    poste VARCHAR(150) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
