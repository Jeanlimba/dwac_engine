<?php

/**
 * Module Présence (pointages biométriques ZKTeco).
 *
 * Partie "consultation" : tableau de bord du jour, rapports et détails,
 * filtrés par tenant. La collecte depuis la pointeuse (enrôlement / sync) se
 * fait côté local et alimente la table `pointages` via l'API d'ingestion.
 */
class Attendance extends Controller {
    private $pointageModel;
    private $employeeModel;

    public function __construct() {
        // Réservé aux gestionnaires du tenant : admin de tenant OU superviseur/manager.
        $this->requireLogin();
        $this->denySuperAdmin();

        $isAdmin = !$this->isEmployee();
        $isPrivileged = in_array($_SESSION['user_role'] ?? '', ['superviseur', 'manager'], true);
        if (!$isAdmin && !$isPrivileged) {
            $this->redirectTo('dashboard');
        }

        $this->pointageModel = $this->model('Pointage');
        $this->employeeModel = $this->model('Employee');
    }

    /** Tableau de bord : pointages du jour. */
    public function index() {
        $data = [
            'title' => 'Présence',
            'logs'  => $this->pointageModel->getTodayByTenant($_SESSION['tenant_id']),
        ];
        $this->view('attendance/index', $data);
    }

    /** Rapports : synthèse journalière filtrable. */
    public function report() {
        $employeeId = $_GET['employee_id'] ?? null;
        $startDate  = $_GET['start_date'] ?? null;
        $endDate    = $_GET['end_date'] ?? null;

        $data = [
            'title'     => 'Rapports de présence',
            'employees' => $this->employeeModel->getEmployeesByTenant($_SESSION['tenant_id']),
            'results'   => $this->pointageModel->getDailySummary($_SESSION['tenant_id'], $employeeId, $startDate, $endDate),
            'filters'   => ['employee_id' => $employeeId, 'start_date' => $startDate, 'end_date' => $endDate],
        ];
        $this->view('attendance/report', $data);
    }

    /** Détail JSON des passages d'un employé un jour donné (tenant-scopé : anti-IDOR). */
    public function details() {
        header('Content-Type: application/json');
        $employeeId = $_GET['employee_id'] ?? null;
        $date = $_GET['date'] ?? null;

        if (!$employeeId || !$date) {
            echo json_encode([]);
            exit;
        }

        $logs = $this->pointageModel->getDayDetail($_SESSION['tenant_id'], $employeeId, $date);
        $out = [];
        foreach ($logs as $log) {
            $out[] = [
                'heure' => date('H:i:s', strtotime($log->date_heure)),
                'type_pointage' => (int) $log->type_pointage,
            ];
        }
        echo json_encode($out);
        exit;
    }
}
