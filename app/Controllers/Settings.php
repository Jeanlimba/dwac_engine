<?php
class Settings extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth');
            exit;
        }

        // Seuls les admins de tenant (non-employés, non super-admins) peuvent accéder à la configuration
        if ((isset($_SESSION['employee_id']) && $_SESSION['employee_id'] !== null) || $_SESSION['is_super_admin']) {
            header('Location: ' . URLROOT . '/dashboard');
            exit;
        }
    }

    public function index() {
        header('Location: ' . URLROOT . '/settings/charges');
        exit;
    }

    public function charges() {
        $data = [
            'title' => 'Configuration des Charges'
        ];

        $this->view('settings/charges', $data);
    }
}
