<?php
use Core\Database;

class Mission {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getMissionsByTenant($tenant_id) {
        $this->db->query("SELECT m.*, p.name as partner_name 
                         FROM missions m 
                         JOIN partners p ON m.partner_id = p.id 
                         WHERE m.tenant_id = :tenant_id 
                         ORDER BY m.created_at DESC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    public function countMissions($tenant_id) {
        $this->db->query("SELECT COUNT(*) as total FROM missions WHERE tenant_id = :tenant_id");
        $this->db->bind(':tenant_id', $tenant_id);
        $row = $this->db->single();
        return $row->total;
    }

    public function countActiveMissions($tenant_id) {
        $this->db->query("SELECT COUNT(*) as total FROM missions WHERE tenant_id = :tenant_id AND status = 'En cours'");
        $this->db->bind(':tenant_id', $tenant_id);
        $row = $this->db->single();
        return $row->total;
    }

    public function createMission($data) {
        // Génération du code automatique (xxxxx/MM-YY)
        $month = date('m');
        $year = date('y');
        
        $this->db->query("SELECT COUNT(*) as total FROM missions WHERE tenant_id = :tenant_id");
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $row = $this->db->single();
        $next_number = ($row->total ?? 0) + 1;
        $mission_code = str_pad($next_number, 5, '0', STR_PAD_LEFT) . '/' . $month . '-' . $year;

        $this->db->query("INSERT INTO missions (tenant_id, mission_code, partner_id, title, date_start, date_end, duration_days, hours_per_day, means_of_transport, estimated_revenue, description, ged_folder_id) 
                         VALUES (:tenant_id, :mission_code, :partner_id, :title, :date_start, :date_end, :duration_days, :hours_per_day, :means_of_transport, :estimated_revenue, :description, :ged_folder_id)");
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':mission_code', $mission_code);
        $this->db->bind(':partner_id', $data['partner_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':date_start', $data['date_start']);
        $this->db->bind(':date_end', $data['date_end']);
        $this->db->bind(':duration_days', $data['duration_days'] ?? 0);
        $this->db->bind(':hours_per_day', $data['hours_per_day'] ?? 0);
        $this->db->bind(':means_of_transport', $data['means_of_transport'] ?? null);
        $this->db->bind(':estimated_revenue', $data['estimated_revenue']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':ged_folder_id', $data['ged_folder_id'] ?? null);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateGedFolder($mission_id, $folder_id) {
        $this->db->query("UPDATE missions SET ged_folder_id = :folder_id WHERE id = :id");
        $this->db->bind(':folder_id', $folder_id);
        $this->db->bind(':id', $mission_id);
        return $this->db->execute();
    }

    public function addTeamMember($data) {
        $this->db->query("INSERT INTO mission_team (mission_id, employee_id, role_in_mission, hourly_rate) 
                         VALUES (:mission_id, :employee_id, :role_in_mission, :hourly_rate)");
        $this->db->bind(':mission_id', $data['mission_id']);
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':role_in_mission', $data['role_in_mission']);
        $this->db->bind(':hourly_rate', $data['hourly_rate']);
        return $this->db->execute();
    }

    public function getMissionById($id, $tenant_id) {
        $this->db->query("SELECT m.*, p.name as partner_name 
                         FROM missions m 
                         JOIN partners p ON m.partner_id = p.id 
                         WHERE m.id = :id AND m.tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->single();
    }

    public function updateMission($data) {
        $this->db->query("UPDATE missions SET 
                         partner_id = :partner_id, 
                         title = :title, 
                         date_start = :date_start, 
                         date_end = :date_end, 
                         duration_days = :duration_days,
                         hours_per_day = :hours_per_day,
                         means_of_transport = :means_of_transport,
                         estimated_revenue = :estimated_revenue, 
                         description = :description,
                         status = :status
                         WHERE id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':partner_id', $data['partner_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':date_start', $data['date_start']);
        $this->db->bind(':date_end', $data['date_end']);
        $this->db->bind(':duration_days', $data['duration_days'] ?? 0);
        $this->db->bind(':hours_per_day', $data['hours_per_day'] ?? 0);
        $this->db->bind(':means_of_transport', $data['means_of_transport'] ?? null);
        $this->db->bind(':estimated_revenue', $data['estimated_revenue']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':tenant_id', $data['tenant_id']);
        return $this->db->execute();
    }

    public function deleteTeamMembers($mission_id) {
        $this->db->query("DELETE FROM mission_team WHERE mission_id = :mission_id");
        $this->db->bind(':mission_id', $mission_id);
        return $this->db->execute();
    }

    public function deleteMission($id, $tenant_id) {
        $this->db->query("DELETE FROM missions WHERE id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->execute();
    }

    public function getMissionTeam($mission_id) {
        $this->db->query("SELECT mt.*, e.prenom, e.nom 
                         FROM mission_team mt 
                         JOIN employees e ON mt.employee_id = e.id 
                         WHERE mt.mission_id = :mission_id");
        $this->db->bind(':mission_id', $mission_id);
        return $this->db->resultSet();
    }

    public function getMissionExpenses($mission_id) {
        $this->db->query("SELECT x.*, e.prenom, e.nom 
                         FROM expenses x 
                         JOIN employees e ON x.employee_id = e.id 
                         WHERE x.mission_id = :mission_id 
                         ORDER BY x.expense_date DESC");
        $this->db->bind(':mission_id', $mission_id);
        return $this->db->resultSet();
    }

    public function getAllMissionBudgetDetails($mission_id) {
        $this->db->query("SELECT dl.*, ml.code as main_code, ml.label as main_label 
                         FROM mission_budget_detail_lines dl
                         JOIN mission_budget_main_lines ml ON dl.main_line_id = ml.id
                         WHERE ml.mission_id = :mission_id
                         ORDER BY ml.code ASC, dl.code ASC");
        $this->db->bind(':mission_id', $mission_id);
        return $this->db->resultSet();
    }

    public function getChargesByCategory($tenant_id, $category) {
        $this->db->query("SELECT * FROM charges WHERE tenant_id = :tenant_id AND category = :category ORDER BY name ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->bind(':category', $category);
        return $this->db->resultSet();
    }

    // --- BUDGET ITEMS ---
    public function getBudgetItems($mission_id) {
        $this->db->query("SELECT mbi.*, c.name as charge_label 
                         FROM mission_budget_items mbi 
                         LEFT JOIN charges c ON mbi.charge_id = c.id 
                         WHERE mbi.mission_id = :mission_id 
                         ORDER BY mbi.budget_line ASC, c.name ASC");
        $this->db->bind(':mission_id', $mission_id);
        return $this->db->resultSet();
    }

    public function addBudgetItem($data) {
        $this->db->query("INSERT INTO mission_budget_items (mission_id, tenant_id, charge_id, label, unit, budget_line, unit_amount) 
                         VALUES (:mission_id, :tenant_id, :charge_id, :label, :unit, :budget_line, :unit_amount)");
        $this->db->bind(':mission_id', $data['mission_id']);
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':charge_id', $data['charge_id']);
        $this->db->bind(':label', $data['label']); // Stocké par sécurité, mais priorité au charge_id
        $this->db->bind(':unit', $data['unit']);
        $this->db->bind(':budget_line', $data['budget_line']);
        $this->db->bind(':unit_amount', $data['unit_amount']);
        return $this->db->execute();
    }

    public function updateBudgetItem($data) {
        $this->db->query("UPDATE mission_budget_items SET 
                         charge_id = :charge_id,
                         label = :label, 
                         unit = :unit, 
                         budget_line = :budget_line, 
                         unit_amount = :unit_amount 
                         WHERE id = :id AND mission_id = :mission_id");
        $this->db->bind(':charge_id', $data['charge_id']);
        $this->db->bind(':label', $data['label']);
        $this->db->bind(':unit', $data['unit']);
        $this->db->bind(':budget_line', $data['budget_line']);
        $this->db->bind(':unit_amount', $data['unit_amount']);
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':mission_id', $data['mission_id']);
        return $this->db->execute();
    }

    public function deleteBudgetItem($id, $mission_id) {
        $this->db->query("DELETE FROM mission_budget_items WHERE id = :id AND mission_id = :mission_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':mission_id', $mission_id);
        return $this->db->execute();
    }

    public function getMissionCharges($tenant_id) {
        $this->db->query("SELECT * FROM charges WHERE tenant_id = :tenant_id AND category = 'Mission' ORDER BY name ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    // --- BUDGET STRUCTURE ---
    public function getBudgetMainLines($mission_id) {
        $this->db->query("SELECT * FROM mission_budget_main_lines WHERE mission_id = :mission_id ORDER BY code ASC");
        $this->db->bind(':mission_id', $mission_id);
        return $this->db->resultSet();
    }

    public function getBudgetDetailLines($main_line_id) {
        $this->db->query("SELECT * FROM mission_budget_detail_lines WHERE main_line_id = :main_line_id ORDER BY code ASC");
        $this->db->bind(':main_line_id', $main_line_id);
        return $this->db->resultSet();
    }

    public function addBudgetMainLine($data) {
        $this->db->query("INSERT INTO mission_budget_main_lines (mission_id, tenant_id, code, label) 
                         VALUES (:mission_id, :tenant_id, :code, :label)");
        $this->db->bind(':mission_id', $data['mission_id']);
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':label', $data['label']);
        return $this->db->execute();
    }

    public function updateBudgetMainLine($data) {
        $this->db->query("UPDATE mission_budget_main_lines SET code = :code, label = :label 
                         WHERE id = :id AND mission_id = :mission_id");
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':label', $data['label']);
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':mission_id', $data['mission_id']);
        return $this->db->execute();
    }

    public function addBudgetDetailLine($data) {
        $this->db->query("INSERT INTO mission_budget_detail_lines (main_line_id, code, label, unit, quantity, unit_price, amount) 
                         VALUES (:main_line_id, :code, :label, :unit, :quantity, :unit_price, :amount)");
        $this->db->bind(':main_line_id', $data['main_line_id']);
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':label', $data['label']);
        $this->db->bind(':unit', $data['unit'] ?? null);
        $this->db->bind(':quantity', $data['quantity'] ?? 1);
        $this->db->bind(':unit_price', $data['unit_price'] ?? 0);
        $this->db->bind(':amount', $data['amount']);
        return $this->db->execute();
    }

    public function updateBudgetDetailLine($data) {
        $this->db->query("UPDATE mission_budget_detail_lines SET code = :code, label = :label, unit = :unit, quantity = :quantity, unit_price = :unit_price, amount = :amount 
                         WHERE id = :id");
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':label', $data['label']);
        $this->db->bind(':unit', $data['unit'] ?? null);
        $this->db->bind(':quantity', $data['quantity'] ?? 1);
        $this->db->bind(':unit_price', $data['unit_price'] ?? 0);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':id', $data['id']);
        return $this->db->execute();
    }

    public function deleteBudgetMainLine($id, $mission_id) {
        $this->db->query("DELETE FROM mission_budget_main_lines WHERE id = :id AND mission_id = :mission_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':mission_id', $mission_id);
        return $this->db->execute();
    }

    public function deleteBudgetDetailLine($id) {
        $this->db->query("DELETE FROM mission_budget_detail_lines WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Budgetary Units
    public function getBudgetaryUnits($tenant_id) {
        $this->db->query("SELECT * FROM budgetary_units WHERE tenant_id = :tenant_id ORDER BY name ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    public function addBudgetaryUnit($tenant_id, $name) {
        $this->db->query("INSERT IGNORE INTO budgetary_units (tenant_id, name) VALUES (:tenant_id, :name)");
        $this->db->bind(':tenant_id', $tenant_id);
        $this->db->bind(':name', $name);
        return $this->db->execute();
    }

    // Budget Templates Methods
    public function getBudgetTemplates($tenant_id) {
        $this->db->query("SELECT * FROM budget_templates WHERE tenant_id = :tenant_id ORDER BY name ASC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    public function getTemplateById($id) {
        $this->db->query("SELECT * FROM budget_templates WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function saveBudgetAsTemplate($mission_id, $tenant_id, $template_name) {
        try {
            // 1. Create template entry
            $this->db->query("INSERT INTO budget_templates (tenant_id, name) VALUES (:tenant_id, :name)");
            $this->db->bind(':tenant_id', $tenant_id);
            $this->db->bind(':name', $template_name);
            $this->db->execute();
            $template_id = $this->db->lastInsertId();

            // 2. Fetch current budget lines
            $mainLines = $this->getBudgetMainLines($mission_id);
            foreach ($mainLines as $main) {
                // Save main line to template
                $this->db->query("INSERT INTO budget_template_main_lines (template_id, code, label) VALUES (:template_id, :code, :label)");
                $this->db->bind(':template_id', $template_id);
                $this->db->bind(':code', $main->code);
                $this->db->bind(':label', $main->label);
                $this->db->execute();
                $new_main_id = $this->db->lastInsertId();

                // Fetch and save details
                $details = $this->getBudgetDetailLines($main->id);
                foreach ($details as $det) {
                    $this->db->query("INSERT INTO budget_template_detail_lines (main_line_id, code, label, unit, quantity, unit_price, amount) 
                                     VALUES (:main_line_id, :code, :label, :unit, :quantity, :unit_price, :amount)");
                    $this->db->bind(':main_line_id', $new_main_id);
                    $this->db->bind(':code', $det->code);
                    $this->db->bind(':label', $det->label);
                    $this->db->bind(':unit', $det->unit);
                    $this->db->bind(':quantity', $det->quantity);
                    $this->db->bind(':unit_price', $det->unit_price);
                    $this->db->bind(':amount', $det->amount);
                    $this->db->execute();
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function importTemplateToMission($template_id, $mission_id, $tenant_id) {
        try {
            // Fetch template structure
            $this->db->query("SELECT * FROM budget_template_main_lines WHERE template_id = :template_id");
            $this->db->bind(':template_id', $template_id);
            $templateMainLines = $this->db->resultSet();

            foreach ($templateMainLines as $tMain) {
                // Add to mission budget
                $this->db->query("INSERT INTO mission_budget_main_lines (mission_id, tenant_id, code, label) VALUES (:mission_id, :tenant_id, :code, :label)");
                $this->db->bind(':mission_id', $mission_id);
                $this->db->bind(':tenant_id', $tenant_id);
                $this->db->bind(':code', $tMain->code);
                $this->db->bind(':label', $tMain->label);
                $this->db->execute();
                $new_mission_main_id = $this->db->lastInsertId();

                // Fetch template details
                $this->db->query("SELECT * FROM budget_template_detail_lines WHERE main_line_id = :main_line_id");
                $this->db->bind(':main_line_id', $tMain->id);
                $templateDetails = $this->db->resultSet();

                foreach ($templateDetails as $tDet) {
                    $this->db->query("INSERT INTO mission_budget_detail_lines (main_line_id, code, label, unit, quantity, unit_price, amount) 
                                     VALUES (:main_line_id, :code, :label, :unit, :quantity, :unit_price, :amount)");
                    $this->db->bind(':main_line_id', $new_mission_main_id);
                    $this->db->bind(':code', $tDet->code);
                    $this->db->bind(':label', $tDet->label);
                    $this->db->bind(':unit', $tDet->unit);
                    $this->db->bind(':quantity', $tDet->quantity);
                    $this->db->bind(':unit_price', $tDet->unit_price);
                    $this->db->bind(':amount', $tDet->amount);
                    $this->db->execute();
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteBudgetTemplate($id, $tenant_id) {
        $this->db->query("DELETE FROM budget_templates WHERE id = :id AND tenant_id = :tenant_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->execute();
    }
}
