<?php
class Externalged extends Controller {
    private $linkModel;
    private $gedFileModel;
    private $gedFolderModel;

    public function __construct() {
        $this->linkModel = $this->model('GedExternalLink');
        $this->gedFileModel = $this->model('GedFile');
        $this->gedFolderModel = $this->model('GedFolder');
    }

    public function deposit($token = null, $folderId = null) {
        if (!$token) {
            $this->view('errors/404');
            exit;
        }
        $link = $this->linkModel->getLinkByToken($token);
        
        if (!$link) {
            $this->view('errors/404');
            exit;
        }

        $currentFolderId = $folderId ?: $link->folder_id;

        // Sécurité : vérifier que le dossier demandé est bien un descendant du dossier lié
        if ($currentFolderId != $link->folder_id) {
            if (!$this->isDescendant($currentFolderId, $link->folder_id)) {
                $this->view('errors/404');
                exit;
            }
        }

        $folder = $this->gedFolderModel->getFolderById($currentFolderId);
        
        if (!$folder) {
            $this->view('errors/404');
            exit;
        }

        $orderBy = isset($_GET['sort']) ? $_GET['sort'] : 'name';
        $orderDir = isset($_GET['dir']) ? $_GET['dir'] : 'ASC';
        $q = isset($_GET['q']) ? trim($_GET['q']) : null;

        $subfolders = $this->gedFolderModel->getSubfolders($currentFolderId, $orderBy, $orderDir, $q);
        $files = $this->gedFileModel->getFilesByFolder($currentFolderId, $orderBy, $orderDir, $q);

        // Breadcrumbs filtrés
        $allBreadcrumbs = $this->gedFolderModel->getBreadcrumbs($currentFolderId);
        $filteredBreadcrumbs = [];
        $foundStart = false;
        foreach ($allBreadcrumbs as $crumb) {
            if ($crumb->id == $link->folder_id) {
                $foundStart = true;
            }
            if ($foundStart) {
                $filteredBreadcrumbs[] = $crumb;
            }
        }

        $data = [
            'title' => 'GED - ' . $folder->name,
            'link' => $link,
            'current_folder' => $folder,
            'subfolders' => $subfolders ?: [],
            'files' => $files ?: [],
            'breadcrumbs' => $filteredBreadcrumbs,
            'is_root' => ($currentFolderId == $link->folder_id),
            'sort' => $orderBy,
            'dir' => $orderDir,
            'search_term' => $q
        ];

        // On utilise une vue spéciale sans le layout admin
        $this->view('ged/external_deposit', $data);
    }

    private function isDescendant($folderId, $ancestorId) {
        $breadcrumbs = $this->gedFolderModel->getBreadcrumbs($folderId);
        foreach ($breadcrumbs as $crumb) {
            if ($crumb->id == $ancestorId) return true;
        }
        return false;
    }

    public function viewFile($token, $fileId) {
        $link = $this->linkModel->getLinkByToken($token);
        if (!$link) {
            $this->view('errors/404');
            exit;
        }

        $file = $this->gedFileModel->getFileById($fileId);
        if (!$file) {
            $this->view('errors/404');
            exit;
        }

        // Sécurité : vérifier que le fichier appartient au dossier lié ou un de ses descendants
        if ($file->folder_id != $link->folder_id && !$this->isDescendant($file->folder_id, $link->folder_id)) {
            $this->view('errors/404');
            exit;
        }

        $filePath = APPROOT . '/../public/uploads/ged/' . $file->physical_name;
        if (!file_exists($filePath)) {
            $this->view('errors/404');
            exit;
        }

        // Détecter le type MIME si non présent ou générique
        $mimeType = $file->mime_type;
        if (empty($mimeType) || $mimeType == 'application/octet-stream') {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
        }

        // Nom assaini : on retire les caractères de contrôle (\r\n) pour éviter
        // une injection d'en-tête HTTP via un nom de fichier piégé, et les
        // guillemets qui casseraient l'attribut filename.
        $safeName = preg_replace('/[\r\n"\\\\]+/', '_', $file->name);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . $safeName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=3600');
        
        readfile($filePath);
        exit;
    }

    public function upload() {
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        try {
            $token = $_GET['t'] ?? '';
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $link = $this->linkModel->getLinkByToken($token);
                if (!$link) {
                    echo json_encode(['success' => false, 'message' => 'Lien invalide ou expiré.']);
                    exit;
                }

                if (!isset($_FILES['files'])) {
                    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu dans la requête.']);
                    exit;
                }

                $targetFolderId = $_GET['f'] ?? $link->folder_id;
                
                // Sécurité : vérifier que le dossier cible est bien un descendant du dossier lié
                if ($targetFolderId != $link->folder_id) {
                    if (!$this->isDescendant($targetFolderId, $link->folder_id)) {
                        echo json_encode(['success' => false, 'message' => 'Accès non autorisé au dossier cible.']);
                        exit;
                    }
                }

                $targetDir = APPROOT . '/../public/uploads/ged/';
                
                if (!is_dir($targetDir)) {
                    // 0755 : pas de droit d'écriture pour "tout le monde".
                    if (!mkdir($targetDir, 0755, true)) {
                        throw new Exception("Impossible de créer le dossier d'upload.");
                    }
                }

                $uploadedCount = 0;
                $errors = [];

                foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
                    if (empty($tmpName)) {
                        if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) {
                            $errors[] = "Erreur d'upload pour " . $_FILES['files']['name'][$key] . " (Code: " . $_FILES['files']['error'][$key] . ")";
                        }
                        continue;
                    }

                    $name = $_FILES['files']['name'][$key];
                    $size = $_FILES['files']['size'][$key];

                    // Validation de sécurité (dépôt PUBLIC : contrôle d'autant plus
                    // important). Extension en liste blanche + MIME réel + nom aléatoire.
                    $check = validate_upload($name, $tmpName, $size);
                    if (!$check['ok']) {
                        $errors[] = $check['error'] . " (" . $name . ")";
                        continue;
                    }

                    if (move_uploaded_file($tmpName, $targetDir . $check['physical_name'])) {
                        $fileData = [
                            'folder_id' => $targetFolderId,
                            'user_id' => null,
                            'name' => $name,
                            'physical_name' => $check['physical_name'],
                            'size' => $size,
                            'extension' => $check['extension'],
                            'mime_type' => $check['mime']
                        ];
                        if ($this->gedFileModel->addFile($fileData)) {
                            $uploadedCount++;
                        } else {
                            $errors[] = "Erreur lors de l'enregistrement en base pour " . $name;
                        }
                    } else {
                        $errors[] = "Échec du déplacement du fichier " . $name;
                    }
                }

                if ($uploadedCount > 0) {
                    echo json_encode([
                        'success' => true, 
                        'count' => $uploadedCount, 
                        'message' => $uploadedCount . " fichier(s) importé(s) avec succès.",
                        'errors' => $errors
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Aucun fichier n\'a pu être importé.',
                        'errors' => $errors
                    ]);
                }
                exit;
            }
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur PHP : ' . $e->getMessage()]);
        }
        exit;
    }
}
