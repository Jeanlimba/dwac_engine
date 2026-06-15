-- Autoriser user_id à être NULL pour les dépôts externes
ALTER TABLE ged_files MODIFY user_id INT NULL;

-- On s'assure que la contrainte de clé étrangère permet le NULL
ALTER TABLE ged_files DROP FOREIGN KEY ged_files_ibfk_2;
ALTER TABLE ged_files ADD CONSTRAINT ged_files_ibfk_2 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
