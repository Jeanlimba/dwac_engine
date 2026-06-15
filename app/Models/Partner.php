<?php
use Core\Database;

class Partner {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getPartnersByTenant($tenant_id) {
        $this->db->query("SELECT * FROM partners WHERE tenant_id = :tenant_id ORDER BY name ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    public function countPartners($tenant_id) {
        $this->db->query("SELECT COUNT(*) as total FROM partners WHERE tenant_id = :tenant_id");
        $this->db->bind(':tenant_id', $tenant_id);
        $row = $this->db->single();
        return $row->total;
    }

    public function addPartner($data) {
        $this->db->query("INSERT INTO partners (tenant_id, name, contact_person, email, phone, address) 
                         VALUES (:tenant_id, :name, :contact_person, :email, :phone, :address)");
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':contact_person', $data['contact_person']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        return $this->db->execute();
    }
}
