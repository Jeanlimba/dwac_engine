<?php
use Core\Database;

class GedExternalLink {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function generateLink($folderId, $expiryDays = 7) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiryDays days"));

        $this->db->query("INSERT INTO ged_external_links (folder_id, token, expires_at) VALUES (:folder_id, :token, :expires_at)");
        $this->db->bind(':folder_id', $folderId);
        $this->db->bind(':token', $token);
        $this->db->bind(':expires_at', $expiresAt);
        
        if ($this->db->execute()) {
            return $token;
        }
        return false;
    }

    public function getLinkByToken($token) {
        $this->db->query("SELECT l.*, f.name as folder_name, f.tenant_id 
                         FROM ged_external_links l 
                         JOIN ged_folders f ON l.folder_id = f.id 
                         WHERE l.token = :token AND (l.expires_at IS NULL OR l.expires_at > NOW())");
        $this->db->bind(':token', $token);
        return $this->db->single();
    }

    public function getLinkByFolderId($folderId) {
        $this->db->query("SELECT * FROM ged_external_links WHERE folder_id = :folder_id ORDER BY created_at DESC LIMIT 1");
        $this->db->bind(':folder_id', $folderId);
        return $this->db->single();
    }

    public function renewLink($token, $expiryDays = 7) {
        $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiryDays days"));
        $this->db->query("UPDATE ged_external_links SET expires_at = :expires_at WHERE token = :token");
        $this->db->bind(':expires_at', $expiresAt);
        $this->db->bind(':token', $token);
        
        return $this->db->execute();
    }
}
