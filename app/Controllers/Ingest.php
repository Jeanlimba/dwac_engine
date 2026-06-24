<?php

/**
 * API d'ingestion de la présence (machine-à-machine).
 *
 * Reçoit en POST un lot de pointages envoyé par le collecteur local (qui lit la
 * pointeuse ZKTeco sur le LAN). Authentifiée par signature HMAC-SHA256 du corps
 * brut (pas de session) ; exemptée de la protection CSRF dans le front controller.
 *
 * Contrat :
 *   POST /ingest/pointages
 *   En-tête : X-Presence-Signature: hmac_sha256(body, PRESENCE_API_SECRET)
 *   Corps (JSON) : [ {"zk_id":1,"date_heure":"2026-06-24 08:01:00","type":0}, ... ]
 *   Réponse : {"inserted":N,"skipped":M}
 */
class Ingest extends Controller {

    public function pointages() {
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

        // Comparaison à temps constant ; rejette si signature absente/invalide.
        if (!is_string($sent) || $sent === '' || !hash_equals($expected, $sent)) {
            http_response_code(401);
            echo json_encode(['error' => 'Signature invalide']);
            exit;
        }

        $payload = json_decode($body, true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Corps JSON invalide']);
            exit;
        }

        $tenantId = defined('PRESENCE_TENANT_ID') ? (int) PRESENCE_TENANT_ID : 0;
        if ($tenantId <= 0) {
            http_response_code(500);
            echo json_encode(['error' => 'Tenant non configuré']);
            exit;
        }

        $result = $this->model('Pointage')->ingestBatch($tenantId, $payload);
        echo json_encode($result);
        exit;
    }
}
