<?php
class Employees extends Controller {
    private $employeeModel;
    public function __construct() {
        // Protection de la route
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth');
            exit;
        }

        if ($_SESSION['is_super_admin']) {
            header('Location: ' . URLROOT . '/dashboard');
            exit;
        }

        $this->employeeModel = $this->model('Employee');
    }

    public function index() {
        // Seuls les admins, managers et superviseurs voient la liste complète
        $is_admin = !isset($_SESSION['employee_id']) || $_SESSION['employee_id'] === null;
        $is_privileged_role = isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'superviseur' || $_SESSION['user_role'] === 'manager');

        if (!$is_admin && !$is_privileged_role) {
            header('Location: ' . URLROOT . '/employees/details/' . $_SESSION['employee_id']);
            exit;
        }

        $employees = $this->employeeModel->getEmployeesByTenant($_SESSION['tenant_id']);
        
        $data = [
            'title' => 'Gestion des Employés',
            'employees' => $employees
        ];

        $this->view('employees/index', $data);
    }

    public function details($id) {
        // Sécurité IDOR : Un employé standard ne peut voir que sa propre fiche
        $is_admin = !isset($_SESSION['employee_id']) || $_SESSION['employee_id'] === null;
        $is_privileged_role = isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'superviseur' || $_SESSION['user_role'] === 'manager');

        if (!$is_admin && !$is_privileged_role && $_SESSION['employee_id'] != $id) {
            header('Location: ' . URLROOT . '/employees/details/' . $_SESSION['employee_id']);
            exit;
        }

        // Sécurité : Vérifier que l'employé appartient au tenant
        $employee = $this->employeeModel->getEmployeeById($id, $_SESSION['tenant_id']);

        if (!$employee) {
            die("Employé introuvable ou accès non autorisé.");
        }

        $data = [
            'title' => 'Fiche Employé',
            'employee' => $employee
        ];

        $this->view('employees/details', $data);
    }

    public function delete($id) {
        $is_admin = !isset($_SESSION['employee_id']) || $_SESSION['employee_id'] === null;
        $is_privileged_role = isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'superviseur' || $_SESSION['user_role'] === 'manager');

        if (!$is_admin && !$is_privileged_role) {
            header('Location: ' . URLROOT . '/employees');
            exit;
        }

        if ($this->employeeModel->deleteEmployee($id, $_SESSION['tenant_id'])) {
            header('Location: ' . URLROOT . '/employees');
        } else {
            die('Une erreur est survenue.');
        }
    }
}
