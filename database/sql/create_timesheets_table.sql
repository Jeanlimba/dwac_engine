CREATE TABLE timesheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    employee_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    category ENUM('Mission', 'Voyage', 'Courses administratif', 'Autre course', 'Lecture', 'Autre temps libre') NOT NULL,
    mission_id INT DEFAULT NULL,
    custom_mission_name VARCHAR(255) DEFAULT NULL,
    task_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE SET NULL
);
