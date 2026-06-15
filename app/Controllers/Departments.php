<?php
class Departments extends Controller {
    private $departmentModel;
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth');
            exit;
        }

        // Restriction Employé et Super Admin
        if ((isset($_SESSION['employee_id']) && $_SESSION['employee_id'] !== null) || $_SESSION['is_super_admin']) {
            header('Location: ' . URLROOT . '/dashboard');
            exit;
        }

        $this->departmentModel = $this->model('Department');
    }

    public function index() {
        $departments = $this->departmentModel->getDepartmentsByTenant($_SESSION['tenant_id']);
        
        $data = [
            'title' => 'Affectations',
            'departments' => $departments
        ];

        $this->view('departments/index', $data);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'tenant_id' => $_SESSION['tenant_id'],
                'name' => trim($_POST['name']),
                'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : NULL
            ];

            if ($this->departmentModel->add($data)) {
                $_SESSION['success_message'] = "Entité d'affectation créée avec succès.";
                header('Location: ' . URLROOT . '/departments');
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la création.';
                header('Location: ' . URLROOT . '/departments');
            }
        }
    }

    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => $id,
                'tenant_id' => $_SESSION['tenant_id'],
                'name' => trim($_POST['name']),
                'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : NULL
            ];

            if ($this->departmentModel->update($data)) {
                $_SESSION['success_message'] = "Entité d'affectation mise à jour avec succès.";
                header('Location: ' . URLROOT . '/departments');
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la mise à jour.';
                header('Location: ' . URLROOT . '/departments');
            }
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tenant_id = $_SESSION['tenant_id'];

            if ($this->departmentModel->hasEmployees($id, $tenant_id)) {
                $_SESSION['error_message'] = 'Impossible de supprimer : des employés sont associés.';
            } elseif ($this->departmentModel->hasChildren($id, $tenant_id)) {
                $_SESSION['error_message'] = 'Impossible de supprimer : possède des sous-entités.';
            } else {
                if ($this->departmentModel->delete($id, $tenant_id)) {
                    $_SESSION['success_message'] = "Entité d'affectation supprimée.";
                } else {
                    $_SESSION['error_message'] = 'Erreur lors de la suppression.';
                }
            }
            header('Location: ' . URLROOT . '/departments');
        }
    }
}
