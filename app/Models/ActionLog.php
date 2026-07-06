<?php
use Core\Database;

/**
 * Journal d'audit : enregistre les actions sensibles (qui, quoi, quand, où).
 * La consultation est filtrée par tenant (le super-admin voit tout).
 */
class ActionLog {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /** Enregistre une entrée d'audit. */
    public function record($data) {
        $this->db->query("INSERT INTO action_logs (user_id, tenant_id, action, details, ip_address)
                          VALUES (:user_id, :tenant_id, :action, :details, :ip_address)");
        $this->db->bind(':user_id', $data['user_id'] ?? null);
        $this->db->bind(':tenant_id', $data['tenant_id'] ?? null);
        $this->db->bind(':action', $data['action']);
        $this->db->bind(':details', $data['details'] ?? null);
        $this->db->bind(':ip_address', $data['ip_address'] ?? null);
        return $this->db->execute();
    }

    /**
     * Entrées récentes. Si $tenantId est null (super-admin), retourne tout ;
     * sinon uniquement celles du tenant. Limit/offset castés en entier (sûrs).
     */
    public function getRecent($tenantId = null, $limit = 50, $offset = 0) {
        $limit = max(1, (int) $limit);
        $offset = max(0, (int) $offset);

        $sql = "SELECT a.*, u.username
                FROM action_logs a
                LEFT JOIN users u ON a.user_id = u.id";
        if ($tenantId !== null) {
            $sql .= " WHERE a.tenant_id = :tenant_id";
        }
        $sql .= " ORDER BY a.created_at DESC LIMIT $limit OFFSET $offset";

        $this->db->query($sql);
        if ($tenantId !== null) {
            $this->db->bind(':tenant_id', $tenantId);
        }
        return $this->db->resultSet();
    }

    /** Nombre total d'entrées (pour la pagination). */
    public function countAll($tenantId = null) {
        $sql = "SELECT COUNT(*) AS total FROM action_logs";
        if ($tenantId !== null) {
            $sql .= " WHERE tenant_id = :tenant_id";
        }
        $this->db->query($sql);
        if ($tenantId !== null) {
            $this->db->bind(':tenant_id', $tenantId);
        }
        $row = $this->db->single();
        return (int) ($row->total ?? 0);
    }
}
