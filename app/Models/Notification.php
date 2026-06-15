<?php
use Core\Database;

class Notification {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function create($data) {
        $this->db->query("INSERT INTO notifications (user_id, tenant_id, title, message, type, link) 
                         VALUES (:user_id, :tenant_id, :title, :message, :type, :link)");
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':message', $data['message']);
        $this->db->bind(':type', $data['type'] ?? 'info');
        $this->db->bind(':link', $data['link'] ?? null);
        return $this->db->execute();
    }

    public function getUnreadByUser($user_id) {
        $this->db->query("SELECT * FROM notifications WHERE user_id = :user_id AND is_read = FALSE ORDER BY created_at DESC");
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getAllByUser($user_id) {
        $this->db->query("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 50");
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function markAsRead($id, $user_id) {
        $this->db->query("UPDATE notifications SET is_read = TRUE WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    public function markAllAsRead($user_id) {
        $this->db->query("UPDATE notifications SET is_read = TRUE WHERE user_id = :user_id");
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    /**
     * Notify all users with a specific role in a tenant
     */
    public function notifyRole($tenant_id, $role, $title, $message, $type = 'info', $link = null) {
        $this->db->query("SELECT u.id FROM users u 
                         JOIN employees e ON u.employee_id = e.id 
                         WHERE u.tenant_id = :tenant_id AND e.role = :role");
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->bind(':role', $role);
        $users = $this->db->resultSet();

        foreach ($users as $user) {
            $this->create([
                'user_id' => $user->id,
                'tenant_id' => $tenant_id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link' => $link
            ]);
        }
    }

    /**
     * Notify a specific employee (finds their user account)
     */
    public function notifyEmployee($employee_id, $tenant_id, $title, $message, $type = 'info', $link = null) {
        $this->db->query("SELECT id FROM users WHERE employee_id = :employee_id");
        $this->db->bind(':employee_id', $employee_id);
        $user = $this->db->single();

        if ($user) {
            $this->create([
                'user_id' => $user->id,
                'tenant_id' => $tenant_id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link' => $link
            ]);
        }
    }
}
