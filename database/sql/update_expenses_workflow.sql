ALTER TABLE expenses 
ADD COLUMN budget_item_id INT NULL AFTER mission_id,
ADD COLUMN supervisor_validation_date TIMESTAMP NULL,
ADD COLUMN manager_validation_date TIMESTAMP NULL,
ADD COLUMN supervisor_id INT NULL, -- Superviseur qui a validé
ADD COLUMN manager_id INT NULL,    -- Manager qui a validé
ADD COLUMN rejection_reason TEXT NULL,
ADD CONSTRAINT fk_expense_budget_item FOREIGN KEY (budget_item_id) REFERENCES mission_budget_items(id) ON DELETE SET NULL;
