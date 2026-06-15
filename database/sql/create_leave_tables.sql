CREATE TABLE leave_types (
    id INT(11) NOT NULL AUTO_INCREMENT,
    tenant_id INT(11) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_paid BOOLEAN DEFAULT true,
    requires_attachment BOOLEAN DEFAULT false,
    PRIMARY KEY (id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY (tenant_id, name)
);

CREATE TABLE leave_requests (
    id INT(11) NOT NULL AUTO_INCREMENT,
    employee_id INT(11) NOT NULL,
    leave_type_id INT(11) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    attachment_path VARCHAR(255),
    comments TEXT,
    approved_by INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id)
);

-- Insert default leave types for a default tenant (assuming tenant_id = 1)
-- You may need to adjust the tenant_id based on your data.
INSERT INTO leave_types (tenant_id, name, is_paid) VALUES
(1, 'Congé Annuel', true),
(1, 'Congé Maladie', true),
(1, 'Congé de Maternité/Paternité', true),
(1, 'Congé sans solde', false);