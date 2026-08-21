<?php

/**
 * Échappement HTML pour toute sortie de données non fiables (BD, session,
 * requête) dans une vue. Raccourci standard : <?= e($valeur) ?>.
 * ENT_QUOTES échappe aussi les apostrophes (attributs à quotes simples).
 *
 * @param mixed $value
 * @return string
 */
function e($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Initialize session securely.
 */
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set session cookie parameters for better security
        $cookieParams = session_get_cookie_params();
        $isSecure = false;
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) {
            $isSecure = true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $isSecure = true;
        }

        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path' => $cookieParams['path'],
            'domain' => $cookieParams['domain'],
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
    }

    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } else {
        $interval = 60 * 30; // 30 minutes
        if (time() - $_SESSION['last_regeneration'] > $interval) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    // Expiration par inactivité : au-delà de 2h sans requête, la session est
    // vidée (user_id disparaît => l'utilisateur est déconnecté).
    $idleTimeout = 2 * 60 * 60;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $idleTimeout)) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Check if a user is logged in.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Journal d'audit : enregistre une action sensible.
 * Lit l'utilisateur/tenant courant en session et l'IP. TOUJOURS défensif :
 * une erreur d'audit ne doit jamais interrompre l'action métier.
 *
 * @param string      $action  Code court de l'action (ex: 'login', 'user.delete').
 * @param string|null $details Détail lisible (ex: cible, valeurs).
 * @return void
 */
function audit_log($action, $details = null) {
    try {
        require_once APPROOT . '/Models/ActionLog.php';
        $log = new ActionLog();
        $log->record([
            'user_id'    => $_SESSION['user_id'] ?? null,
            'tenant_id'  => $_SESSION['tenant_id'] ?? null,
            'action'     => $action,
            'details'    => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (\Throwable $e) {
        error_log('[audit] échec journalisation: ' . $e->getMessage());
    }
}

/**
 * Détermine si l'hôte HTTP correspond à un environnement local de développement.
 * Tolère un port (ex: "localhost:9911") et les TLD de dev usuels. Sert à la fois
 * à la sélection de la base et à l'affichage des erreurs — un hôte de production
 * réel ne correspondra jamais à ces motifs.
 *
 * @param string $host Valeur de $_SERVER['HTTP_HOST'].
 * @return bool
 */
function is_local_host($host) {
    $host = strtolower((string) $host);
    // Retirer le port éventuel (localhost:9911 -> localhost).
    if (($pos = strpos($host, ':')) !== false) {
        $host = substr($host, 0, $pos);
    }
    if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
        return true;
    }
    foreach (['.test', '.local', '.localhost'] as $suffix) {
        if (substr($host, -strlen($suffix)) === $suffix) {
            return true;
        }
    }
    return false;
}

/**
 * Redirect to a specific page.
 */
function redirect($page) {
    header('location: ' . URLROOT . '/' . $page);
    exit;
}

/**
 * Load environment variables from a .env file.
 */
function load_env($path) {
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
    return true;
}

/**
 * Clean a string: remove accents, spaces, and special characters.
 */
function clean_string($string) {
    $string = str_replace(
        array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'â', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý'),
        array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y'),
        $string
    );
    $string = preg_replace('/[^A-Za-z0-9]/', '', $string);
    return strtolower($string);
}

/**
 * Generate a unique username based on employee name and tenant acronym.
 */
function generate_unique_username($pdo, $prenom, $nom, $acronym, $tenant_id) {
    $base = clean_string($prenom) . '.' . clean_string($nom);
    $sigle = clean_string($acronym);

    $index = 1;
    $username = $base . "@" . $sigle;

    // Check if username already exists in this tenant
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND tenant_id = :tenant_id");
        $stmt->execute(['username' => $username, 'tenant_id' => $tenant_id]);
        if (!$stmt->fetch()) {
            return $username;
        }
        $username = $base . $index . "@" . $sigle;
        $index++;
    }
}

/* =========================================================================
 * SÉCURITÉ DES UPLOADS
 * -------------------------------------------------------------------------
 * Toute la validation des fichiers déposés passe par ces fonctions. Le but
 * est d'empêcher le dépôt d'un script exécutable (.php, .phtml...) qui, stocké
 * sous public/uploads/, pourrait sinon être appelé et exécuté à distance (RCE).
 * Défense en profondeur : liste blanche d'extensions + contrôle du MIME réel
 * + nom physique imprévisible, en complément du .htaccess de public/uploads/.
 * ========================================================================= */

/**
 * Liste blanche des extensions autorisées (documents RH, images, archives).
 * Tout ce qui n'est pas listé ici est refusé.
 *
 * @return string[]
 */
function allowed_upload_extensions() {
    return [
        // Documents bureautiques
        'pdf', 'doc', 'docx', 'docm', 'dot', 'dotx',
        'xls', 'xlsx', 'xlsm', 'xlsb', 'ppt', 'pptx', 'ppsx',
        'odt', 'ods', 'odp', 'csv', 'txt', 'rtf', 'md', 'xml', 'json',
        // Images (dont HEIC/HEIF des iPhone et TIFF des scanners)
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'heic', 'heif', 'tif', 'tiff',
        // Courriels
        'msg', 'eml',
        // Archives
        'zip', 'rar', '7z',
    ];
}

/**
 * Valide un fichier uploadé avant de l'enregistrer.
 *
 * Vérifie successivement : l'extension (liste blanche), l'absence d'extension
 * dangereuse cachée (double extension type "facture.php.pdf"), la taille, et
 * le type MIME réel détecté côté serveur (le type fourni par le navigateur
 * n'est PAS fiable). En cas de succès, fournit un nom physique aléatoire.
 *
 * @param string   $originalName Nom d'origine du fichier (côté client).
 * @param string   $tmpName      Chemin temporaire ($_FILES[...]['tmp_name']).
 * @param int|null $size         Taille en octets (null = non vérifiée).
 * @param int      $maxBytes     Taille max autorisée (défaut 20 Mo).
 * @return array{ok:bool,error:string,extension:string,mime:string,physical_name:string}
 */
function validate_upload($originalName, $tmpName, $size = null, $maxBytes = null) {
    $result = ['ok' => false, 'error' => '', 'extension' => '', 'mime' => '', 'physical_name' => ''];

    // Taille max applicative : configurable via MAX_UPLOAD_MB (.env), défaut 64 Mo.
    // NB : de toute façon bornée par upload_max_filesize / post_max_size du serveur.
    if ($maxBytes === null) {
        $maxBytes = (defined('MAX_UPLOAD_MB') ? (int) MAX_UPLOAD_MB : 64) * 1048576;
    }

    // 1. Extension présente et dans la liste blanche.
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($ext === '' || !in_array($ext, allowed_upload_extensions(), true)) {
        $result['error'] = "Type de fichier non autorisé : ." . ($ext ?: '?');
        return $result;
    }

    // 2. Refus des extensions de SCRIPT exécutables côté serveur, même
    //    dissimulées en double extension (ex : "rapport.phtml.pdf"). On ne
    //    bloque QUE les scripts réellement interprétés par le serveur web : on
    //    a retiré exe/com/bat/htaccess (binaires Windows non exécutés par le
    //    serveur) qui provoquaient des faux positifs — ex. "societe.com.pdf".
    $dangerous = ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phps',
                  'phar', 'pht', 'shtml', 'cgi', 'pl', 'py', 'sh', 'asp', 'aspx', 'jsp'];
    foreach (explode('.', strtolower($originalName)) as $segment) {
        if (in_array($segment, $dangerous, true)) {
            $result['error'] = "Nom de fichier interdit (extension exécutable détectée).";
            return $result;
        }
    }

    // 3. Taille.
    if ($size !== null && $maxBytes > 0 && $size > $maxBytes) {
        $result['error'] = "Fichier trop volumineux (max " . round($maxBytes / 1048576) . " Mo).";
        return $result;
    }

    // 4. Type MIME réel (lu sur le contenu, pas sur ce qu'annonce le client).
    $mime = '';
    if (is_file($tmpName) && function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = (string) finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        $blockedMime = [
            'text/x-php', 'application/x-php', 'application/x-httpd-php',
            'text/x-shellscript', 'application/x-executable',
            'application/x-dosexec', 'application/x-msdownload',
        ];
        if (in_array($mime, $blockedMime, true)) {
            $result['error'] = "Contenu de fichier non autorisé.";
            return $result;
        }
    }

    // 5. Validé : on génère un nom physique aléatoire et imprévisible
    //    (random_bytes plutôt que uniqid, qui est devinable car basé sur l'heure).
    $result['ok']            = true;
    $result['extension']     = $ext;
    $result['mime']          = $mime;
    $result['physical_name'] = bin2hex(random_bytes(16)) . '.' . $ext;
    return $result;
}

/**
 * Traduit un code d'erreur $_FILES[...]['error'] en message lisible.
 * Essentiel en hébergement mutualisé où un fichier trop lourd renvoie
 * UPLOAD_ERR_INI_SIZE (limite PHP) plutôt qu'un refus applicatif.
 *
 * @param int $code
 * @return string  Chaîne vide si aucune erreur (UPLOAD_ERR_OK).
 */
function upload_error_message($code) {
    switch ($code) {
        case UPLOAD_ERR_OK:        return '';
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE: return "Fichier trop volumineux pour le serveur (limite d'upload PHP dépassée).";
        case UPLOAD_ERR_PARTIAL:   return "Transfert interrompu : fichier incomplet.";
        case UPLOAD_ERR_NO_FILE:   return "Aucun fichier reçu.";
        case UPLOAD_ERR_NO_TMP_DIR:return "Erreur serveur : dossier temporaire manquant.";
        case UPLOAD_ERR_CANT_WRITE:return "Erreur serveur : écriture sur disque impossible.";
        case UPLOAD_ERR_EXTENSION: return "Import bloqué par une extension PHP du serveur.";
        default:                   return "Erreur d'upload (code " . (int) $code . ").";
    }
}

/**
 * Handle file upload safely.
 *
 * Désormais sécurisé via validate_upload() : extension en liste blanche,
 * contrôle MIME, nom physique aléatoire.
 *
 * @param string $file_key   The key in the $_FILES array.
 * @param string $upload_dir The destination directory (relative to root).
 * @return string|null The path to the uploaded file or null on failure/refus.
 */
function handle_upload($file_key, $upload_dir) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES[$file_key]['tmp_name'];
        $file_name     = $_FILES[$file_key]['name'];
        $file_size     = $_FILES[$file_key]['size'] ?? null;

        // Validation de sécurité avant tout déplacement.
        $check = validate_upload($file_name, $file_tmp_path, $file_size);
        if (!$check['ok']) {
            return null;
        }

        // Ensure upload directory exists (0755 : pas de droit d'écriture "monde").
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $dest_path = $upload_dir . $check['physical_name'];

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            return $dest_path;
        }
    }
    return null;
}

/* =========================================================================
 * PROTECTION CSRF
 * -------------------------------------------------------------------------
 * Un jeton unique par session est injecté dans chaque formulaire via
 * csrf_field() et vérifié côté serveur via csrf_check_or_die() au début de
 * chaque traitement POST. Cela empêche un site tiers de soumettre des
 * actions à l'insu de l'utilisateur connecté (Cross-Site Request Forgery).
 * ========================================================================= */

/**
 * Retourne le jeton CSRF de la session, en le générant au besoin.
 *
 * @return string
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Champ caché à insérer dans tout formulaire POST : <?= csrf_field() ?>
 *
 * @return string
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="'
         . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Vérifie le jeton CSRF reçu (champ POST ou en-tête X-CSRF-Token pour l'AJAX).
 * Utilise hash_equals pour une comparaison à temps constant.
 *
 * @return bool
 */
function csrf_verify() {
    $sent = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return !empty($_SESSION['csrf_token'])
        && is_string($sent)
        && hash_equals($_SESSION['csrf_token'], $sent);
}

/**
 * À appeler au début de chaque traitement POST sensible : interrompt la
 * requête si le jeton est absent ou invalide.
 *
 * @return void
 */
function csrf_check_or_die() {
    if (!csrf_verify()) {
        // 403 Forbidden (code standard, correctement émis par Apache/PHP-FPM,
        // contrairement au 419 non standard qui retombait en 500).
        http_response_code(403);
        die('Jeton de sécurité invalide ou expiré. Veuillez recharger la page et réessayer.');
    }
}

/**
 * Simple flash message function
 */
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!empty($name)) {
        if (!empty($message)) {
            if (!empty($_SESSION[$name])) {
                unset($_SESSION[$name]);
            }
            if (!empty($_SESSION[$name . '_class'])) {
                unset($_SESSION[$name . '_class']);
            }

            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            echo '<div class="' . $class . ' alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div>' . $_SESSION[$name] . '</div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                  </div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}
