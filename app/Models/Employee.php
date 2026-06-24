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

    /* ---- Présence : enrôlement biométrique (zk_id) ---- */

    /** Employés d'un tenant non encore enrôlés sur la pointeuse. */
    public function getWithoutZkIdByTenant($tenant_id) {
        $this->db->query("SELECT id, nom, prenom, matricule
                          FROM employees
                          WHERE tenant_id = :tenant_id AND (zk_id IS NULL OR zk_id = 0)
                          ORDER BY nom ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    /** Plus grand zk_id déjà attribué dans le tenant (pour calculer le suivant). */
    public function getMaxZkIdByTenant($tenant_id) {
        $this->db->query("SELECT COALESCE(MAX(zk_id), 0) AS max_id FROM employees WHERE tenant_id = :tenant_id");
        $this->db->bind(':tenant_id', $tenant_id);
        $row = $this->db->single();
        return (int) ($row->max_id ?? 0);
    }

    /**
     * Attribue un zk_id à un employé du tenant, uniquement s'il n'en a pas déjà
     * (évite d'écraser un enrôlement existant). Renvoie true si la mise à jour
     * a bien eu lieu.
     */
    public function assignZkId($id, $tenant_id, $zk_id) {
        $this->db->query("UPDATE employees SET zk_id = :zk_id
                          WHERE id = :id AND tenant_id = :tenant_id
                            AND (zk_id IS NULL OR zk_id = 0)");
        $this->db->bind(':zk_id', $zk_id);
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }
}
