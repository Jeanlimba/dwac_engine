CREATE TABLE mission_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mission_id INT NOT NULL,
    tenant_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL,
    type ENUM('personnel', 'collectif') DEFAULT 'personnel',
    employee_id INT DEFAULT NULL, -- NULL if collective
    object VARCHAR(255) NOT NULL,
    itinerary TEXT,
    means_of_transport VARCHAR(255),
    departure_date DATE NOT NULL,
    return_date DATE NOT NULL,
    status ENUM('Brouillon', 'En attente', 'Validé', 'Rejeté') DEFAULT 'Brouillon',
    validated_by INT DEFAULT NULL, -- user_id of manager
    validated_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL
);
