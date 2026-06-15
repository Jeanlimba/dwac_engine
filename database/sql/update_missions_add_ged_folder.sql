-- Ajout de la colonne ged_folder_id à la table missions
ALTER TABLE missions ADD COLUMN ged_folder_id INT DEFAULT NULL;
ALTER TABLE missions ADD CONSTRAINT fk_mission_ged_folder FOREIGN KEY (ged_folder_id) REFERENCES ged_folders(id) ON DELETE SET NULL;
