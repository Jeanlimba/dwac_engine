<?php
use Core\Database;

class GedFile {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getFilesByFolder($folderId, $orderBy = 'name', $orderDir = 'ASC', $q = null) {
        $validColumns = ['name', 'created_at', 'size'];
        $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'name';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT f.*, 
                         (SELECT COUNT(*) FROM missions WHERE ged_folder_id = f.folder_id) as is_mission
                         FROM ged_files f WHERE f.folder_id = :folder_id";
        
        if ($q) {
            $sql .= " AND f.name LIKE :q";
        }

        $sql .= " ORDER BY f.$orderBy $orderDir";

        $this->db->query($sql);
        $this->db->bind(':folder_id', $folderId);
        if ($q) {
            $this->db->bind(':q', '%' . $q . '%');
        }
        return $this->db->resultSet();
    }

    public function getFileById($id) {
        $this->db->query("SELECT f.*, 
                         (SELECT COUNT(*) FROM missions WHERE ged_folder_id = f.folder_id) as is_mission
                         FROM ged_files f WHERE f.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addFile($data) {
        $this->db->query("INSERT INTO ged_files (folder_id, user_id, name, physical_name, size, extension, mime_type) VALUES (:folder_id, :user_id, :name, :physical_name, :size, :extension, :mime_type)");
        $this->db->bind(':folder_id', $data['folder_id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':physical_name', $data['physical_name']);
        $this->db->bind(':size', $data['size']);
        $this->db->bind(':extension', $data['extension']);
        $this->db->bind(':mime_type', $data['mime_type']);
        return $this->db->execute();
    }

    public function searchFiles($userId, $term) {
        $this->db->query("SELECT f.*, 
                         (SELECT COUNT(*) FROM missions WHERE ged_folder_id = f.folder_id) as is_mission
                         FROM ged_files f 
                         JOIN ged_folders d ON f.folder_id = d.id
                         WHERE d.user_id = :user_id AND f.name LIKE :term 
                         ORDER BY f.name ASC");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':term', '%' . $term . '%');
        return $this->db->resultSet();
    }

    public function renameFile($id, $newName) {
        $this->db->query("UPDATE ged_files SET name = :name WHERE id = :id");
        $this->db->bind(':name', $newName);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function deleteFile($id) {
        $this->db->query("DELETE FROM ged_files WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function moveFile($id, $newFolderId) {
        $this->db->query("UPDATE ged_files SET folder_id = :folder_id WHERE id = :id");
        $this->db->bind(':folder_id', $newFolderId);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function copyFile($id, $newFolderId) {
        $file = $this->getFileById($id);
        if (!$file) return false;

        $targetDir = APPROOT . '/../public/uploads/ged/';
        $extension = pathinfo($file->name, PATHINFO_EXTENSION);
        $physicalName = uniqid() . '.' . $extension;

        if (copy($targetDir . $file->physical_name, $targetDir . $physicalName)) {
            $data = [
                'folder_id' => $newFolderId,
                'user_id' => $file->user_id,
                'name' => 'Copie de ' . $file->name,
                'physical_name' => $physicalName,
                'size' => $file->size,
                'extension' => $file->extension,
                'mime_type' => $file->mime_type
            ];
            return $this->addFile($data);
        }
        return false;
    }
}
