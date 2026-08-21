<?php
class Auth extends Controller {
    private $userModel;
    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function index() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Anti-brute-force : limiter les tentatives par IP sur une fenêtre
            // glissante (réutilise le journal d'audit 'login_failed').
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $logModel = $this->model('ActionLog');
            if ($logModel->countRecentFailures($ip, 15) >= 10) {
                audit_log('login_blocked', 'Trop de tentatives depuis ' . $ip);
                $_SESSION['login_error'] = "Trop de tentatives de connexion. Merci de patienter quelques minutes avant de réessayer.";
                $this->view('auth/login');
                return;
            }

            // Process form
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            $loggedInUser = $this->userModel->login($username, $password);

            if ($loggedInUser === 'blocked') {
                audit_log('login_failed', 'Compte bloqué : ' . $username);
                $_SESSION['login_error'] = 'Votre compte est bloqué. Veuillez contacter l\'administrateur.';
                $this->view('auth/login');
            } elseif ($loggedInUser) {
                // Create Session
                $this->createUserSession($loggedInUser);
            } else {
                audit_log('login_failed', 'Identifiants incorrects : ' . $username);
                $_SESSION['login_error'] = 'Identifiants incorrects';
                $this->view('auth/login');
            }
        } else {
            // Init data
            $this->view('auth/login');
        }
    }

    public function createUserSession($user) {
        // Anti-fixation de session : nouvel ID à l'élévation de privilège (login).
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['tenant_id'] = $user->tenant_id;
        $_SESSION['employee_id'] = $user->employee_id;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['is_super_admin'] = (bool)$user->is_super_admin;
        $_SESSION['user_firstname'] = $user->prenom ?? '';
        $_SESSION['user_lastname'] = $user->nom ?? '';
        $_SESSION['user_photo'] = $user->photo ?? '';

        audit_log('login'); // connexion réussie (session renseignée)

        // Assurer que l'espace GED existe
        if (!$_SESSION['is_super_admin']) {
            require_once '../app/Models/GedFolder.php';
            $gedModel = new GedFolder();
            $gedModel->ensureRootFolder($user->id, $user->tenant_id, $user->username);
        }
        
        if ($_SESSION['is_super_admin']) {
            header('Location: ' . URLROOT . '/dashboard/superadmin');
        } else {
            header('Location: ' . URLROOT . '/dashboard');
        }
        exit;
    }

    public function logout() {
        audit_log('logout'); // avant de vider la session (user_id encore présent)
        unset($_SESSION['user_id']);
        unset($_SESSION['tenant_id']);
        unset($_SESSION['employee_id']);
        unset($_SESSION['user_role']);
        unset($_SESSION['is_super_admin']);
        session_destroy();
        header('Location: ' . URLROOT . '/auth');
        exit;
    }
}
