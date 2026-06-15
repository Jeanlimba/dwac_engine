<?php

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
}

/**
 * Check if a user is logged in.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
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

/**
 * Handle file upload safely.
 * 
 * @param string $file_key The key in the $_FILES array.
 * @param string $upload_dir The destination directory (relative to root).
 * @return string|null The path to the uploaded file or null on failure.
 */
function handle_upload($file_key, $upload_dir) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES[$file_key]['tmp_name'];
        $file_name = $_FILES[$file_key]['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid('', true) . '.' . $file_ext;
        
        // Ensure upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            return $dest_path;
        }
    }
    return null;
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
