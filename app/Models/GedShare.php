<?php
use Core\Database;

class GedShare {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function addShare($data) {
        // Check if share already exists
        $this->db->query("SELECT id FROM ged_shares WHERE folder_id = :folder_id AND file_id = :file_id AND shared_with_user_id = :shared_with");
        $this->db->bind(':folder_id', $data['folder_id']);
        $this->db->bind(':file_id', $data['file_id']);
        $this->db->bind(':shared_with', $data['shared_with']);
        if ($this->db->single()) {
            return true; // Already shared
        }

        $this->db->query("INSERT INTO ged_shares (folder_id, file_id, shared_by_user_id, shared_with_user_id, permission) VALUES (:folder_id, :file_id, :shared_by, :shared_with, :permission)");
        $this->db->bind(':folder_id', $data['folder_id']);
        $this->db->bind(':file_id', $data['file_id']);
        $this->db->bind(':shared_by', $data['shared_by']);
        $this->db->bind(':shared_with', $data['shared_with']);
        $this->db->bind(':permission', $data['permission']);
        return $this->db->execute();
    }

    public function revokeShareByFolderAndUser($folderId, $userId) {
        $this->db->query("DELETE FROM ged_shares WHERE folder_id = :folder_id AND shared_with_user_id = :user_id");
        $this->db->bind(':folder_id', $folderId);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function getSharedWithMe($userId) {
        $this->db->query("SELECT s.*, f.name as folder_name, fi.name as file_name 
                         FROM ged_shares s 
                         LEFT JOIN ged_folders f ON s.folder_id = f.id
                         LEFT JOIN ged_files fi ON s.file_id = fi.id
                         WHERE s.shared_with_user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function getItemShares($type, $id) {
        $column = ($type === 'folder') ? 'folder_id' : 'file_id';
        $this->db->query("SELECT s.*, u.username as shared_with_name 
                         FROM ged_shares s 
                         JOIN users u ON s.shared_with_user_id = u.id 
                         WHERE s.$column = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultSet();
    }

    public function getShareById($shareId) {
        $this->db->query("SELECT * FROM ged_shares WHERE id = :id");
        $this->db->bind(':id', $shareId);
        return $this->db->single();
    }

    public function revokeShare($shareId) {
        $this->db->query("DELETE FROM ged_shares WHERE id = :id");
        $this->db->bind(':id', $shareId);
        return $this->db->execute();
    }
}
