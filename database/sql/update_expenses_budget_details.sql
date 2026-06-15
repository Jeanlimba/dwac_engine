-- Ajout de la colonne pour lier directement à une ligne de détail du budget
ALTER TABLE expenses 
ADD COLUMN budget_detail_id INT NULL AFTER budget_item_id,
ADD CONSTRAINT fk_expense_budget_detail FOREIGN KEY (budget_detail_id) REFERENCES mission_budget_detail_lines(id) ON DELETE SET NULL;
