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
            $is_privileged = isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'superviseur' || $_SESSION['user_role'] === 'manager');
            $tenant_id = $_SESSION['tenant_id'];

            if ($is_privileged) {
                // Admin Tenant / Manager / Supervisor View
                $data['stats'] = [
                    'employees_count' => $this->employeeModel->countEmployees($tenant_id),
                    'missions_count' => $this->missionModel->countMissions($tenant_id),
                    'active_missions_count' => $this->missionModel->countActiveMissions($tenant_id),
                    'pending_expenses_count' => $this->expenseModel->countPendingExpenses($tenant_id),
                    'total_expenses' => $this->expenseModel->getTotalApprovedAmount($tenant_id),
                    'partners_count' => $this->partnerModel->countPartners($tenant_id)
                ];
                $this->view('dashboard/index', $data);
            } else {
                // Standard Employee View
                $employee_id = $_SESSION['employee_id'];
                $data['stats'] = [
                    'pending_expenses' => $this->expenseModel->countEmployeePendingExpenses($employee_id),
                    'approved_expenses_amount' => $this->expenseModel->getEmployeeTotalApprovedAmount($employee_id),
                    'recent_expenses' => $this->expenseModel->getEmployeeExpenses($employee_id)
                ];
                $this->view('dashboard/employee', $data);
            }
        }
    }
}
