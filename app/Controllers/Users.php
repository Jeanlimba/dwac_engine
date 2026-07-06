<?php
class Users extends Controller {
    private $userModel;
    private $tenantModel;

    public function __construct() {
        $this->requireLogin();

        // Réservé aux super-admins OU aux administrateurs de tenant (non-employés).
        if (!$this->isSuperAdmin() && $this->isEmployee()) {
            $this->redirectTo('dashboard');
        }

        $this->userModel = $this->model('User');
        $this->tenantModel = $this->model('Tenant');
    }

    public function index() {
        if ($_SESSION['is_super_admin']) {
            $users = $this->userModel->getAllUsers();
        } else {
            $users = $this->userModel->getUsersByTenant($_SESSION['tenant_id']);
        }

        $data = [
            'title' => 'Gestion des Utilisateurs',
            'users' => $users
        ];

        $this->view('users/index', $data);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tenant_id = $_SESSION['is_super_admin'] ? $_POST['tenant_id'] : $_SESSION['tenant_id'];
            $data = [
                'username' => trim($_POST['username']),
                'password' => $_POST['password'],
                'tenant_id' => !empty($tenant_id) ? $tenant_id : null,
                'employee_id' => !empty($_POST['employee_id']) ? $_POST['employee_id'] : null,
                'is_super_admin' => $_SESSION['is_super_admin'] && isset($_POST['is_super_admin']) ? 1 : 0,
                'status' => 'active'
            ];

            if ($this->userModel->create($data)) {
                audit_log('user.create', 'Utilisateur : ' . ($data['username'] ?? '?'));
                header('Location: ' . URLROOT . '/users');
            } else {
                die('Erreur lors de la création');
            }
        } else {
            $tenants = $_SESSION['is_super_admin'] ? $this->tenantModel->getTenants() : [];
            $employees = [];
            if (!$_SESSION['is_super_admin']) {
                $employeeModel = $this->model('Employee');
                $employees = $employeeModel->getEmployeesByTenant($_SESSION['tenant_id']);
            }

            $data = [
                'title' => 'Ajouter un Utilisateur',
                'tenants' => $tenants,
                'employees' => $employees
            ];
            $this->view('users/create', $data);
        }
    }

    public function edit($id) {
        $user = $this->userModel->getUserById($id);

        if (!$user) {
            die('Utilisateur non trouvé');
        }

        // Check permissions
        if (!$_SESSION['is_super_admin'] && $user->tenant_id != $_SESSION['tenant_id']) {
            die('Accès non autorisé');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tenant_id = $_SESSION['is_super_admin'] ? $_POST['tenant_id'] : $user->tenant_id;
            $data = [
                'id' => $id,
                'username' => trim($_POST['username']),
                'tenant_id' => !empty($tenant_id) ? $tenant_id : null,
                'status' => $_POST['status']
            ];

            if ($this->userModel->update($data)) {
                // Update password if provided
                if (!empty($_POST['password'])) {
                    $this->userModel->updatePassword($id, $_POST['password']);
                }
                header('Location: ' . URLROOT . '/users');
            } else {
                die('Erreur lors de la mise à jour');
            }
        } else {
            $tenants = $_SESSION['is_super_admin'] ? $this->tenantModel->getTenants() : [];
            $data = [
                'title' => 'Modifier l\'utilisateur',
                'user' => $user,
                'tenants' => $tenants
            ];
            $this->view('users/edit', $data);
        }
    }

    public function delete($id) {
        $this->requirePost();
        $user = $this->userModel->getUserById($id);
        if (!$user) die('Utilisateur non trouvé');

        if (!$_SESSION['is_super_admin'] && $user->tenant_id != $_SESSION['tenant_id']) {
            die('Accès non autorisé');
        }

        if ($this->userModel->delete($id)) {
            audit_log('user.delete', 'Utilisateur #' . $id . ' (' . ($user->username ?? '?') . ')');
            header('Location: ' . URLROOT . '/users');
        } else {
            die('Erreur lors de la suppression');
        }
    }

    public function toggle($id) {
        $this->requirePost();
        $user = $this->userModel->getUserById($id);
        if (!$user) die('Utilisateur non trouvé');

        if (!$_SESSION['is_super_admin'] && $user->tenant_id != $_SESSION['tenant_id']) {
            die('Accès non autorisé');
        }

        if ($this->userModel->toggleStatus($id)) {
            audit_log('user.toggle_status', 'Utilisateur #' . $id . ' (' . ($user->username ?? '?') . ')');
            header('Location: ' . URLROOT . '/users');
        } else {
            die('Erreur lors du changement de statut');
        }
    }
}
