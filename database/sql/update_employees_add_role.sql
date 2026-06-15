ALTER TABLE employees
ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'employee';

-- You can optionally update existing employees to a default role if needed, for example:
-- UPDATE employees SET role = 'employee' WHERE role IS NULL;
