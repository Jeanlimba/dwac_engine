-- =============================================================================
-- Journal d'audit : mise à niveau de la table action_logs.
-- -----------------------------------------------------------------------------
-- La table existait mais n'était pas exploitable comme audit :
--  - user_id NOT NULL + FK ON DELETE CASCADE => les traces disparaissaient à la
--    suppression d'un utilisateur (inacceptable pour un journal d'audit) ;
--  - pas de tenant_id (multi-tenant) ni d'IP.
-- On rend user_id nullable, on préserve les traces (ON DELETE SET NULL), et on
-- ajoute tenant_id + ip_address + index.
-- =============================================================================

ALTER TABLE action_logs MODIFY user_id INT NULL;
ALTER TABLE action_logs DROP FOREIGN KEY action_logs_ibfk_1;
ALTER TABLE action_logs ADD CONSTRAINT action_logs_user_fk
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE action_logs ADD COLUMN tenant_id INT NULL AFTER user_id;
ALTER TABLE action_logs ADD COLUMN ip_address VARCHAR(45) NULL AFTER details;
ALTER TABLE action_logs ADD INDEX idx_actionlogs_tenant (tenant_id);
ALTER TABLE action_logs ADD INDEX idx_actionlogs_created (created_at);
