<?php
class Timesheets extends Controller {
    private $timesheetModel;
    private $missionModel;
    private $employeeModel;

    public function __construct() {
        $this->requireLogin();

        $this->timesheetModel = $this->model('Timesheet');
        $this->missionModel = $this->model('Mission');
        $this->employeeModel = $this->model('Employee');
    }

    public function index() {
        if (!isset($_SESSION['employee_id'])) {
            die("Accès réservé aux employés");
        }

        $view = $_GET['view'] ?? 'week';
        if (!in_array($view, ['day', 'week', 'month', 'year'], true)) $view = 'week';
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $selected_date = $_GET['date'] ?? date('Y-m-d');

        // Ancre = date sélectionnée ; l'offset décale par unité de la vue.
        $base = new DateTime($selected_date);

        switch ($view) {
            case 'day':
                if ($offset) $base->modify("$offset days");
                $start_date = clone $base;
                $end_date   = clone $base;
                break;
            case 'month':
                $start_date = new DateTime($base->format('Y-m-01'));
                if ($offset) $start_date->modify("$offset months");
                $end_date = clone $start_date;
                $end_date->modify('last day of this month');
                break;
            case 'year':
                $year = (int) $base->format('Y') + $offset;
                $start_date = new DateTime("$year-01-01");
                $end_date   = new DateTime("$year-12-31");
                break;
            case 'week':
            default:
                $start_date = clone $base;
                $start_date->modify('monday this week');
                if ($offset) $start_date->modify("$offset weeks");
                $end_date = clone $start_date;
                $end_date->modify('+6 days');
                break;
        }
        // La date "ancre" reflète la période affichée (cohérence du sélecteur de vue).
        $selected_date = $start_date->format('Y-m-d');

        // Fetch entries for the range
        $entries = $this->timesheetModel->getByEmployeeAndWeek(
            $_SESSION['employee_id'], 
            $start_date->format('Y-m-d'), 
            $end_date->format('Y-m-d')
        );
        
        $missions = $this->missionModel->getMissionsByTenant($_SESSION['tenant_id']);

        $data = [
            'title' => 'Mon Timesheet',
            'view' => $view,
            'offset' => $offset,
            'selected_date' => $selected_date,
            'start_date' => $start_date,
            'end_date' => $end_date,
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
                // Anti-IDOR intra-tenant : l'entrée doit appartenir à l'employé courant.
                $existing = $this->timesheetModel->getById($id, $_SESSION['tenant_id']);
                if (!$existing || $existing->employee_id != $_SESSION['employee_id']) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
                    exit;
                }
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
            // Anti-IDOR intra-tenant : l'entrée doit appartenir à l'employé courant.
            $existing = $this->timesheetModel->getById($id, $_SESSION['tenant_id']);
            if (!$existing || $existing->employee_id != $_SESSION['employee_id']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
                exit;
            }
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
            $success = $this->timesheetModel->validate($id, $rating, $_SESSION['user_id'], $_SESSION['tenant_id']);
            if ($success) audit_log('timesheet.validate', 'Feuille de temps #' . $id);
            echo json_encode(['success' => $success]);
            exit;
        }
    }

    public function reject() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_SESSION['user_role'] === 'superviseur' || $_SESSION['user_role'] === 'manager')) {
            header('Content-Type: application/json');
            $id = $_POST['id'];
            $reason = $_POST['rejection_reason'];
            $success = $this->timesheetModel->reject($id, $reason, $_SESSION['user_id'], $_SESSION['tenant_id']);
            if ($success) audit_log('timesheet.reject', 'Feuille de temps #' . $id);
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
