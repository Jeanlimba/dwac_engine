<?php

/*
 * App Core Class
 * Creates URL & loads core controller
 * URL FORMAT - /controller/method/params
 */
class App {
    protected $currentController = 'Dashboard';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->getUrl();

        // Look in controllers for first value
        if ($url && file_exists(APPROOT . '/Controllers/' . ucwords($url[0]) . '.php')) {
            $this->currentController = ucwords($url[0]);
            unset($url[0]);
        }

        require_once APPROOT . '/Controllers/' . $this->currentController . '.php';
        $controllerName = $this->currentController;
        $this->currentController = new $this->currentController;

        // Check for second part of url
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // Get params
        $this->params = $url ? array_values($url) : [];

        // Protection CSRF globale : toute requête POST routée par un contrôleur
        // doit porter un jeton valide (champ POST csrf_token ou en-tête
        // X-CSRF-Token pour l'AJAX). Exception : Externalged, dépôt PUBLIC
        // protégé par un jeton d'URL et sans session utilisateur.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $controllerName !== 'Externalged') {
            csrf_check_or_die();
        }

        // Call a callback with array of params
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
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
