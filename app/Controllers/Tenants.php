<?php
class Tenants extends Controller {
    private $tenantModel;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || !$_SESSION['is_super_admin']) {
            header('Location: ' . URLROOT . '/dashboard');
            exit;
        }

        $this->tenantModel = $this->model('Tenant');
    }

    public function index() {
        $tenants = $this->tenantModel->getTenants();
        $data = [
            'title' => 'Gestion des Entreprises',
            'tenants' => $tenants
        ];
        $this->view('tenants/index', $data);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'name' => trim($_POST['name']),
                'acronym' => trim($_POST['acronym']),
                'address' => trim($_POST['address']),
                'phone' => trim($_POST['phone'])
            ];

            if ($this->tenantModel->create($data)) {
                header('Location: ' . URLROOT . '/tenants');
            } else {
                die('Erreur lors de la création');
            }
        } else {
            $data = ['title' => 'Ajouter une Entreprise'];
            $this->view('tenants/create', $data);
        }
    }

    public function edit($id) {
        $tenant = $this->tenantModel->getTenantById($id);
        if (!$tenant) die('Entreprise non trouvée');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => $id,
                'name' => trim($_POST['name']),
                'acronym' => trim($_POST['acronym']),
                'address' => trim($_POST['address']),
                'phone' => trim($_POST['phone'])
            ];

            if ($this->tenantModel->update($data)) {
                header('Location: ' . URLROOT . '/tenants');
            } else {
                die('Erreur lors de la modification');
            }
        } else {
            $data = [
                'title' => 'Modifier l\'Entreprise',
                'tenant' => $tenant
            ];
            $this->view('tenants/edit', $data);
        }
    }

    public function delete($id) {
        if ($this->tenantModel->delete($id)) {
            header('Location: ' . URLROOT . '/tenants');
        } else {
            die('Erreur lors de la suppression');
        }
    }
}
