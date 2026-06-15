-- Table des dossiers
CREATE TABLE IF NOT EXISTS ged_folders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    parent_id INT DEFAULT NULL,
    is_root BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES ged_folders(id) ON DELETE CASCADE
);

-- Table des fichiers
CREATE TABLE IF NOT EXISTS ged_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folder_id INT NOT NULL,
    user_id INT NOT NULL, -- L'uploadeur
    name VARCHAR(255) NOT NULL, -- Nom affiché
    physical_name VARCHAR(255) NOT NULL, -- Nom sur le disque
    size INT NOT NULL, -- Taille en octets
    extension VARCHAR(10) NOT NULL,
    mime_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (folder_id) REFERENCES ged_folders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des partages
CREATE TABLE IF NOT EXISTS ged_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folder_id INT DEFAULT NULL,
    file_id INT DEFAULT NULL,
    shared_by_user_id INT NOT NULL,
    shared_with_user_id INT NOT NULL,
    permission ENUM('read', 'download', 'edit') DEFAULT 'read',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (folder_id) REFERENCES ged_folders(id) ON DELETE CASCADE,
    FOREIGN KEY (file_id) REFERENCES ged_files(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_with_user_id) REFERENCES users(id) ON DELETE CASCADE
);
