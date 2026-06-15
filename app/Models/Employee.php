<?php
use Core\Database;

class Employee {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Récupérer tous les employés d'un tenant (exclure les admins)
    public function getEmployeesByTenant($tenant_id) {
        $this->db->query("SELECT * FROM employees 
                         WHERE tenant_id = :tenant_id 
                         AND (role != 'admin' OR role IS NULL) 
                         ORDER BY nom ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    // Récupérer un employé par son ID
    public function getEmployeeById($id, $tenant_id) {
        $this->db->query("SELECT e.*, d.name AS department_name, p.nom_poste AS poste_name, u.username 
                         FROM employees e 
                         LEFT JOIN departments d ON e.departement_id = d.id 
                         LEFT JOIN postes p ON e.poste_id = p.id
                         LEFT JOIN users u ON e.id = u.employee_id
                         WHERE e.id = :id AND e.tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->single();
    }

    // Compter le nombre d'employés
    public function countEmployees($tenant_id) {
        $this->db->query("SELECT COUNT(*) as total FROM employees WHERE tenant_id = :tenant_id");
        $this->db->bind(':tenant_id', $tenant_id);
        $row = $this->db->single();
        return $row->total;
    }

    public function getUserIdByEmployeeId($employee_id) {
        $this->db->query("SELECT id FROM users WHERE employee_id = :employee_id");
        $this->db->bind(':employee_id', $employee_id);
        $row = $this->db->single();
        return $row ? $row->id : null;
    }

    public function deleteEmployee($id, $tenant_id) {
        $this->db->query("DELETE FROM employees WHERE id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->execute();
    }
}
