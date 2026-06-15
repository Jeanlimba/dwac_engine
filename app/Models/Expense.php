<?php
use Core\Database;

class Expense {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function countPendingExpenses($tenant_id) {
        $this->db->query("SELECT COUNT(*) as total FROM expenses WHERE tenant_id = :tenant_id AND status = 'En attente'");
        $this->db->bind(':tenant_id', $tenant_id);
        $row = $this->db->single();
        return $row->total;
    }

    public function getTotalApprovedAmount($tenant_id) {
        $this->db->query("SELECT SUM(amount) as total FROM expenses WHERE tenant_id = :tenant_id AND status IN ('Validé', 'Payé', 'Validé Manager')");
        $this->db->bind(':tenant_id', $tenant_id);
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    public function countEmployeePendingExpenses($employee_id) {
        $this->db->query("SELECT COUNT(*) as total FROM expenses WHERE employee_id = :employee_id AND status = 'En attente'");
        $this->db->bind(':employee_id', $employee_id);
        $row = $this->db->single();
        return $row->total;
    }

    public function getEmployeeTotalApprovedAmount($employee_id) {
        $this->db->query("SELECT SUM(amount) as total FROM expenses WHERE employee_id = :employee_id AND status IN ('Validé', 'Payé', 'Validé Manager')");
        $this->db->bind(':employee_id', $employee_id);
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    public function getExpensesByTenant($tenant_id) {
        $this->db->query("SELECT x.*, m.title as mission_title, e.prenom, e.nom, 
                         mbi.label as budget_item_label,
                         CONCAT(mbdl.code, ' ', mbdl.label) as budget_detail_label
                         FROM expenses x 
                         LEFT JOIN missions m ON x.mission_id = m.id 
                         LEFT JOIN mission_budget_items mbi ON x.budget_item_id = mbi.id
                         LEFT JOIN mission_budget_detail_lines mbdl ON x.budget_detail_id = mbdl.id
                         JOIN employees e ON x.employee_id = e.id 
                         WHERE x.tenant_id = :tenant_id 
                         ORDER BY x.expense_date DESC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    public function getEmployeeExpenses($employee_id) {
        $this->db->query("SELECT x.*, m.title as mission_title, 
                         mbi.label as budget_item_label,
                         CONCAT(mbdl.code, ' ', mbdl.label) as budget_detail_label
                         FROM expenses x 
                         LEFT JOIN missions m ON x.mission_id = m.id 
                         LEFT JOIN mission_budget_items mbi ON x.budget_item_id = mbi.id
                         LEFT JOIN mission_budget_detail_lines mbdl ON x.budget_detail_id = mbdl.id
                         WHERE x.employee_id = :employee_id 
                         ORDER BY x.expense_date DESC");
        $this->db->bind(':employee_id', $employee_id);
        return $this->db->resultSet();
    }

    public function addExpense($data) {
        $this->db->query("INSERT INTO expenses (tenant_id, mission_id, budget_item_id, budget_detail_id, employee_id, category, amount, currency, description, expense_date, expense_date_end, receipt_path) 
                         VALUES (:tenant_id, :mission_id, :budget_item_id, :budget_detail_id, :employee_id, :category, :amount, :currency, :description, :expense_date, :expense_date_end, :receipt_path)");
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':mission_id', $data['mission_id']);
        $this->db->bind(':budget_item_id', $data['budget_item_id'] ?? null);
        $this->db->bind(':budget_detail_id', $data['budget_detail_id'] ?? null);
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':currency', $data['currency']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':expense_date', $data['expense_date']);
        $this->db->bind(':expense_date_end', $data['expense_date_end']);
        $this->db->bind(':receipt_path', $data['receipt_path']);
        return $this->db->execute();
    }

    public function updateStatus($id, $status, $comment = null) {
        $this->db->query("UPDATE expenses SET status = :status, validation_comment = :comment WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':comment', $comment);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function validateBySupervisor($id, $supervisor_id, $comment = null) {
        $this->db->query("UPDATE expenses SET status = 'Validé Superviseur', supervisor_id = :s_id, supervisor_validation_date = CURRENT_TIMESTAMP, validation_comment = :comment WHERE id = :id");
        $this->db->bind(':s_id', $supervisor_id);
        $this->db->bind(':comment', $comment);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function validateByManager($id, $manager_id, $comment = null) {
        $this->db->query("UPDATE expenses SET status = 'Validé Manager', manager_id = :m_id, manager_validation_date = CURRENT_TIMESTAMP, validation_comment = :comment WHERE id = :id");
        $this->db->bind(':m_id', $manager_id);
        $this->db->bind(':comment', $comment);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function rejectExpense($id, $reason) {
        $this->db->query("UPDATE expenses SET status = 'Rejeté', validation_comment = :reason WHERE id = :id");
        $this->db->bind(':reason', $reason);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function requestModification($id, $comment) {
        $this->db->query("UPDATE expenses SET status = 'Modification demandée', validation_comment = :comment WHERE id = :id");
        $this->db->bind(':comment', $comment);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getExpenseById($id) {
        $this->db->query("SELECT x.*, m.title as mission_title, 
                         mbi.label as budget_item_label,
                         CONCAT(mbdl.code, ' ', mbdl.label) as budget_detail_label,
                         e.prenom, e.nom, e.role as employee_role
                         FROM expenses x 
                         LEFT JOIN missions m ON x.mission_id = m.id 
                         LEFT JOIN mission_budget_items mbi ON x.budget_item_id = mbi.id
                         LEFT JOIN mission_budget_detail_lines mbdl ON x.budget_detail_id = mbdl.id
                         JOIN employees e ON x.employee_id = e.id
                         WHERE x.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateExpense($data) {
        $sql = "UPDATE expenses SET 
                mission_id = :mission_id, 
                budget_item_id = :budget_item_id, 
                budget_detail_id = :budget_detail_id, 
                category = :category, 
                amount = :amount, 
                currency = :currency, 
                description = :description, 
                expense_date = :expense_date, 
                expense_date_end = :expense_date_end,
                status = 'En attente'";
        
        if (!empty($data['receipt_path'])) {
            $sql .= ", receipt_path = :receipt_path";
        }

        $sql .= " WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(':mission_id', $data['mission_id']);
        $this->db->bind(':budget_item_id', $data['budget_item_id'] ?? null);
        $this->db->bind(':budget_detail_id', $data['budget_detail_id'] ?? null);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':currency', $data['currency']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':expense_date', $data['expense_date']);
        $this->db->bind(':expense_date_end', $data['expense_date_end']);
        $this->db->bind(':id', $data['id']);
        
        if (!empty($data['receipt_path'])) {
            $this->db->bind(':receipt_path', $data['receipt_path']);
        }

        return $this->db->execute();
    }

    public function deleteExpense($id) {
        $this->db->query("DELETE FROM expenses WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
