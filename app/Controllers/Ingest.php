<?php

/**
 * API d'ingestion / enrôlement de la présence (machine-à-machine).
 *
 * Utilisée par le collecteur local (qui voit la pointeuse ZKTeco sur le LAN)
 * pour échanger avec evolution en ligne sans accès MySQL distant. Toutes les
 * routes sont authentifiées par signature HMAC-SHA256 du corps brut
 * (en-tête X-Presence-Signature) contre PRESENCE_API_SECRET ; le contrôleur est
 * exempté de la protection CSRF dans le front controller.
 *
 * Routes (toutes en POST, corps JSON, réponse JSON) :
 *   /ingest/pointages        : lot de pointages -> {inserted,skipped}
 *   /ingest/pendingEmployees : employés sans zk_id -> {employees,max_zk_id}
 *   /ingest/setZkId          : {employe_id, zk_id} -> {success}
 */
class Ingest extends Controller {

    /**
     * Authentifie la requête (POST + signature HMAC) et renvoie [tenantId, body].
     * Interrompt avec une réponse JSON d'erreur sinon.
     */
    private function authenticate() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'POST requis']);
            exit;
        }

        $secret = defined('PRESENCE_API_SECRET') ? PRESENCE_API_SECRET : '';
        if ($secret === '') {
            http_response_code(500);
            echo json_encode(['error' => 'API non configurée']);
            exit;
        }

        $body = file_get_contents('php://input');
        $sent = $_SERVER['HTTP_X_PRESENCE_SIGNATURE'] ?? '';
        $expected = hash_hmac('sha256', $body, $secret);
        if (!is_string($sent) || $sent === '' || !hash_equals($expected, $sent)) {
            http_response_code(401);
            echo json_encode(['error' => 'Signature invalide']);
            exit;
        }

        $tenantId = defined('PRESENCE_TENANT_ID') ? (int) PRESENCE_TENANT_ID : 0;
        if ($tenantId <= 0) {
            http_response_code(500);
            echo json_encode(['error' => 'Tenant non configuré']);
            exit;
        }

        return [$tenantId, $body];
    }

    /** Réception d'un lot de pointages. */
    public function pointages() {
        [$tenantId, $body] = $this->authenticate();

        $payload = json_decode($body, true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Corps JSON invalide']);
            exit;
        }

        $result = $this->model('Pointage')->ingestBatch($tenantId, $payload);
        echo json_encode($result);
        exit;
    }

    /** Liste des employés du tenant non encore enrôlés + plus grand zk_id utilisé. */
    public function pendingEmployees() {
        [$tenantId] = $this->authenticate();

        $employeeModel = $this->model('Employee');
        echo json_encode([
            'employees'  => $employeeModel->getWithoutZkIdByTenant($tenantId),
            'max_zk_id'  => $employeeModel->getMaxZkIdByTenant($tenantId),
        ]);
        exit;
    }

    /** Persiste le zk_id attribué à un employé (après poussée sur la pointeuse). */
    public function setZkId() {
        [$tenantId, $body] = $this->authenticate();

        $data = json_decode($body, true);
        $employeId = isset($data['employe_id']) ? (int) $data['employe_id'] : 0;
        $zkId = isset($data['zk_id']) ? (int) $data['zk_id'] : 0;

        if ($employeId <= 0 || $zkId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'employe_id et zk_id requis']);
            exit;
        }

        $ok = $this->model('Employee')->assignZkId($employeId, $tenantId, $zkId);
        if (!$ok) {
            http_response_code(409);
            echo json_encode(['error' => 'Employé introuvable, hors tenant, ou déjà enrôlé']);
            exit;
        }

        echo json_encode(['success' => true]);
        exit;
    }
}
