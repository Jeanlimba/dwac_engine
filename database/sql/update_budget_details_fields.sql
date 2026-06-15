-- Ajout des champs Unité, Quantité et Prix Unitaire aux détails du budget
ALTER TABLE mission_budget_detail_lines 
ADD COLUMN unit VARCHAR(50) DEFAULT NULL,
ADD COLUMN quantity DECIMAL(15, 2) DEFAULT 1.00,
ADD COLUMN unit_price DECIMAL(15, 2) DEFAULT 0.00;

-- Mise à jour des modèles également
ALTER TABLE budget_template_detail_lines 
ADD COLUMN unit VARCHAR(50) DEFAULT NULL,
ADD COLUMN quantity DECIMAL(15, 2) DEFAULT 1.00,
ADD COLUMN unit_price DECIMAL(15, 2) DEFAULT 0.00;

-- Table pour les unités budgétaires réutilisables (optionnel mais utile pour le "à sélectionner")
CREATE TABLE IF NOT EXISTS budgetary_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    UNIQUE(tenant_id, name)
);
