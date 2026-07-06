<?php

/**
 * Consultation du journal d'audit.
 * Super-admin : toutes les entrées. Admin de tenant : celles de son tenant.
 * Interdit aux employés simples.
 */
class Auditlog extends Controller {
    private $logModel;

    public function __construct() {
        $this->requireLogin();
        // Super-admin OU administrateur de tenant (non rattaché à un employé).
        if (!$this->isSuperAdmin() && $this->isEmployee()) {
            $this->redirectTo('dashboard');
        }
        $this->logModel = $this->model('ActionLog');
    }

    public function index() {
        $perPage = 50;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        // null => le super-admin voit tous les tenants ; sinon on filtre.
        $tenantId = $this->isSuperAdmin() ? null : $this->currentTenantId();

        $data = [
            'title'   => "Journal d'audit",
            'logs'    => $this->logModel->getRecent($tenantId, $perPage, $offset),
            'total'   => $this->logModel->countAll($tenantId),
            'page'    => $page,
            'perPage' => $perPage,
            'is_super' => $this->isSuperAdmin(),
        ];
        $this->view('auditlog/index', $data);
    }
}
