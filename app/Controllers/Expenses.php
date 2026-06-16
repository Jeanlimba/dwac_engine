<?php
class Expenses extends Controller {
    private $expenseModel;
    private $missionModel;
    private $notificationModel;

    public function __construct() {
        $this->requireLogin();
        $this->denySuperAdmin();

        $this->expenseModel = $this->model('Expense');
        $this->missionModel = $this->model('Mission');
        $this->notificationModel = $this->model('Notification');
    }

    public function index() {
        $raw_expenses = $this->expenseModel->getEmployeeExpenses($_SESSION['employee_id']);
        
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
        $admin_charges = $this->missionModel->getChargesByCategory($_SESSION['tenant_id'], 'Administrative');
        $notifications = $this->notificationModel->getUnreadByUser($_SESSION['user_id']);

        $data = [
            'title' => 'Mes Dépenses',
            'expenses_by_status' => $expenses_by_status,
            'missions' => $missions,
            'admin_charges' => $admin_charges,
            'notifications' => $notifications
        ];

        $this->view('expenses/index', $data);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $count = count($_POST['expense_date']);
            $success_count = 0;
            $global_mission_id = !empty($_POST['mission_id']) ? $_POST['mission_id'] : null;

            for ($i = 0; $i < $count; $i++) {
                if (empty($_POST['amount'][$i])) continue;

                $receipt_path = null;
                if (isset($_FILES['receipt']['name'][$i]) && $_FILES['receipt']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_tmp_path = $_FILES['receipt']['tmp_name'][$i];
                    $file_name = $_FILES['receipt']['name'][$i];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $new_file_name = uniqid('', true) . '.' . $file_ext;
                    $upload_dir = 'public/uploads/receipts/';
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $dest_path = $upload_dir . $new_file_name;

                    if (move_uploaded_file($file_tmp_path, $dest_path)) {
                        $receipt_path = str_replace('public/', '', $dest_path);
                    }
                }

                $budget_detail_id = !empty($_POST['budget_detail_id'][$i]) ? $_POST['budget_detail_id'][$i] : null;
                $category = $_POST['category'][$i] ?? 'Mission';

                $data = [
                    'tenant_id' => $_SESSION['tenant_id'],
                    'employee_id' => $_SESSION['employee_id'],
                    'mission_id' => $global_mission_id,
                    'budget_item_id' => null,
                    'budget_detail_id' => $budget_detail_id,
                    'category' => $category,
                    'amount' => $_POST['amount'][$i],
                    'currency' => $_POST['currency'][$i] ?? 'USD',
                    'description' => trim($_POST['description'][$i]),
                    'expense_date' => $_POST['expense_date'][$i],
                    'expense_date_end' => !empty($_POST['expense_date_end'][$i]) ? $_POST['expense_date_end'][$i] : null,
                    'receipt_path' => $receipt_path
                ];

                if ($this->expenseModel->addExpense($data)) {
                    $success_count++;
                }
            }

            if ($success_count > 0) {
                // Notifications
                $role = $_SESSION['user_role'];
                $emp_name = $_SESSION['user_name'] ?? 'Un employé';
                if ($role === 'superviseur') {
                    $this->notificationModel->notifyRole($_SESSION['tenant_id'], 'manager', "Nouvelle dépense à valider", "$emp_name a soumis $success_count dépense(s).", 'warning', 'supervisor/expenses');
                } else {
                    $this->notificationModel->notifyRole($_SESSION['tenant_id'], 'superviseur', "Nouvelle dépense à valider", "$emp_name a soumis $success_count dépense(s).", 'warning', 'supervisor/expenses');
                }

                flash('expense_message', $success_count . ' dépense(s) enregistrée(s) avec succès');
            }
            header('Location: ' . URLROOT . '/expenses');
            exit;
        }
    }

    public function getMissionRubrics($mission_id) {
        $rubrics = $this->missionModel->getAllMissionBudgetDetails($mission_id);
        header('Content-Type: application/json');
        echo json_encode($rubrics);
        exit;
    }

    public function getExpenseFormData() {
        $missions = $this->missionModel->getMissionsByTenant($_SESSION['tenant_id']);
        $admin_charges = $this->missionModel->getChargesByCategory($_SESSION['tenant_id'], 'Administrative');
        
        header('Content-Type: application/json');
        echo json_encode([
            'missions' => $missions,
            'admin_charges' => $admin_charges
        ]);
        exit;
    }

    public function edit($id) {
        $expense = $this->expenseModel->getExpenseById($id);

        // Security check
        if (!$expense || $expense->employee_id != $_SESSION['employee_id']) {
            flash('expense_message', 'Accès non autorisé', 'alert alert-danger');
            header('Location: ' . URLROOT . '/expenses');
            exit;
        }

        // Only editable if pending or rejected
        if ($expense->status != 'En attente' && $expense->status != 'Rejeté') {
            flash('expense_message', 'Cette dépense ne peut plus être modifiée', 'alert alert-danger');
            header('Location: ' . URLROOT . '/expenses');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $receipt_path = $expense->receipt_path;
            
            if (isset($_FILES['receipt']['name']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
                $file_tmp_path = $_FILES['receipt']['tmp_name'];
                $file_name = $_FILES['receipt']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_name = uniqid('', true) . '.' . $file_ext;
                $upload_dir = 'public/uploads/receipts/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $dest_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp_path, $dest_path)) {
                    $receipt_path = str_replace('public/', '', $dest_path);
                }
            }

            $mission_id = !empty($_POST['mission_id']) ? $_POST['mission_id'] : null;
            $budget_detail_id = !empty($_POST['budget_detail_id']) ? $_POST['budget_detail_id'] : null;
            $category = $_POST['category'];

            $data = [
                'id' => $id,
                'mission_id' => $mission_id,
                'budget_item_id' => null,
                'budget_detail_id' => $budget_detail_id,
                'category' => $category,
                'amount' => $_POST['amount'],
                'currency' => $_POST['currency'] ?? 'USD',
                'description' => trim($_POST['description']),
                'expense_date' => $_POST['expense_date'],
                'expense_date_end' => !empty($_POST['expense_date_end']) ? $_POST['expense_date_end'] : null,
                'receipt_path' => $receipt_path
            ];

            if ($this->expenseModel->updateExpense($data)) {
                flash('expense_message', 'Dépense mise à jour avec succès');
                header('Location: ' . URLROOT . '/expenses');
                exit;
            } else {
                die('Erreur lors de la mise à jour');
            }
        } else {
            $missions = $this->missionModel->getMissionsByTenant($_SESSION['tenant_id']);
            $rubrics = $expense->mission_id ? $this->missionModel->getAllMissionBudgetDetails($expense->mission_id) : [];
            $admin_charges = $this->missionModel->getChargesByCategory($_SESSION['tenant_id'], 'Administrative');

            $data = [
                'title' => 'Modifier la dépense',
                'expense' => $expense,
                'missions' => $missions,
                'rubrics' => $rubrics,
                'admin_charges' => $admin_charges
            ];

            $this->view('expenses/edit', $data);
        }
    }

    public function delete($id) {
        $expense = $this->expenseModel->getExpenseById($id);

        if (!$expense || $expense->employee_id != $_SESSION['employee_id']) {
            flash('expense_message', 'Accès non autorisé', 'alert alert-danger');
            header('Location: ' . URLROOT . '/expenses');
            exit;
        }

        if ($expense->status != 'En attente' && $expense->status != 'Rejeté') {
            flash('expense_message', 'Cette dépense ne peut plus être supprimée', 'alert alert-danger');
            header('Location: ' . URLROOT . '/expenses');
            exit;
        }

        if ($this->expenseModel->deleteExpense($id)) {
            flash('expense_message', 'Dépense supprimée avec succès');
        } else {
            flash('expense_message', 'Erreur lors de la suppression', 'alert alert-danger');
        }
        header('Location: ' . URLROOT . '/expenses');
        exit;
    }
}
