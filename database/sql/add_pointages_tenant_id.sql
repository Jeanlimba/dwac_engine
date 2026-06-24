-- =============================================================================
-- Intégration "Présence" : isolation multi-tenant des pointages.
-- -----------------------------------------------------------------------------
-- La table `pointages` et la colonne `employees.zk_id` existent déjà, de même
-- que la contrainte UNIQUE(employe_id, date_heure) (`unique_attendance`) qui
-- assure la déduplication via INSERT IGNORE.
-- Cette migration ajoute `tenant_id` aux pointages pour que les rapports soient
-- filtrés par entreprise (et que l'API d'ingestion l'enregistre directement).
-- Idempotence : conçue pour une exécution unique sur la base `evolution`.
-- =============================================================================

-- 1. Colonne (d'abord nullable pour permettre le backfill).
ALTER TABLE pointages ADD COLUMN tenant_id INT NULL AFTER employe_id;

-- 2. Backfill depuis l'employé rattaché.
UPDATE pointages p
JOIN employees e ON p.employe_id = e.id
SET p.tenant_id = e.tenant_id;

-- 3. Désormais obligatoire (toujours renseigné à l'ingestion).
ALTER TABLE pointages MODIFY COLUMN tenant_id INT NOT NULL;

-- 4. Index pour les rapports filtrés par tenant.
ALTER TABLE pointages ADD INDEX idx_pointages_tenant (tenant_id);
