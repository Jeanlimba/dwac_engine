<?php
use Core\Database;

class Timesheet {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function add($data) {
        $this->db->query("INSERT INTO timesheets (tenant_id, employee_id, date, start_time, end_time, category, mission_id, custom_mission_name, task_description) 
                         VALUES (:tenant_id, :employee_id, :date, :start_time, :end_time, :category, :mission_id, :custom_mission_name, :task_description)");
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':date', $data['date']);
        $this->db->bind(':start_time', $data['start_time']);
        $this->db->bind(':end_time', $data['end_time']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':mission_id', $data['mission_id']);
        $this->db->bind(':custom_mission_name', $data['custom_mission_name']);
        $this->db->bind(':task_description', $data['task_description']);
        return $this->db->execute();
    }

    public function update($id, $data) {
        $this->db->query("UPDATE timesheets SET 
                         date = :date, 
                         start_time = :start_time, 
                         end_time = :end_time, 
                         category = :category, 
                         mission_id = :mission_id, 
                         custom_mission_name = :custom_mission_name, 
                         task_description = :task_description 
                         WHERE id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':date', $data['date']);
        $this->db->bind(':start_time', $data['start_time']);
        $this->db->bind(':end_time', $data['end_time']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':mission_id', $data['mission_id']);
        $this->db->bind(':custom_mission_name', $data['custom_mission_name']);
        $this->db->bind(':task_description', $data['task_description']);
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $data['tenant_id']);
        return $this->db->execute();
    }

    public function delete($id, $tenant_id) {
        $this->db->query("DELETE FROM timesheets WHERE id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->execute();
    }

    public function getById($id, $tenant_id) {
        $this->db->query("SELECT * FROM timesheets WHERE id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->single();
    }

    public function getByEmployeeAndWeek($employee_id, $start_date, $end_date) {
        $this->db->query("SELECT t.*, m.title as mission_title 
                         FROM timesheets t 
                         LEFT JOIN missions m ON t.mission_id = m.id 
                         WHERE t.employee_id = :employee_id 
                         AND t.date BETWEEN :start_date AND :end_date 
                         ORDER BY t.date ASC, t.start_time ASC");
        $this->db->bind(':employee_id', $employee_id);
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);
        return $this->db->resultSet();
    }

    public function validate($id, $rating, $user_id) {
        $this->db->query("UPDATE timesheets SET status = 'valide', rating = :rating, validated_by = :validated_by, validated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $this->db->bind(':rating', $rating);
        $this->db->bind(':validated_by', $user_id);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function reject($id, $reason, $user_id) {
        $this->db->query("UPDATE timesheets SET status = 'rejete', rejection_reason = :reason, validated_by = :validated_by, validated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $this->db->bind(':reason', $reason);
        $this->db->bind(':validated_by', $user_id);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getPendingByTenant($tenant_id) {
        $this->db->query("SELECT t.*, e.prenom, e.nom, m.title as mission_title 
                         FROM timesheets t 
                         JOIN employees e ON t.employee_id = e.id 
                         LEFT JOIN missions m ON t.mission_id = m.id 
                         WHERE t.tenant_id = :tenant_id AND t.status = 'soumis' 
                         ORDER BY t.date DESC, e.nom ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    public function getTenantPerformance($tenant_id, $start_date, $end_date) {
        $this->db->query("SELECT e.id as employee_id, e.prenom, e.nom,
                         SUM(TIME_TO_SEC(TIMEDIFF(t.end_time, t.start_time))) / 3600 as total_hours,
                         SUM(CASE WHEN t.category = 'Mission' THEN TIME_TO_SEC(TIMEDIFF(t.end_time, t.start_time)) ELSE 0 END) / 3600 as mission_hours,
                         AVG(t.rating) as avg_rating,
                         COUNT(CASE WHEN t.status = 'valide' THEN 1 END) as validated_entries
                         FROM employees e
                         LEFT JOIN timesheets t ON e.id = t.employee_id AND t.date BETWEEN :start_date AND :end_date
                         WHERE e.tenant_id = :tenant_id
                         GROUP BY e.id, e.prenom, e.nom
                         ORDER BY total_hours DESC");
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);
        return $this->db->resultSet();
    }

    public function getDetailedReport($tenant_id, $start_date, $end_date) {
        $this->db->query("SELECT t.*, e.prenom, e.nom, m.title as mission_title 
                         FROM timesheets t 
                         JOIN employees e ON t.employee_id = e.id 
                         LEFT JOIN missions m ON t.mission_id = m.id 
                         WHERE t.tenant_id = :tenant_id 
                         AND t.date BETWEEN :start_date AND :end_date 
                         ORDER BY t.date ASC, e.nom ASC, t.start_time ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);
        return $this->db->resultSet();
    }
}
