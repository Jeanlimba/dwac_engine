<?php

/**
 * Contrôleur de base.
 *
 * Fournit le chargement des modèles/vues ainsi que des helpers d'autorisation
 * centralisés (requireLogin, requireRole, etc.) afin que chaque contrôleur n'ait
 * plus à dupliquer la même logique de garde d'accès dans son constructeur.
 */
class Controller {

    /**
     * Charge et instancie un modèle.
     *
     * @param string $model Nom du modèle (fichier app/Models/<model>.php).
     * @return object
     */
    public function model($model) {
        require_once APPROOT . '/Models/' . $model . '.php';
        return new $model();
    }

    /**
     * Rend une vue.
     *
     * @param string $view Chemin de la vue (sans extension) sous app/Views/.
     * @param array  $data Données passées à la vue.
     * @return void
     */
    public function view($view, $data = []) {
        if (file_exists(APPROOT . '/Views/' . $view . '.php')) {
            require_once APPROOT . '/Views/' . $view . '.php';
        } else {
            die('View does not exist');
        }
    }

    /* =====================================================================
     * Helpers d'autorisation
     * ---------------------------------------------------------------------
     * Chaque helper interrompt la requête (redirection + exit) si la
     * condition d'accès n'est pas remplie. À appeler en tête de constructeur
     * ou de méthode. Centralise une logique auparavant copiée dans chaque
     * contrôleur.
     * ===================================================================== */

    /** L'utilisateur courant est-il super-admin ? */
    protected function isSuperAdmin() {
        return !empty($_SESSION['is_super_admin']);
    }

    /** L'utilisateur courant est-il rattaché à un employé (vs admin pur) ? */
    protected function isEmployee() {
        return isset($_SESSION['employee_id']) && $_SESSION['employee_id'] !== null;
    }

    /** Identifiant du tenant courant (ou null). */
    protected function currentTenantId() {
        return $_SESSION['tenant_id'] ?? null;
    }

    /** Identifiant de l'utilisateur courant (ou null). */
    protected function currentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /** Redirige vers une route de l'application puis stoppe l'exécution. */
    protected function redirectTo($path) {
        header('Location: ' . URLROOT . '/' . ltrim($path, '/'));
        exit;
    }

    /** Exige une session authentifiée, sinon redirige vers la page de connexion. */
    protected function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirectTo('auth');
        }
    }

    /** Exige un super-admin connecté, sinon renvoie au tableau de bord. */
    protected function requireSuperAdmin() {
        $this->requireLogin();
        if (!$this->isSuperAdmin()) {
            $this->redirectTo('dashboard');
        }
    }

    /**
     * Réserve l'espace aux comptes « tenant » : refuse les super-admins
     * (qui ont leur propre espace). À utiliser après requireLogin.
     */
    protected function denySuperAdmin() {
        if ($this->isSuperAdmin()) {
            $this->redirectTo('dashboard');
        }
    }

    /**
     * Exige un administrateur de tenant : connecté, non super-admin et non
     * rattaché à un employé.
     */
    protected function requireTenantAdmin() {
        $this->requireLogin();
        if ($this->isEmployee() || $this->isSuperAdmin()) {
            $this->redirectTo('dashboard');
        }
    }

    /**
     * Exige que le rôle de l'utilisateur figure dans la liste fournie.
     *
     * @param string[] $roles Rôles autorisés (ex: ['superviseur','manager']).
     */
    protected function requireRole(array $roles) {
        $this->requireLogin();
        $role = $_SESSION['user_role'] ?? null;
        if (!in_array($role, $roles, true)) {
            $this->redirectTo('dashboard');
        }
    }

    /**
     * Exige une requête POST. À placer en tête des actions destructives
     * (suppression, changement d'état) : couplé à la vérification CSRF globale
     * du front controller, cela empêche le déclenchement via un simple lien GET.
     */
    protected function requirePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Méthode non autorisée.');
        }
    }

    /**
     * Désactive une route / un module : renvoie une page 404 et stoppe.
     * Utilisé pour rendre une fonctionnalité inaccessible même par URL directe
     * (ex. module Dépenses masqué). Facilement réversible en retirant l'appel.
     */
    protected function featureDisabled() {
        http_response_code(404);
        $data = ['title' => 'Indisponible', 'notifications' => []];
        if (file_exists(APPROOT . '/Views/errors/404.php')) {
            require APPROOT . '/Views/errors/404.php';
        } else {
            echo 'Fonctionnalité désactivée.';
        }
        exit;
    }
}
