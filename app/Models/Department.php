<?php
use Core\Database;

class Department {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Récupérer tous les départements d'un tenant avec le nom du parent
    public function getDepartmentsByTenant($tenant_id) {
        $this->db->query("SELECT d.*, p.name AS parent_name 
                         FROM departments d 
                         LEFT JOIN departments p ON d.parent_id = p.id 
                         WHERE d.tenant_id = :tenant_id 
                         ORDER BY d.name ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    // Compter les départements
    public function countDepartments($tenant_id) {
        $this->db->query("SELECT COUNT(*) as total FROM departments WHERE tenant_id = :tenant_id");
        $this->db->bind(':tenant_id', $tenant_id);
        $row = $this->db->single();
        return $row->total;
    }

    // Ajouter un département
    public function add($data) {
        $this->db->query("INSERT INTO departments (tenant_id, name, parent_id) VALUES (:tenant_id, :name, :parent_id)");
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':parent_id', $data['parent_id']);
        return $this->db->execute();
    }

    public function getDepartmentById($id, $tenant_id) {
        $this->db->query("SELECT * FROM departments WHERE id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->single();
    }

    public function update($data) {
        $this->db->query("UPDATE departments SET name = :name, parent_id = :parent_id WHERE id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':parent_id', $data['parent_id']);
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':tenant_id', $data['tenant_id']);
        return $this->db->execute();
    }

    public function delete($id, $tenant_id) {
        $this->db->query("DELETE FROM departments WHERE id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->execute();
    }

    public function hasEmployees($id, $tenant_id) {
        $this->db->query("SELECT COUNT(*) as total FROM employees WHERE departement_id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        $row = $this->db->single();
        return $row->total > 0;
    }

    public function hasChildren($id, $tenant_id) {
        $this->db->query("SELECT COUNT(*) as total FROM departments WHERE parent_id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        $row = $this->db->single();
        return $row->total > 0;
    }
}
