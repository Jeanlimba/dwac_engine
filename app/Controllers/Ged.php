<?php
class Ged extends Controller {
    private $gedFolderModel;
    private $gedFileModel;

    public function __construct() {
        $this->requireLogin();
        $this->denySuperAdmin();

        $this->gedFolderModel = $this->model('GedFolder');
        $this->gedFileModel = $this->model('GedFile');
    }

    public function index() {
        $rootFolder = $this->gedFolderModel->getRootFolder($_SESSION['user_id']);
        
        if (!$rootFolder) {
            // Sécurité au cas où la session n'a pas déclenché la création
            $this->gedFolderModel->ensureRootFolder($_SESSION['user_id'], $_SESSION['tenant_id'], $_SESSION['username']);
            $rootFolder = $this->gedFolderModel->getRootFolder($_SESSION['user_id']);
        }

        $this->folder($rootFolder->id);
    }

    public function folder($id) {
        // Vérifier l'accès au dossier (Propriétaire ou Partagé)
        $folder = $this->gedFolderModel->getFolderById($id);
        
        if (!$folder || ($folder->user_id != $_SESSION['user_id'] && !$this->gedFolderModel->hasAccess($id, $_SESSION['user_id']))) {
            die("Accès refusé ou dossier inexistant.");
        }

        $orderBy = isset($_GET['sort']) ? $_GET['sort'] : 'name';
        $orderDir = isset($_GET['dir']) ? $_GET['dir'] : 'ASC';

        $userModel = $this->model('User');
        require_once '../app/Models/GedShare.php';
        $shareModel = new GedShare();

        $subfolders = $this->gedFolderModel->getSubfolders($id, $orderBy, $orderDir);
        $files = $this->gedFileModel->getFilesByFolder($id, $orderBy, $orderDir);

        // Si on est à la racine, on ajoute les éléments partagés
        $isRoot = $folder->is_root;
        $sharedFolders = [];
        if ($isRoot) {
            $sharedItems = $shareModel->getSharedWithMe($_SESSION['user_id']);
            foreach ($sharedItems as $item) {
                if ($item->folder_id) {
                    $sharedFolder = $this->gedFolderModel->getFolderById($item->folder_id);
                    if ($sharedFolder) {
                        $sharedFolder->is_shared_received = true;
                        $owner = $userModel->getUserById($item->shared_by_user_id);
                        $sharedFolder->shared_by_name = $owner ? $owner->username : 'Inconnu';
                        $sharedFolder->share_count = 0; // Pas pertinent pour le destinataire
                        $subfolders[] = $sharedFolder;
                    }
                }
            }
        }

        $data = [
            'title' => 'GED',
            'current_folder' => $folder,
            'subfolders' => $subfolders,
            'files' => $files,
            'breadcrumbs' => $this->gedFolderModel->getBreadcrumbs($id),
            'tenant_users' => $userModel->getUsersByTenant($_SESSION['tenant_id'], $_SESSION['user_id']),
            'sort' => $orderBy,
            'dir' => $orderDir
        ];

        $this->view('ged/index', $data);
    }

    public function search() {
        $term = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        if (empty($term)) {
            header('Location: ' . URLROOT . '/ged');
            exit;
        }

        $userModel = $this->model('User');

        $data = [
            'title' => 'Recherche GED',
            'is_search' => true,
            'search_term' => $term,
            'subfolders' => $this->gedFolderModel->searchFolders($_SESSION['user_id'], $term),
            'files' => $this->gedFileModel->searchFiles($_SESSION['user_id'], $term),
            'breadcrumbs' => [
                (object)['id' => '', 'name' => 'Résultats pour "' . $term . '"']
            ],
            'tenant_users' => $userModel->getUsersByTenant($_SESSION['tenant_id'], $_SESSION['user_id'])
        ];

        $this->view('ged/index', $data);
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;

        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

        // Mutualisé : si le POST dépasse post_max_size, PHP vide $_POST/$_FILES
        // alors que des données ont bien été envoyées (CONTENT_LENGTH > 0).
        if (empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $msg = "Le total des fichiers dépasse la limite du serveur (post_max_size). Réduisez la taille ou le nombre de fichiers, puis réessayez.";
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['reload' => false, 'errors' => [$msg]]); exit; }
            $_SESSION['ged_flash_errors'] = [$msg];
            header('Location: ' . URLROOT . '/ged');
            exit;
        }
        if (!isset($_FILES['files'])) {
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['reload' => false, 'errors' => ['Aucun fichier reçu.']]); exit; }
            header('Location: ' . URLROOT . '/ged');
            exit;
        }

        // (La vérification CSRF est assurée globalement par le front controller App.)
        $folderId = $_POST['parent_id'] ?? null;
        $targetDir = APPROOT . '/../public/uploads/ged/';
        // 0755 et non 0777 : le dossier ne doit pas être inscriptible par tous.
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $okCount = 0;
        $errors = [];

        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            $name = $_FILES['files']['name'][$key] ?? 'Fichier';
            $err  = $_FILES['files']['error'][$key] ?? UPLOAD_ERR_NO_FILE;

            // Erreur de transfert (taille serveur dépassée, transfert partiel…).
            if ($err !== UPLOAD_ERR_OK) {
                if ($err !== UPLOAD_ERR_NO_FILE) { // champ vide : on ignore en silence
                    $errors[] = $name . ' : ' . upload_error_message($err);
                }
                continue;
            }

            $size = $_FILES['files']['size'][$key];

            // Validation de sécurité : extension en liste blanche, MIME réel,
            // nom physique aléatoire.
            $check = validate_upload($name, $tmpName, $size);
            if (!$check['ok']) {
                $errors[] = $name . ' : ' . $check['error']; // retour explicite
                continue;
            }

            if (move_uploaded_file($tmpName, $targetDir . $check['physical_name'])) {
                $this->gedFileModel->addFile([
                    'folder_id'     => $folderId,
                    'user_id'       => $_SESSION['user_id'],
                    'name'          => $name,
                    'physical_name' => $check['physical_name'],
                    'size'          => $size,
                    'extension'     => $check['extension'],
                    'mime_type'     => $check['mime'], // MIME réel détecté, pas celui du client
                ]);
                $okCount++;
            } else {
                $errors[] = $name . " : échec de l'enregistrement du fichier.";
            }
        }

        // Retour AJAX (upload XHR avec barre de progression) : on recharge dès
        // qu'au moins un fichier est passé (le flash affiche succès + refus) ;
        // sinon on renvoie les erreurs pour affichage dans la modale.
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($okCount > 0) {
                $_SESSION['ged_flash_success'] = $okCount . ' fichier(s) importé(s) avec succès.';
                if ($errors) { $_SESSION['ged_flash_errors'] = $errors; }
                echo json_encode(['reload' => true]);
            } else {
                echo json_encode(['reload' => false, 'errors' => $errors]);
            }
            exit;
        }

        // Retour classique (formulaire POST sans JavaScript).
        if ($okCount > 0) { $_SESSION['ged_flash_success'] = $okCount . ' fichier(s) importé(s) avec succès.'; }
        if ($errors)      { $_SESSION['ged_flash_errors']  = $errors; }
        header('Location: ' . URLROOT . '/ged/folder/' . $folderId);
        exit;
    }

    public function createFolder() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $parentId = $_POST['parent_id'];

            if (!empty($name)) {
                $data = [
                    'tenant_id' => $_SESSION['tenant_id'],
                    'user_id' => $_SESSION['user_id'],
                    'name' => $name,
                    'parent_id' => $parentId
                ];

                if ($this->gedFolderModel->createFolder($data)) {
                    header('Location: ' . URLROOT . '/ged/folder/' . $parentId);
                } else {
                    die('Quelque chose s\'est mal passé');
                }
            }
        }
    }

    /* =====================================================================
     * Helpers de propriété (anti-IDOR)
     * ---------------------------------------------------------------------
     * Les actions GED reçoivent des identifiants depuis le client ; sans
     * contrôle, un utilisateur pourrait agir sur les dossiers/fichiers d'un
     * autre en devinant l'ID. Ces helpers vérifient la propriété avant action.
     * ===================================================================== */

    /** Le dossier doit appartenir à l'utilisateur courant, sinon 403. */
    private function ownedFolderOrDeny($folderId) {
        $folder = $this->gedFolderModel->getFolderById($folderId);
        if (!$folder || $folder->user_id != $_SESSION['user_id']) {
            $this->denyAccess();
        }
        return $folder;
    }

    /**
     * Le fichier doit se trouver dans un dossier appartenant à l'utilisateur
     * courant. Les fichiers déposés en externe ont user_id = null : on se
     * base donc sur le propriétaire du dossier parent.
     */
    private function ownedFileOrDeny($fileId) {
        $file = $this->gedFileModel->getFileById($fileId);
        if (!$file) {
            $this->denyAccess();
        }
        $this->ownedFolderOrDeny($file->folder_id);
        return $file;
    }

    /** Réponse 403 puis arrêt. */
    private function denyAccess() {
        http_response_code(403);
        die('Accès non autorisé.');
    }

    public function rename() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $type = $_POST['type']; // 'folder' ou 'file'
            $newName = trim($_POST['name']);
            $currentFolderId = $_POST['current_folder_id'];

            if (!empty($newName)) {
                if ($type === 'folder') {
                    $this->ownedFolderOrDeny($id);
                    $this->gedFolderModel->renameFolder($id, $newName);
                } else {
                    $this->ownedFileOrDeny($id);
                    $this->gedFileModel->renameFile($id, $newName);
                }
                header('Location: ' . URLROOT . '/ged/folder/' . $currentFolderId);
            }
        }
    }

    public function delete() {
        // Suppression réservée au POST : action destructive, donc protégée par
        // le jeton CSRF (vérifié globalement par le front controller sur les POST).
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->denyAccess();
        }

        $type = $_POST['type'] ?? '';
        $id = $_POST['id'] ?? null;
        $currentFolderId = $_POST['current_folder_id'] ?? '';

        if ($type === 'folder') {
            $this->ownedFolderOrDeny($id);
            $this->gedFolderModel->deleteFolder($id);
        } else {
            $this->ownedFileOrDeny($id);
            // Suppression physique et logique
            $file = $this->gedFileModel->getFileById($id);
            if ($file) {
                $path = "../public/uploads/ged/" . $file->physical_name;
                if (file_exists($path)) unlink($path);
                $this->gedFileModel->deleteFile($id);
            }
        }
        audit_log('ged.delete', $type . ' #' . $id);
        header('Location: ' . URLROOT . '/ged/folder/' . $currentFolderId);
        exit;
    }

    public function share() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $type = $_POST['type'];
            $itemId = $_POST['item_id'];
            $userIds = $_POST['user_ids']; // Tableau d'IDs
            $permission = $_POST['permission'];
            $currentFolderId = $_POST['current_folder_id'];

            // Seul le propriétaire de l'item peut le partager (anti-IDOR).
            if ($type === 'folder') {
                $this->ownedFolderOrDeny($itemId);
            } else {
                $this->ownedFileOrDeny($itemId);
            }

            require_once '../app/Models/GedShare.php';
            $shareModel = new GedShare();

            foreach ($userIds as $sharedWithId) {
                $data = [
                    'shared_by' => $_SESSION['user_id'],
                    'shared_with' => $sharedWithId,
                    'permission' => $permission
                ];
                if ($type === 'folder') {
                    $data['folder_id'] = $itemId;
                    $data['file_id'] = null;
                } else {
                    $data['file_id'] = $itemId;
                    $data['folder_id'] = null;
                }
                $shareModel->addShare($data);
            }
            header('Location: ' . URLROOT . '/ged/folder/' . $currentFolderId);
        }
    }

    public function revokeShare($shareId, $currentFolderId) {
        // POST uniquement (protégé CSRF par le front controller).
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->denyAccess();
        }
        require_once '../app/Models/GedShare.php';
        $shareModel = new GedShare();
        $share = $shareModel->getShareById($shareId);
        if (!$share) {
            $this->denyAccess();
        }
        // Seul le propriétaire de l'item partagé peut révoquer le partage.
        if ($share->folder_id) {
            $this->ownedFolderOrDeny($share->folder_id);
        } else {
            $this->ownedFileOrDeny($share->file_id);
        }
        $shareModel->revokeShare($shareId);
        header('Location: ' . URLROOT . '/ged/folder/' . $currentFolderId);
    }

    public function getShares($type, $id) {
        // Ne révèle les partages que pour un item appartenant à l'utilisateur.
        if ($type === 'folder') {
            $this->ownedFolderOrDeny($id);
        } else {
            $this->ownedFileOrDeny($id);
        }
        require_once '../app/Models/GedShare.php';
        $shareModel = new GedShare();
        $shares = $shareModel->getItemShares($type, $id);
        echo json_encode($shares);
        exit;
    }

    public function move() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $type = $_POST['type']; // 'folder' ou 'file'
            $newParentId = $_POST['destination_id'];
            $currentFolderId = $_POST['current_folder_id'];

            // L'item déplacé ET le dossier de destination doivent appartenir
            // à l'utilisateur courant (anti-IDOR).
            $this->ownedFolderOrDeny($newParentId);
            if ($type === 'folder') {
                $this->ownedFolderOrDeny($id);
                $this->gedFolderModel->moveFolder($id, $newParentId);
            } else {
                $this->ownedFileOrDeny($id);
                $this->gedFileModel->moveFile($id, $newParentId);
            }
            header('Location: ' . URLROOT . '/ged/folder/' . $currentFolderId);
        }
    }

    public function copy() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $type = $_POST['type']; // 'folder' ou 'file'
            $newParentId = $_POST['destination_id'];
            $currentFolderId = $_POST['current_folder_id'];

            // Source et destination doivent appartenir à l'utilisateur courant.
            $this->ownedFolderOrDeny($newParentId);
            if ($type === 'folder') {
                $this->ownedFolderOrDeny($id);
                $this->gedFolderModel->copyFolder($id, $newParentId, $this->gedFileModel);
            } else {
                $this->ownedFileOrDeny($id);
                $this->gedFileModel->copyFile($id, $newParentId);
            }
            header('Location: ' . URLROOT . '/ged/folder/' . $currentFolderId);
        }
    }

    public function getFoldersTree() {
        $folders = $this->gedFolderModel->getAllFoldersTree($_SESSION['user_id']);
        echo json_encode($folders);
        exit;
    }

    public function generateExternalLink($folderId) {
        // Sécurité : ne générer un lien public que sur un dossier appartenant à
        // l'utilisateur courant (sinon un simple utilisateur pouvait émettre un
        // jeton de dépôt/consultation sur le dossier d'autrui, cross-tenant).
        $this->ownedFolderOrDeny($folderId);

        $linkModel = $this->model('GedExternalLink');

        // Vérifier si un lien existe déjà pour ce dossier
        $existingLink = $linkModel->getLinkByFolderId($folderId);
        
        if ($existingLink) {
            // Renouveller le lien existant
            if ($linkModel->renewLink($existingLink->token)) {
                $fullUrl = URLROOT . '/externalged/deposit/' . $existingLink->token;
                echo json_encode(['success' => true, 'url' => $fullUrl, 'renewed' => true]);
                exit;
            }
        }

        // Sinon en générer un nouveau
        $token = $linkModel->generateLink($folderId);
        if ($token) {
            $fullUrl = URLROOT . '/externalged/deposit/' . $token;
            echo json_encode(['success' => true, 'url' => $fullUrl]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}
