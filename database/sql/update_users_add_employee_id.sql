ALTER TABLE users
ADD COLUMN employee_id INT UNIQUE AFTER tenant_id;

ALTER TABLE users
ADD CONSTRAINT fk_employee
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;
