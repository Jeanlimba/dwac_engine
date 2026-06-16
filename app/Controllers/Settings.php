<?php
class Settings extends Controller {
    public function __construct() {
        // Réservé aux administrateurs de tenant (non-employés, non super-admins).
        $this->requireTenantAdmin();
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
