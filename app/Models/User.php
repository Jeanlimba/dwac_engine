<?php
use Core\Database;

class User {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Find user by username
    public function findUserByUsername($username) {
        $this->db->query('SELECT u.*, e.role, e.prenom, e.nom, e.photo 
                         FROM users u 
                         LEFT JOIN employees e ON u.employee_id = e.id 
                         WHERE u.username = :username');
        $this->db->bind(':username', $username);

        $row = $this->db->single();

        // Check row
        if ($this->db->rowCount() > 0) {
            return $row;
        } else {
            return false;
        }
    }

    // Login User
    public function login($username, $password) {
        $row = $this->findUserByUsername($username);

        if ($row === false) return false;

        if ($row->status === 'blocked') return 'blocked';

        $hashed_password = $row->password;
        if (password_verify($password, $hashed_password)) {
            return $row;
        } else {
            return false;
        }
    }

    public function getUserById($id) {
        $this->db->query('SELECT u.*, e.nom, e.prenom, e.role as employee_role FROM users u LEFT JOIN employees e ON u.employee_id = e.id WHERE u.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get all users (for Super Admin)
    public function getAllUsers() {
        $this->db->query('SELECT u.*, t.name as tenant_name, e.nom, e.prenom 
                         FROM users u 
                         LEFT JOIN tenants t ON u.tenant_id = t.id 
                         LEFT JOIN employees e ON u.employee_id = e.id
                         ORDER BY u.created_at DESC');
        return $this->db->resultSet();
    }

    // Get users by tenant
    public function getUsersByTenant($tenantId, $excludeUserId = null) {
        $sql = 'SELECT u.*, e.nom, e.prenom 
                FROM users u 
                LEFT JOIN employees e ON u.employee_id = e.id 
                WHERE u.tenant_id = :tenant_id';
        if ($excludeUserId) {
            $sql .= ' AND u.id != :exclude_id';
        }
        $this->db->query($sql);
        $this->db->bind(':tenant_id', $tenantId);
        if ($excludeUserId) {
            $this->db->bind(':exclude_id', $excludeUserId);
        }
        return $this->db->resultSet();
    }

    // Update user
    public function update($data) {
        $this->db->query('UPDATE users SET username = :username, tenant_id = :tenant_id, status = :status WHERE id = :id');
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':id', $data['id']);

        return $this->db->execute();
    }

    // Update password
    public function updatePassword($id, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $this->db->query('UPDATE users SET password = :password WHERE id = :id');
        $this->db->bind(':password', $hashed_password);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Delete user
    public function delete($id) {
        $this->db->query('DELETE FROM users WHERE id = :id AND is_super_admin = 0');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Toggle user status
    public function toggleStatus($id) {
        $user = $this->getUserById($id);
        if (!$user || $user->is_super_admin) return false;

        $newStatus = ($user->status === 'active') ? 'blocked' : 'active';
        $this->db->query('UPDATE users SET status = :status WHERE id = :id');
        $this->db->bind(':status', $newStatus);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function create($data) {
        $this->db->query('INSERT INTO users (tenant_id, employee_id, username, password, is_super_admin, status) 
                         VALUES (:tenant_id, :employee_id, :username, :password, :is_super_admin, :status)');
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':employee_id', $data['employee_id'] ?? null);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':is_super_admin', $data['is_super_admin'] ?? 0);
        $this->db->bind(':status', $data['status'] ?? 'active');

        return $this->db->execute();
    }
}
