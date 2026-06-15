ALTER TABLE mission_budget_items 
ADD COLUMN charge_id INT NULL AFTER tenant_id,
ADD CONSTRAINT fk_budget_item_charge FOREIGN KEY (charge_id) REFERENCES charges(id) ON DELETE SET NULL;
