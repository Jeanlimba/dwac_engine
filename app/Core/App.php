<?php

/*
 * App Core Class
 * Construit l'URL et charge le contrôleur/méthode correspondants.
 * Format d'URL : /controleur/methode/param1/param2...
 */
class App {
    protected $currentController = 'Dashboard';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->getUrl();

        // --- Résolution du contrôleur (1er segment) ---
        // On refuse tout segment non strictement alphabétique pour éviter
        // de construire un nom de classe/fichier inattendu (path traversal, etc.).
        if ($url && isset($url[0]) && $url[0] !== '' && ctype_alpha($url[0])) {
            $candidate = ucwords($url[0]);
            if (file_exists(APPROOT . '/Controllers/' . $candidate . '.php')) {
                $this->currentController = $candidate;
                unset($url[0]);
            }
        }

        require_once APPROOT . '/Controllers/' . $this->currentController . '.php';
        $controllerName = $this->currentController;
        $this->currentController = new $this->currentController;

        // --- Résolution de la méthode (2e segment) ---
        // On n'autorise QUE les méthodes publiques déclarées sur le contrôleur
        // lui-même. Cela empêche d'appeler via l'URL les helpers hérités du
        // contrôleur de base (model, view, requireLogin, redirectTo...) ou des
        // méthodes magiques. Un 2e segment non valide est traité comme un
        // paramètre (comportement historique conservé).
        if (isset($url[1])) {
            if ($this->isCallableAction($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // Paramètres restants
        $this->params = $url ? array_values($url) : [];

        // Protection CSRF globale : toute requête POST routée par un contrôleur
        // doit porter un jeton valide (champ POST csrf_token ou en-tête
        // X-CSRF-Token pour l'AJAX). Exception : Externalged, dépôt PUBLIC
        // protégé par un jeton d'URL et sans session utilisateur.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $controllerName !== 'Externalged') {
            csrf_check_or_die();
        }

        // Appel de l'action avec ses paramètres
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    /**
     * Détermine si une action est appelable depuis l'URL : méthode publique,
     * non statique, non magique, et déclarée DANS la classe du contrôleur
     * (et non héritée du contrôleur de base).
     *
     * @param object $controller Instance du contrôleur courant.
     * @param string $method     Nom de méthode candidat issu de l'URL.
     * @return bool
     */
    protected function isCallableAction($controller, $method) {
        if ($method === '' || strncmp($method, '__', 2) === 0) {
            return false;
        }
        if (!method_exists($controller, $method)) {
            return false;
        }
        try {
            $ref = new ReflectionMethod($controller, $method);
        } catch (ReflectionException $e) {
            return false;
        }
        return $ref->isPublic()
            && !$ref->isStatic()
            && $ref->getDeclaringClass()->getName() === get_class($controller);
    }

    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
    }
}
