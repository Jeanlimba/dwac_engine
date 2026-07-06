<?php
use Core\Database;

class Supervisor extends Controller {
    private $partnerModel;
    private $missionModel;
    private $expenseModel;
    private $employeeModel;
    private $missionOrderModel;
    private $notificationModel;

    public function __construct() {
        $this->requireRole(['superviseur', 'manager']);

        $this->partnerModel = $this->model('Partner');
        $this->missionModel = $this->model('Mission');
        $this->expenseModel = $this->model('Expense');
        $this->employeeModel = $this->model('Employee');
        $this->missionOrderModel = $this->model('MissionOrder');
        $this->notificationModel = $this->model('Notification');
    }

    // --- PARTNERS ---
    public function partners() {
        $partners = $this->partnerModel->getPartnersByTenant($_SESSION['tenant_id']);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'tenant_id' => $_SESSION['tenant_id'],
                'name' => trim($_POST['name']),
                'contact_person' => trim($_POST['contact_person']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                'address' => trim($_POST['address'])
            ];

            if ($this->partnerModel->addPartner($data)) {
                header('Location: ' . URLROOT . '/supervisor/partners');
                exit;
            }
        }

        $data = [
            'title' => 'Gestion des Partenaires',
            'partners' => $partners
        ];

        $this->view('supervisor/partners', $data);
    }

    private function syncMissionTeamGedAndNotify($mission_id, $new_team_employees, $folder_id) {
        require_once '../app/Models/GedShare.php';
        $gedShareModel = new GedShare();
        $mission = $this->missionModel->getMissionById($mission_id, $_SESSION['tenant_id']);
        
        // Get current team in DB (before update if we are in editMission)
        // Wait, if this is called AFTER DB update, we need to know who was removed.
        // Let's pass $new_team_employees (array of employee IDs)
        
        $old_team = $this->missionModel->getMissionTeam($mission_id);
        $old_employee_ids = array_map(function($m) { return $m->employee_id; }, $old_team);
        
        // 1. Revoke shares for removed employees
        if ($folder_id) {
            foreach ($old_employee_ids as $old_emp_id) {
                if (!in_array($old_emp_id, $new_team_employees)) {
                    $user_id = $this->employeeModel->getUserIdByEmployeeId($old_emp_id);
                    if ($user_id) {
                        $gedShareModel->revokeShareByFolderAndUser($folder_id, $user_id);
                        // Notification for removal
                        $this->notificationModel->notifyEmployee($old_emp_id, $_SESSION['tenant_id'], "Retrait de mission", "Vous avez été retiré de la mission : " . $mission->title, 'info', 'ged');
                    }
                }
            }
        }

        // 2. Add shares and notify for new/existing employees
        foreach ($new_team_employees as $emp_id) {
            if ($folder_id) {
                $user_id = $this->employeeModel->getUserIdByEmployeeId($emp_id);
                if ($user_id) {
                    $shareData = [
                        'folder_id' => $folder_id,
                        'file_id' => null,
                        'shared_by' => $_SESSION['user_id'],
                        'shared_with' => $user_id,
                        'permission' => 'edit'
                    ];
                    $gedShareModel->addShare($shareData);
                }
            }
            
            // Notify if they are new
            if (!in_array($emp_id, $old_employee_ids)) {
                $this->notificationModel->notifyEmployee($emp_id, $_SESSION['tenant_id'], "Affectation à une mission", "Vous avez été affecté à la mission : " . $mission->title, 'success', 'ged');
            }
        }
    }

    // --- MISSIONS ---
    public function missions() {
        $missions = $this->missionModel->getMissionsByTenant($_SESSION['tenant_id']);
        $partners = $this->partnerModel->getPartnersByTenant($_SESSION['tenant_id']);
        $employees = $this->employeeModel->getEmployeesByTenant($_SESSION['tenant_id']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_mission'])) {
            $means = $_POST['means_of_transport'] ?? null;
            if (is_array($means)) $means = implode(', ', $means);

            $missionData = [
                'tenant_id' => $_SESSION['tenant_id'],
                'partner_id' => $_POST['partner_id'],
                'title' => trim($_POST['title']),
                'date_start' => $_POST['date_start'],
                'date_end' => $_POST['date_end'],
                'duration_days' => $_POST['duration_days'] ?? 0,
                'hours_per_day' => $_POST['hours_per_day'] ?? 0,
                'means_of_transport' => $means,
                'estimated_revenue' => $_POST['estimated_revenue'],
                'description' => trim($_POST['description'])
            ];

            $mission_id = $this->missionModel->createMission($missionData);

            if ($mission_id) {
                // GED : Créer un dossier pour la mission
                require_once '../app/Models/GedFolder.php';
                $gedFolderModel = new GedFolder();

                $rootFolder = $gedFolderModel->getRootFolder($_SESSION['user_id']);
                $folderData = [
                    'tenant_id' => $_SESSION['tenant_id'],
                    'user_id' => $_SESSION['user_id'],
                    'name' => 'Mission : ' . $missionData['title'],
                    'parent_id' => $rootFolder ? $rootFolder->id : null
                ];
                
                $folder_id = $gedFolderModel->createFolder($folderData);

                if ($folder_id) {
                    $this->missionModel->updateGedFolder($mission_id, $folder_id);
                }

                // Add team members
                $new_employee_ids = [];
                if (isset($_POST['team']) && is_array($_POST['team'])) {
                    foreach ($_POST['team'] as $member) {
                        if (!empty($member['employee_id'])) {
                            $teamData = [
                                'mission_id' => $mission_id,
                                'employee_id' => $member['employee_id'],
                                'role_in_mission' => $member['role'],
                                'hourly_rate' => $member['hourly_rate']
                            ];
                            $this->missionModel->addTeamMember($teamData);
                            $new_employee_ids[] = $member['employee_id'];
                        }
                    }
                }

                // Sync GED and Notify
                $this->syncMissionTeamGedAndNotify($mission_id, $new_employee_ids, $folder_id);

                header('Location: ' . URLROOT . '/supervisor/missions');
                exit;
            }
        }

        $data = [
            'title' => 'Gestion des Missions',
            'missions' => $missions,
            'partners' => $partners,
            'employees' => $employees
        ];

        $this->view('supervisor/missions', $data);
    }

    public function missionDetails($id) {
        $mission = $this->missionModel->getMissionById($id, $_SESSION['tenant_id']);
        if (!$mission) {
            die("Mission introuvable");
        }

        $team = $this->missionModel->getMissionTeam($id);

        $raw_expenses = $this->missionModel->getMissionExpenses($id);
        $expenses_by_status = [
            'pending' => [],
            'validated' => [],
            'rejected' => []
        ];
        foreach ($raw_expenses as $expense) {
            $status = $expense->status;
            $status_key = 'pending';
            if (strpos($status, 'Validé') !== false) {
                $status_key = 'validated';
            } elseif ($status == 'Rejeté') {
                $status_key = 'rejected';
            }
            $expenses_by_status[$status_key][] = $expense;
        }

        $budgetItems = $this->missionModel->getBudgetItems($id);
        $missionCharges = $this->missionModel->getMissionCharges($_SESSION['tenant_id']);
        $missionOrders = $this->missionOrderModel->getOrdersByMission($id);
        
        // Fetch budget structure
        $budgetMainLines = $this->missionModel->getBudgetMainLines($id);
        foreach ($budgetMainLines as $line) {
            $line->details = $this->missionModel->getBudgetDetailLines($line->id);
        }

        // Fetch budget templates
        $budgetTemplates = $this->missionModel->getBudgetTemplates($_SESSION['tenant_id']);

        // Fetch budgetary units
        $budgetaryUnits = $this->missionModel->getBudgetaryUnits($_SESSION['tenant_id']);

        $data = [
            'title' => 'Détails de la Mission',
            'mission' => $mission,
            'team' => $team,
            'expenses_by_status' => $expenses_by_status,
            'budgetItems' => $budgetItems,
            'missionCharges' => $missionCharges,
            'budgetMainLines' => $budgetMainLines,
            'missionOrders' => $missionOrders,
            'budgetTemplates' => $budgetTemplates,
            'budgetaryUnits' => $budgetaryUnits
        ];

        $this->view('supervisor/mission_details', $data);
    }

    public function saveBudgetAsTemplate($mission_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['template_name']);
            if ($this->missionModel->saveBudgetAsTemplate($mission_id, $_SESSION['tenant_id'], $name)) {
                flash('mission_message', 'Budget enregistré comme modèle');
            } else {
                flash('mission_message', 'Erreur lors de l\'enregistrement du modèle', 'alert alert-danger');
            }
            header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
            exit;
        }
    }

    public function importBudgetTemplate($mission_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $template_id = $_POST['template_id'];
            if ($this->missionModel->importTemplateToMission($template_id, $mission_id, $_SESSION['tenant_id'])) {
                flash('mission_message', 'Modèle de budget importé avec succès');
            } else {
                flash('mission_message', 'Erreur lors de l\'importation du modèle', 'alert alert-danger');
            }
            header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
            exit;
        }
    }

    public function deleteBudgetTemplate($id, $mission_id) {
        if ($this->missionModel->deleteBudgetTemplate($id, $_SESSION['tenant_id'])) {
            flash('mission_message', 'Modèle de budget supprimé');
        }
        header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
        exit;
    }

    public function addBudgetMainLine($mission_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'mission_id' => $mission_id,
                'tenant_id' => $_SESSION['tenant_id'],
                'code' => trim($_POST['code']),
                'label' => trim($_POST['label'])
            ];
            $this->missionModel->addBudgetMainLine($data);
            
            if ($this->isAjax()) {
                $this->renderBudgetTable($mission_id);
                return;
            }
            
            header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
            exit;
        }
    }

    public function editBudgetMainLine($mission_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => $_POST['id'],
                'mission_id' => $mission_id,
                'code' => trim($_POST['code']),
                'label' => trim($_POST['label'])
            ];
            $this->missionModel->updateBudgetMainLine($data);
            
            if ($this->isAjax()) {
                $this->renderBudgetTable($mission_id);
                return;
            }

            header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
            exit;
        }
    }

    public function addBudgetDetailLine($mission_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $unit = trim($_POST['unit']);
            $new_unit = trim($_POST['new_unit'] ?? '');
            
            if (!empty($new_unit)) {
                $this->missionModel->addBudgetaryUnit($_SESSION['tenant_id'], $new_unit);
                $unit = $new_unit;
            }

            $qty = floatval($_POST['quantity'] ?? 1);
            $price = floatval($_POST['unit_price'] ?? 0);
            $amount = $qty * $price;

            $data = [
                'main_line_id' => $_POST['main_line_id'],
                'code' => trim($_POST['code']),
                'label' => trim($_POST['label']),
                'unit' => $unit,
                'quantity' => $qty,
                'unit_price' => $price,
                'amount' => $amount
            ];
            $this->missionModel->addBudgetDetailLine($data);
            
            if ($this->isAjax()) {
                $this->renderBudgetTable($mission_id);
                return;
            }

            header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
            exit;
        }
    }

    public function editBudgetDetailLine($mission_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $unit = trim($_POST['unit']);
            $new_unit = trim($_POST['new_unit'] ?? '');
            
            if (!empty($new_unit)) {
                $this->missionModel->addBudgetaryUnit($_SESSION['tenant_id'], $new_unit);
                $unit = $new_unit;
            }

            $qty = floatval($_POST['quantity'] ?? 1);
            $price = floatval($_POST['unit_price'] ?? 0);
            $amount = $qty * $price;

            $data = [
                'id' => $_POST['id'],
                'code' => trim($_POST['code']),
                'label' => trim($_POST['label']),
                'unit' => $unit,
                'quantity' => $qty,
                'unit_price' => $price,
                'amount' => $amount
            ];
            $this->missionModel->updateBudgetDetailLine($data);
            
            if ($this->isAjax()) {
                $this->renderBudgetTable($mission_id);
                return;
            }

            header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
            exit;
        }
    }

    public function deleteBudgetMainLine($id, $mission_id) {
        $this->missionModel->deleteBudgetMainLine($id, $mission_id);
        
        if ($this->isAjax()) {
            $this->renderBudgetTable($mission_id);
            return;
        }

        header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
        exit;
    }

    public function deleteBudgetDetailLine($id, $mission_id) {
        $this->missionModel->deleteBudgetDetailLine($id);
        
        if ($this->isAjax()) {
            $this->renderBudgetTable($mission_id);
            return;
        }

        header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
        exit;
    }

    private function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    private function renderBudgetTable($mission_id) {
        $budgetMainLines = $this->missionModel->getBudgetMainLines($mission_id);
        foreach ($budgetMainLines as $line) {
            $line->details = $this->missionModel->getBudgetDetailLines($line->id);
        }
        $data = ['budgetMainLines' => $budgetMainLines];
        require APPROOT . '/Views/supervisor/_budget_table.php';
    }

    public function addMissionBudgetItem($mission_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Retrieve charge name for label fallback
            $charge_id = $_POST['charge_id'];
            $db = new Database();
            $db->query("SELECT name FROM charges WHERE id = :id");
            $db->bind(':id', $charge_id);
            $charge = $db->single();

            $data = [
                'mission_id' => $mission_id,
                'tenant_id' => $_SESSION['tenant_id'],
                'charge_id' => $charge_id,
                'label' => $charge ? $charge->name : '',
                'unit' => trim($_POST['unit']),
                'budget_line' => trim($_POST['budget_line']),
                'unit_amount' => $_POST['unit_amount']
            ];

            if ($this->missionModel->addBudgetItem($data)) {
                header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
                exit;
            }
        }
    }

    public function editMissionBudgetItem($mission_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $charge_id = $_POST['charge_id'];
            $db = new Database();
            $db->query("SELECT name FROM charges WHERE id = :id");
            $db->bind(':id', $charge_id);
            $charge = $db->single();

            $data = [
                'id' => $_POST['id'],
                'mission_id' => $mission_id,
                'charge_id' => $charge_id,
                'label' => $charge ? $charge->name : '',
                'unit' => trim($_POST['unit']),
                'budget_line' => trim($_POST['budget_line']),
                'unit_amount' => $_POST['unit_amount']
            ];

            if ($this->missionModel->updateBudgetItem($data)) {
                header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
                exit;
            }
        }
    }

    public function importMissionCharges($mission_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['charge_ids'])) {
            $db = new Database();
            foreach ($_POST['charge_ids'] as $id) {
                $db->query("SELECT name FROM charges WHERE id = :id");
                $db->bind(':id', $id);
                $charge = $db->single();

                $data = [
                    'mission_id' => $mission_id,
                    'tenant_id' => $_SESSION['tenant_id'],
                    'charge_id' => $id,
                    'label' => $charge ? $charge->name : '',
                    'unit' => '-',
                    'budget_line' => '-',
                    'unit_amount' => 0
                ];
                $this->missionModel->addBudgetItem($data);
            }
            header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
            exit;
        }
    }

    public function editMission($id) {
        $mission = $this->missionModel->getMissionById($id, $_SESSION['tenant_id']);
        if (!$mission) {
            die("Mission introuvable");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $missionData = [
                'id' => $id,
                'tenant_id' => $_SESSION['tenant_id'],
                'partner_id' => $_POST['partner_id'],
                'title' => trim($_POST['title']),
                'date_start' => $_POST['date_start'],
                'date_end' => $_POST['date_end'],
                'duration_days' => $_POST['duration_days'] ?? 0,
                'hours_per_day' => $_POST['hours_per_day'] ?? 0,
                'means_of_transport' => $_POST['means_of_transport'] ?? null,
                'estimated_revenue' => $_POST['estimated_revenue'],
                'description' => trim($_POST['description']),
                'status' => $_POST['status']
            ];

            if ($this->missionModel->updateMission($missionData)) {
                $folder_id = $mission->ged_folder_id;
                
                $new_employee_ids = [];
                if (isset($_POST['team']) && is_array($_POST['team'])) {
                    foreach ($_POST['team'] as $member) {
                        if (!empty($member['employee_id'])) {
                            $new_employee_ids[] = $member['employee_id'];
                        }
                    }
                }

                // Sync GED and Notify BEFORE deleting old members in DB, 
                // but syncMissionTeamGedAndNotify needs to know the NEW team to compare with current DB.
                // So we call it, then we update DB.
                $this->syncMissionTeamGedAndNotify($id, $new_employee_ids, $folder_id);

                // Update team: delete and re-add
                $this->missionModel->deleteTeamMembers($id);
                if (isset($_POST['team']) && is_array($_POST['team'])) {
                    foreach ($_POST['team'] as $member) {
                        if (!empty($member['employee_id'])) {
                            $teamData = [
                                'mission_id' => $id,
                                'employee_id' => $member['employee_id'],
                                'role_in_mission' => $member['role'],
                                'hourly_rate' => $member['hourly_rate']
                            ];
                            $this->missionModel->addTeamMember($teamData);
                        }
                    }
                }
                header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $id);
                exit;
            }
        }

        $partners = $this->partnerModel->getPartnersByTenant($_SESSION['tenant_id']);
        $employees = $this->employeeModel->getEmployeesByTenant($_SESSION['tenant_id']);
        $team = $this->missionModel->getMissionTeam($id);

        $data = [
            'title' => 'Modifier la Mission',
            'mission' => $mission,
            'partners' => $partners,
            'employees' => $employees,
            'team' => $team
        ];

        $this->view('supervisor/edit_mission', $data);
    }

    public function deleteMission($id) {
        if ($this->missionModel->deleteMission($id, $_SESSION['tenant_id'])) {
            header('Location: ' . URLROOT . '/supervisor/missions');
            exit;
        } else {
            die("Erreur lors de la suppression");
        }
    }

    public function saveMissionAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            
            $mission_id = !empty($_POST['mission_id']) ? $_POST['mission_id'] : null;
            
            $missionData = [
                'tenant_id' => $_SESSION['tenant_id'],
                'partner_id' => $_POST['partner_id'],
                'title' => trim($_POST['title']),
                'date_start' => $_POST['date_start'],
                'date_end' => $_POST['date_end'],
                'duration_days' => $_POST['duration_days'] ?? 0,
                'hours_per_day' => $_POST['hours_per_day'] ?? 0,
                'means_of_transport' => $_POST['means_of_transport'] ?? null,
                'estimated_revenue' => $_POST['estimated_revenue'],
                'description' => trim($_POST['description']),
                'status' => $_POST['status'] ?? 'En attente'
            ];

            if ($mission_id) {
                // Update
                $missionData['id'] = $mission_id;
                $success = $this->missionModel->updateMission($missionData);
                $id = $mission_id;
            } else {
                // Create
                $id = $this->missionModel->createMission($missionData);
                $success = (bool)$id;
            }

            if ($success) {
                // GED : Gérer le dossier de mission
                require_once '../app/Models/GedFolder.php';
                $gedFolderModel = new GedFolder();

                $mission = $this->missionModel->getMissionById($id, $_SESSION['tenant_id']);
                $folder_id = $mission->ged_folder_id;

                if (!$folder_id) {
                    $rootFolder = $gedFolderModel->getRootFolder($_SESSION['user_id']);
                    $folderData = [
                        'tenant_id' => $_SESSION['tenant_id'],
                        'user_id' => $_SESSION['user_id'],
                        'name' => 'Mission : ' . $missionData['title'],
                        'parent_id' => $rootFolder ? $rootFolder->id : null
                    ];
                    $folder_id = $gedFolderModel->createFolder($folderData);
                    if ($folder_id) {
                        $this->missionModel->updateGedFolder($id, $folder_id);
                    }
                }

                $new_employee_ids = [];
                if (isset($_POST['team']) && is_array($_POST['team'])) {
                    foreach ($_POST['team'] as $member) {
                        if (!empty($member['employee_id'])) {
                            $new_employee_ids[] = $member['employee_id'];
                        }
                    }
                }

                // Sync GED and Notify
                $this->syncMissionTeamGedAndNotify($id, $new_employee_ids, $folder_id);

                // Update team: delete and re-add
                $this->missionModel->deleteTeamMembers($id);
                if (isset($_POST['team']) && is_array($_POST['team'])) {
                    foreach ($_POST['team'] as $member) {
                        if (!empty($member['employee_id'])) {
                            $teamData = [
                                'mission_id' => $id,
                                'employee_id' => $member['employee_id'],
                                'role_in_mission' => $member['role'],
                                'hourly_rate' => $member['hourly_rate']
                            ];
                            $this->missionModel->addTeamMember($teamData);
                        }
                    }
                }
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
            }
            exit;
        }
    }

    // --- EXPENSES ---
    public function expenses() {
        $raw_expenses = $this->expenseModel->getExpensesByTenant($_SESSION['tenant_id']);
        
        $expenses_by_status = [
            'pending' => [],
            'validated' => [],
            'rejected' => []
        ];

        foreach ($raw_expenses as $expense) {
            $status = $expense->status;
            $status_key = 'pending';
            
            if (strpos($status, 'Validé') !== false) {
                $status_key = 'validated';
            } elseif ($status == 'Rejeté') {
                $status_key = 'rejected';
            }
            
            $group_name = $expense->mission_id ? $expense->mission_title : 'Administration';
            if (!isset($expenses_by_status[$status_key][$group_name])) {
                $expenses_by_status[$status_key][$group_name] = [];
            }
            $expenses_by_status[$status_key][$group_name][] = $expense;
        }

        $missions = $this->missionModel->getMissionsByTenant($_SESSION['tenant_id']);
        $notifications = $this->notificationModel->getUnreadByUser($_SESSION['user_id']);

        $data = [
            'title' => 'Validation des Dépenses',
            'expenses_by_status' => $expenses_by_status,
            'missions' => $missions,
            'notifications' => $notifications
        ];

        $this->view('supervisor/expenses', $data);
    }

    public function processExpense($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/supervisor/expenses');
            exit;
        }

        $action = $_POST['action']; // validate, reject, modify
        $comment = trim($_POST['comment']);
        $role = $_SESSION['user_role'];
        
        $expense = $this->expenseModel->getExpenseById($id);
        // Vérifie que la dépense appartient bien au tenant courant : empêche un
        // superviseur/manager de valider/rejeter la dépense d'un autre tenant (IDOR).
        if (!$expense || $expense->tenant_id != $_SESSION['tenant_id']) {
            header('Location: ' . URLROOT . '/supervisor/expenses');
            exit;
        }

        $status = $expense->status;
        $employee_id = $expense->employee_id;
        $tenant_id = $_SESSION['tenant_id'];

        if ($action === 'validate') {
            if ($role === 'superviseur' && $status === 'En attente') {
                $this->expenseModel->validateBySupervisor($id, $_SESSION['employee_id'], $comment);
                
                // Notify Employee
                $this->notificationModel->notifyEmployee($employee_id, $tenant_id, "Dépense validée (Niveau 1)", "Votre dépense de $expense->amount $expense->currency a été validée par le superviseur.", 'success', 'expenses');
                
                // Notify Managers
                $this->notificationModel->notifyRole($tenant_id, 'manager', "Nouvelle dépense à valider (Final)", "Une dépense a été validée par un superviseur et attend votre validation finale.", 'warning', 'supervisor/expenses');
                
            } elseif ($role === 'manager') {
                // Manager validates
                $this->expenseModel->validateByManager($id, $_SESSION['employee_id'], $comment);
                
                // Notify Employee
                $this->notificationModel->notifyEmployee($employee_id, $tenant_id, "Dépense validée (Final)", "Votre dépense de $expense->amount $expense->currency a reçu la validation finale.", 'success', 'expenses');
                
                // Notify Supervisor if they validated it first
                if ($expense->supervisor_id) {
                    $this->notificationModel->notifyEmployee($expense->supervisor_id, $tenant_id, "Dépense validée par le Manager", "Une dépense que vous aviez approuvée a été finalisée.", 'info', 'supervisor/expenses');
                }
            }
        } elseif ($action === 'modify') {
            $this->expenseModel->requestModification($id, $comment);
            $this->notificationModel->notifyEmployee($employee_id, $tenant_id, "Modification demandée", "Une modification est demandée pour votre dépense : $comment", 'warning', 'expenses');
        } elseif ($action === 'reject') {
            $this->expenseModel->rejectExpense($id, $comment);
            $this->notificationModel->notifyEmployee($employee_id, $tenant_id, "Dépense rejetée", "Votre dépense a été rejetée. Motif : $comment", 'danger', 'expenses');
        }

        audit_log('expense.' . $action, 'Dépense #' . $id . ' (employé #' . $employee_id . ')');
        flash('supervisor_message', 'Action effectuée avec succès');
        header('Location: ' . URLROOT . '/supervisor/expenses');
        exit;
    }

    public function validateExpense($id) {
        header('Location: ' . URLROOT . '/supervisor/expenses');
        exit;
    }

    public function rejectExpense($id) {
        header('Location: ' . URLROOT . '/supervisor/expenses');
        exit;
    }

    public function getExpenseDetail($id) {
        $expense = $this->expenseModel->getExpenseById($id);
        // Ne divulgue le détail que pour une dépense du tenant courant (IDOR).
        if (!$expense || $expense->tenant_id != $_SESSION['tenant_id']) {
            echo json_encode(['success' => false]);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode($expense);
        exit;
    }

    // --- MISSION ORDERS ---
    public function missionOrders() {
        $orders = $this->missionOrderModel->getOrdersByTenant($_SESSION['tenant_id']);
        
        $data = [
            'title' => 'Ordres de Mission',
            'orders' => $orders
        ];

        $this->view('supervisor/mission_orders', $data);
    }

    public function createMissionOrder($mission_id = null) {
        if ($mission_id === null && isset($_POST['mission_id'])) {
            $mission_id = $_POST['mission_id'] ?: null;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_number'])) {
            $data = [
                'mission_id' => $mission_id,
                'tenant_id' => $_SESSION['tenant_id'],
                'order_number' => trim($_POST['order_number']),
                'type' => $_POST['type'],
                'employee_id' => $_POST['employee_id'] ?: null,
                'object' => trim($_POST['object']),
                'itinerary' => trim($_POST['itinerary']),
                'means_of_transport' => trim($_POST['means_of_transport']),
                'departure_date' => $_POST['departure_date'],
                'return_date' => $_POST['return_date'],
                'status' => 'En attente'
            ];

            if ($this->missionOrderModel->create($data)) {
                if ($mission_id) {
                    header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $mission_id);
                } else {
                    header('Location: ' . URLROOT . '/supervisor/missionOrders');
                }
                exit;
            }
        }

        if ($mission_id || (isset($_POST['action']) && $_POST['action'] == 'create_instant')) {
            $mission = null;
            if ($mission_id) {
                $mission = $this->missionModel->getMissionById($mission_id, $_SESSION['tenant_id']);
            }

            $employees = $this->employeeModel->getEmployeesByTenant($_SESSION['tenant_id']);
            $team = $mission_id ? $this->missionModel->getMissionTeam($mission_id) : $employees;

            $data = [
                'title' => 'Créer un Ordre de Mission',
                'mission' => $mission,
                'employees' => $employees,
                'team' => $team,
                'is_instant' => !$mission_id
            ];

            $this->view('supervisor/create_mission_order', $data);
        } else {
            // No mission selected yet, show mission selection
            $missions = $this->missionModel->getMissionsByTenant($_SESSION['tenant_id']);
            $data = [
                'title' => 'Choisir une Mission',
                'missions' => $missions
            ];
            $this->view('supervisor/select_mission_for_order', $data);
        }
    }

    public function validateMissionOrder($id) {
        if ($_SESSION['user_role'] !== 'manager') {
            die("Accès refusé");
        }

        if ($this->missionOrderModel->validate($id, $_SESSION['user_id'])) {
            $order = $this->missionOrderModel->getOrderById($id);
            if ($order->mission_id) {
                header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $order->mission_id);
            } else {
                header('Location: ' . URLROOT . '/supervisor/missionOrders');
            }
            exit;
        }
    }

    public function editMissionOrder($id) {
        $order = $this->missionOrderModel->getOrderById($id);
        if (!$order) die("Ordre introuvable");

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => $id,
                'order_number' => trim($_POST['order_number']),
                'type' => $_POST['type'],
                'employee_id' => $_POST['employee_id'] ?: null,
                'object' => trim($_POST['object']),
                'itinerary' => trim($_POST['itinerary']),
                'means_of_transport' => trim($_POST['means_of_transport']),
                'departure_date' => $_POST['departure_date'],
                'return_date' => $_POST['return_date'],
                'status' => $order->status,
                'signatory_name' => trim($_POST['signatory_name']),
                'signatory_role' => trim($_POST['signatory_role']),
                'sign_city' => trim($_POST['sign_city']),
                'footer_text' => trim($_POST['footer_text']),
                'agency_name' => trim($_POST['agency_name']),
                'agency_address' => trim($_POST['agency_address']),
                'agency_phone' => trim($_POST['agency_phone'])
            ];

            if ($this->missionOrderModel->update($data)) {
                echo json_encode(['success' => true]);
                exit;
            }
        }

        $tenant = $this->model('Tenant')->getTenantById($_SESSION['tenant_id']);
        $employee = null;
        if ($order->employee_id) {
            $employee = $this->employeeModel->getEmployeeById($order->employee_id, $_SESSION['tenant_id']);
        }
        $employees = $this->employeeModel->getEmployeesByTenant($_SESSION['tenant_id']);

        $data = [
            'order' => $order,
            'tenant' => $tenant,
            'employee' => $employee,
            'employees' => $employees
        ];

        $this->view('supervisor/edit_mission_order', $data);
    }

    public function rejectMissionOrder($id) {
        if ($_SESSION['user_role'] !== 'manager') {
            die("Accès refusé");
        }

        if ($this->missionOrderModel->reject($id, $_SESSION['user_id'])) {
            $order = $this->missionOrderModel->getOrderById($id);
            if ($order->mission_id) {
                header('Location: ' . URLROOT . '/supervisor/missionDetails/' . $order->mission_id);
            } else {
                header('Location: ' . URLROOT . '/supervisor/missionOrders');
            }
            exit;
        }
    }

    public function printMissionOrder($id) {
        $order = $this->missionOrderModel->getOrderById($id);
        if (!$order) die("Ordre introuvable");
        if ($order->status !== 'Validé' && $_SESSION['user_role'] !== 'manager' && $_SESSION['user_role'] !== 'superviseur') {
            die("Accès refusé - L'ordre doit être validé pour l'impression");
        }

        $tenant = $this->model('Tenant')->getTenantById($_SESSION['tenant_id']);
        
        $employee = null;
        if ($order->employee_id) {
            $employee = $this->employeeModel->getEmployeeById($order->employee_id, $_SESSION['tenant_id']);
        }

        $data = [
            'order' => $order,
            'tenant' => $tenant,
            'employee' => $employee
        ];

        $this->view('supervisor/print_mission_order', $data);
    }

    public function downloadMissionOrder($id) {
        $order = $this->missionOrderModel->getOrderById($id);
        if (!$order) die("Ordre introuvable");
        if ($order->status !== 'Validé' && $_SESSION['user_role'] !== 'manager' && $_SESSION['user_role'] !== 'superviseur') {
            die("Accès refusé - L'ordre doit être validé pour le téléchargement");
        }

        $tenant = $this->model('Tenant')->getTenantById($_SESSION['tenant_id']);
        
        // Fetch employee details for function/post
        $employee = null;
        if ($order->employee_id) {
            $employee = $this->employeeModel->getEmployeeById($order->employee_id, $_SESSION['tenant_id']);
        }

        require_once APPROOT . '/../src/DocxTemplate.php';
        $templatePath = APPROOT . '/../public/ordre_mission/template_final.docx';
        
        try {
            $docx = new DocxTemplate($templatePath);
            
            $start = new DateTime($order->departure_date);
            $end = new DateTime($order->return_date);
            $diff = $start->diff($end);
            $days = $diff->days + 1;

            $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
            $period = "du " . $formatter->format($start) . " au " . $formatter->format($end);
            $durationText = $days . " jours (" . $period . ")";

            $beneficiary = $order->type == 'collectif' ? 'COLLECTIF' : ($order->prenom . ' ' . $order->nom);
            
            $data = [
                'NUMERO' => strip_tags($order->order_number),
                'NOMS' => strip_tags($beneficiary),
                'FONCTION' => strip_tags($order->signatory_role ?? ($employee->poste_name ?? 'Agent')),
                'OBJET' => strip_tags($order->object),
                'LIEUX' => strip_tags($order->itinerary),
                'DUREE' => strip_tags($durationText),
                'MOYEN_DEPLACEMENT' => strip_tags($order->means_of_transport),
                'DATE_VALIDATION' => $order->validated_at ? $formatter->format(new DateTime($order->validated_at)) : $formatter->format(new DateTime()),
                'AGENCE' => strip_tags($order->agency_name ?? $tenant->name),
                'VILLE' => strip_tags($order->sign_city ?? 'Kinshasa'),
                'SIGNATAIRE' => strip_tags($order->signatory_name ?? 'NGUBI Mac'),
                'FOOTER_TEXT' => strip_tags($order->footer_text)
            ];
            
            $docx->replace($data);
            $filename = "Ordre_de_mission_" . str_replace('/', '_', $order->order_number) . ".docx";
            $docx->download($filename);
        } catch (Exception $e) {
            die("Erreur lors de la génération du fichier : " . $e->getMessage());
        }
    }
}
