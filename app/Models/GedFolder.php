<?php
use Core\Database;

class GedFolder {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Vérifie si l'utilisateur a un dossier racine, sinon le crée.
     */
    public function ensureRootFolder($userId, $tenantId, $username) {
        $this->db->query("SELECT id FROM ged_folders WHERE user_id = :user_id AND is_root = 1");
        $this->db->bind(':user_id', $userId);
        
        if (!$this->db->single()) {
            $this->db->query("INSERT INTO ged_folders (tenant_id, user_id, name, is_root) VALUES (:tenant_id, :user_id, :name, 1)");
            $this->db->bind(':tenant_id', $tenantId);
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':name', 'Mon Espace (' . $username . ')');
            $this->db->execute();
        }
    }

    /**
     * Récupère le dossier racine d'un utilisateur
     */
    public function getRootFolder($userId) {
        $this->db->query("SELECT * FROM ged_folders WHERE user_id = :user_id AND is_root = 1");
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
    }

    public function getFolderById($id) {
        $this->db->query("SELECT f.*, 
                         (SELECT COUNT(*) FROM missions WHERE ged_folder_id = f.id) as is_mission
                         FROM ged_folders f WHERE f.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getSubfolders($parentId, $orderBy = 'name', $orderDir = 'ASC', $q = null) {
        $validColumns = ['name', 'created_at'];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'name';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT f.*, 
                         (SELECT COUNT(*) FROM ged_shares WHERE folder_id = f.id) as share_count,
                         (SELECT COUNT(*) FROM ged_files WHERE folder_id = f.id) as file_count,
                         (SELECT SUM(size) FROM ged_files WHERE folder_id = f.id) as total_size,
                         (SELECT COUNT(*) FROM missions WHERE ged_folder_id = f.id) as is_mission
                         FROM ged_folders f 
                         WHERE f.parent_id = :parent_id";
        
        if ($q) {
            $sql .= " AND f.name LIKE :q";
        }
        
        $sql .= " ORDER BY f.$orderBy $orderDir";

        $this->db->query($sql);
        $this->db->bind(':parent_id', $parentId);
        if ($q) {
            $this->db->bind(':q', '%' . $q . '%');
        }
        return $this->db->resultSet();
    }

    public function getBreadcrumbs($folderId) {
        $breadcrumbs = [];
        $currentId = $folderId;

        while ($currentId) {
            $this->db->query("SELECT id, name, parent_id FROM ged_folders WHERE id = :id");
            $this->db->bind(':id', $currentId);
            $folder = $this->db->single();
            
            if ($folder) {
                array_unshift($breadcrumbs, $folder);
                $currentId = $folder->parent_id;
            } else {
                break;
            }
        }
        return $breadcrumbs;
    }

    public function createFolder($data) {
        $this->db->query("INSERT INTO ged_folders (tenant_id, user_id, name, parent_id) VALUES (:tenant_id, :user_id, :name, :parent_id)");
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':parent_id', $data['parent_id']);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function renameFolder($id, $newName) {
        $this->db->query("UPDATE ged_folders SET name = :name WHERE id = :id");
        $this->db->bind(':name', $newName);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function deleteFolder($id) {
        $this->db->query("DELETE FROM ged_folders WHERE id = :id AND is_root = 0");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function searchFolders($userId, $term) {
        $this->db->query("SELECT f.*, 
                         (SELECT COUNT(*) FROM ged_shares WHERE folder_id = f.id) as share_count,
                         (SELECT COUNT(*) FROM ged_files WHERE folder_id = f.id) as file_count,
                         (SELECT SUM(size) FROM ged_files WHERE folder_id = f.id) as total_size,
                         (SELECT COUNT(*) FROM missions WHERE ged_folder_id = f.id) as is_mission
                         FROM ged_folders f 
                         WHERE f.user_id = :user_id AND f.name LIKE :term
                         ORDER BY f.name ASC");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':term', '%' . $term . '%');
        return $this->db->resultSet();
    }

    public function hasAccess($folderId, $userId) {
        // Vérifier si un partage existe pour cet utilisateur
        $this->db->query("SELECT id FROM ged_shares WHERE folder_id = :folder_id AND shared_with_user_id = :user_id");
        $this->db->bind(':folder_id', $folderId);
        $this->db->bind(':user_id', $userId);
        
        if ($this->db->single()) return true;

        // Vérifier l'accès hérité (parent)
        $folder = $this->getFolderById($folderId);
        if ($folder && $folder->parent_id) {
            return $this->hasAccess($folder->parent_id, $userId);
        }

        return false;
    }

    public function moveFolder($id, $newParentId) {
        // Sécurité : ne pas déplacer un dossier dans lui-même ou un de ses descendants
        if ($id == $newParentId) return false;
        
        $this->db->query("UPDATE ged_folders SET parent_id = :parent_id WHERE id = :id");
        $this->db->bind(':parent_id', $newParentId);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function copyFolder($id, $newParentId, $gedFileModel) {
        $folder = $this->getFolderById($id);
        if (!$folder) return false;

        // Créer le nouveau dossier
        $newData = [
            'tenant_id' => $folder->tenant_id,
            'user_id' => $folder->user_id,
            'name' => 'Copie de ' . $folder->name,
            'parent_id' => $newParentId
        ];
        $newFolderId = $this->createFolder($newData);
        
        if ($newFolderId) {
            // Copier les fichiers
            $files = $gedFileModel->getFilesByFolder($id);
            foreach ($files as $file) {
                $gedFileModel->copyFile($file->id, $newFolderId);
            }

            // Copier les sous-dossiers (récursif)
            $subfolders = $this->getSubfolders($id);
            foreach ($subfolders as $subfolder) {
                $this->copyFolder($subfolder->id, $newFolderId, $gedFileModel);
            }
            return $newFolderId;
        }
        return false;
    }

    public function getAllFoldersTree($userId) {
        $this->db->query("SELECT id, name, parent_id FROM ged_folders WHERE user_id = :user_id ORDER BY name ASC");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
}
