<?php
use Core\Database;

class Tenant {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function countTenants() {
        $this->db->query("SELECT COUNT(*) as total FROM tenants");
        $row = $this->db->single();
        return $row->total;
    }

    public function getTenants() {
        $this->db->query("SELECT * FROM tenants ORDER BY created_at DESC");
        return $this->db->resultSet();
    }

    public function getTenantById($id) {
        $this->db->query("SELECT * FROM tenants WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function create($data) {
        $this->db->query("INSERT INTO tenants (name, acronym, address, phone) VALUES (:name, :acronym, :address, :phone)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':acronym', $data['acronym']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':phone', $data['phone']);
        return $this->db->execute();
    }

    public function update($data) {
        $this->db->query("UPDATE tenants SET name = :name, acronym = :acronym, address = :address, phone = :phone WHERE id = :id");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':acronym', $data['acronym']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':id', $data['id']);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM tenants WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
