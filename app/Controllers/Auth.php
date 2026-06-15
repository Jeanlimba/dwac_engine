<?php
class Auth extends Controller {
    private $userModel;
    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function index() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            $loggedInUser = $this->userModel->login($username, $password);

            if ($loggedInUser === 'blocked') {
                $_SESSION['login_error'] = 'Votre compte est bloqué. Veuillez contacter l\'administrateur.';
                $this->view('auth/login');
            } elseif ($loggedInUser) {
                // Create Session
                $this->createUserSession($loggedInUser);
            } else {
                $_SESSION['login_error'] = 'Identifiants incorrects';
                $this->view('auth/login');
            }
        } else {
            // Init data
            $this->view('auth/login');
        }
    }

    public function createUserSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['tenant_id'] = $user->tenant_id;
        $_SESSION['employee_id'] = $user->employee_id;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['is_super_admin'] = (bool)$user->is_super_admin;
        $_SESSION['user_firstname'] = $user->prenom ?? '';
        $_SESSION['user_lastname'] = $user->nom ?? '';
        $_SESSION['user_photo'] = $user->photo ?? '';

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
