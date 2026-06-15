CREATE DATABASE evolution;

USE evolution;

CREATE TABLE tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    acronym VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_super_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Default super admin (password: password123)
INSERT INTO `users` (`id`, `tenant_id`, `username`, `password`, `is_super_admin`, `created_at`) VALUES
(1, NULL, 'super_admin', '$2y$10$NO404RzGu67NIUHGbnW6W.87wAYRpEc/aoQ6sza7j.9gVxfB/GsIe', 1, '2024-07-25 10:00:00');


ALTER TABLE users                                                                                                                            
  ADD COLUMN employee_id INT UNIQUE AFTER tenant_id;                                                                                           
                                                                                                                                               
  ALTER TABLE users                                                                                                                            
 ADD CONSTRAINT fk_employee                                                                                                                   
 FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE; 