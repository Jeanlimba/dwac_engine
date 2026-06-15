<?php
class Timesheets extends Controller {
    private $timesheetModel;
    private $missionModel;
    private $employeeModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth');
            exit;
        }

        $this->timesheetModel = $this->model('Timesheet');
        $this->missionModel = $this->model('Mission');
        $this->employeeModel = $this->model('Employee');
    }

    public function index() {
        if (!isset($_SESSION['employee_id'])) {
            die("Accès réservé aux employés");
        }

        $week_offset = isset($_GET['week']) ? (int)$_GET['week'] : 0;
        
        $monday = new DateTime();
        $monday->setISODate((int)$monday->format('o'), (int)$monday->format('W') + $week_offset);
        $monday->setTime(0, 0);
        
        $saturday = clone $monday;
        $saturday->modify('+5 days');
        $saturday->setTime(23, 59, 59);

        // We still fetch until Sunday for overtime detection if any work was done on Sunday
        $sunday = clone $monday;
        $sunday->modify('+6 days');
        $sunday->setTime(23, 59, 59);

        $entries = $this->timesheetModel->getByEmployeeAndWeek($_SESSION['employee_id'], $monday->format('Y-m-d'), $sunday->format('Y-m-d'));
        
        $missions = $this->missionModel->getMissionsByTenant($_SESSION['tenant_id']);

        $data = [
            'title' => 'Mon Timesheet',
            'monday' => $monday,
            'saturday' => $saturday,
            'sunday' => $sunday,
            'week_offset' => $week_offset,
            'entries' => $entries,
            'missions' => $missions
        ];

        $this->view('timesheets/index', $data);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            
            $id = !empty($_POST['id']) ? $_POST['id'] : null;
            
            $data = [
                'tenant_id' => $_SESSION['tenant_id'],
                'employee_id' => $_SESSION['employee_id'],
                'date' => $_POST['date'],
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'],
                'category' => $_POST['category'],
                'mission_id' => !empty($_POST['mission_id']) ? $_POST['mission_id'] : null,
                'custom_mission_name' => trim($_POST['custom_mission_name']),
                'task_description' => trim($_POST['task_description']),
                'status' => 'soumis' // Auto-submit on save for now, or could be draft
            ];

            if ($id) {
                $success = $this->timesheetModel->update($id, $data);
            } else {
                $success = $this->timesheetModel->add($data);
            }

            echo json_encode(['success' => $success]);
            exit;
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            $success = $this->timesheetModel->delete($id, $_SESSION['tenant_id']);
            echo json_encode(['success' => $success]);
            exit;
        }
    }

    public function pending() {
        if ($_SESSION['user_role'] !== 'superviseur' && $_SESSION['user_role'] !== 'manager') {
            die("Accès refusé");
        }

        $pending = $this->timesheetModel->getPendingByTenant($_SESSION['tenant_id']);

        $data = [
            'title' => 'Validations en attente',
            'pending' => $pending
        ];

        $this->view('timesheets/pending', $data);
    }

    public function validate() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_SESSION['user_role'] === 'superviseur' || $_SESSION['user_role'] === 'manager')) {
            header('Content-Type: application/json');
            $id = $_POST['id'];
            $rating = $_POST['rating'];
            $success = $this->timesheetModel->validate($id, $rating, $_SESSION['user_id']);
            echo json_encode(['success' => $success]);
            exit;
        }
    }

    public function reject() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_SESSION['user_role'] === 'superviseur' || $_SESSION['user_role'] === 'manager')) {
            header('Content-Type: application/json');
            $id = $_POST['id'];
            $reason = $_POST['rejection_reason'];
            $success = $this->timesheetModel->reject($id, $reason, $_SESSION['user_id']);
            echo json_encode(['success' => $success]);
            exit;
        }
    }

    public function reports() {
        if ($_SESSION['user_role'] !== 'superviseur' && $_SESSION['user_role'] !== 'manager') {
            die("Accès refusé");
        }

        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('monday this week'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('sunday this week'));

        $performance = $this->timesheetModel->getTenantPerformance($_SESSION['tenant_id'], $start_date, $end_date);
        $detailed = $this->timesheetModel->getDetailedReport($_SESSION['tenant_id'], $start_date, $end_date);

        $data = [
            'title' => 'Rapports de Performance',
            'start_date' => $start_date,
            'end_date' => $end_date,
            'performance' => $performance,
            'detailed' => $detailed
        ];

        $this->view('timesheets/reports', $data);
    }
}
