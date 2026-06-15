CREATE TABLE employee_trainings (
         id INT(11) NOT NULL AUTO_INCREMENT,
         employee_id INT(11) NOT NULL,
         tenant_id INT(11) NOT NULL,
         training_name VARCHAR(255) NOT NULL,
         institution VARCHAR(255),
         date_completed DATE,
         expiry_date DATE,
         description TEXT,
        attachment_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
    );