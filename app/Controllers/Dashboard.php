<?php
class Dashboard extends Controller {
    private $employeeModel;
    private $missionModel;
    private $expenseModel;
    private $tenantModel;
    private $partnerModel;
    private $userModel;
    private $notificationModel;

    public function __construct() {
        $this->requireLogin();
        $this->employeeModel = $this->model('Employee');
        $this->missionModel = $this->model('Mission');
        $this->expenseModel = $this->model('Expense');
        $this->tenantModel = $this->model('Tenant');
        $this->partnerModel = $this->model('Partner');
        $this->userModel = $this->model('User');
        $this->notificationModel = $this->model('Notification');
    }

    public function index() {
        $data = ['title' => 'Tableau de bord'];

        if ($_SESSION['is_super_admin']) {
            $data['stats'] = [
                'tenants_count' => $this->tenantModel->countTenants(),
                'recent_tenants' => $this->tenantModel->getTenants(),
                'users_count' => count($this->userModel->getAllUsers())
            ];
            $this->view('dashboard/superadmin', $data);
        } else {
            $data['notifications'] = $this->notificationModel->getUnreadByUser($_SESSION['user_id']);
            // Encadrement (dashboard gestionnaire) : admin de tenant (sans
            // employee_id) OU rôle admin/manager/superviseur. Un employé simple
            // voit le dashboard personnel.
            $is_privileged = empty($_SESSION['employee_id'])
                || in_array($_SESSION['user_role'] ?? '', ['admin', 'manager', 'superviseur'], true);
            $tenant_id = $_SESSION['tenant_id'];

            if ($is_privileged) {
                // Admin Tenant / Manager / Supervisor View
                // Synthèse Timesheet de la semaine courante (lundi -> dimanche).
                $weekStart = date('Y-m-d', strtotime('monday this week'));
                $weekEnd   = date('Y-m-d', strtotime('sunday this week'));
                $tsModel = $this->model('Timesheet');
                $pending = $tsModel->getPendingByTenant($tenant_id);
                $performance = $tsModel->getTenantPerformance($tenant_id, $weekStart, $weekEnd);
                $weekHours = 0.0;
                foreach ($performance as $p) { $weekHours += (float) $p->total_hours; }

                $data['stats'] = [
                    'employees_count'       => $this->employeeModel->countEmployees($tenant_id),
                    'missions_count'        => $this->missionModel->countMissions($tenant_id),
                    'active_missions_count' => $this->missionModel->countActiveMissions($tenant_id),
                    'ts_pending_count'      => count($pending),
                    'ts_week_hours'         => $weekHours,
                ];
                $data['mission_status'] = $this->missionModel->getStatusBreakdown($tenant_id);
                $data['ts_performance']  = $performance;
                $data['ts_detailed']     = $tsModel->getDetailedReport($tenant_id, $weekStart, $weekEnd);
                $data['week_start']      = $weekStart;
                $data['week_end']        = $weekEnd;
                $this->view('dashboard/index', $data);
            } else {
                // Vue employé : synthèse de SA feuille de temps de la semaine.
                $employee_id = $_SESSION['employee_id'];
                $weekStart = date('Y-m-d', strtotime('monday this week'));
                $weekEnd   = date('Y-m-d', strtotime('sunday this week'));
                $tsModel = $this->model('Timesheet');
                $weekEntries = $tsModel->getByEmployeeAndWeek($employee_id, $weekStart, $weekEnd);

                $weekHours = 0.0; $pendingCount = 0;
                foreach ($weekEntries as $en) {
                    $weekHours += (strtotime($en->end_time) - strtotime($en->start_time)) / 3600;
                    if (($en->status ?? '') === 'soumis') $pendingCount++;
                }

                $data['stats'] = [
                    'ts_week_hours' => $weekHours,
                    'ts_pending'    => $pendingCount,
                ];
                // Saisies de l'année en cours pour la mini-grille annuelle.
                $year = (int) date('Y');
                $data['year']         = $year;
                $data['year_entries'] = $tsModel->getByEmployeeAndWeek($employee_id, "$year-01-01", "$year-12-31");

                $data['week_entries'] = $weekEntries;
                $data['week_start']   = $weekStart;
                $data['week_end']     = $weekEnd;
                $this->view('dashboard/employee', $data);
            }
        }
    }
}
