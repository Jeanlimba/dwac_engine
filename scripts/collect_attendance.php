<?php
/**
 * Collecteur de présence — À EXÉCUTER SUR LE PC LOCAL qui voit la pointeuse.
 *
 * Lit les pointages de la pointeuse ZKTeco (LAN) et les pousse, signés en
 * HMAC-SHA256, vers l'API d'ingestion d'evolution EN LIGNE (HTTPS). Évite ainsi
 * tout besoin d'accès MySQL distant.
 *
 * Planification (Planificateur de tâches Windows, ex. toutes les 5 min) :
 *   php C:\laragon\www\evolution\scripts\collect_attendance.php
 *
 * Prérequis sur le poste : `composer install` (lib coding-libs/zkteco-php) et
 * l'extension PHP `sockets`. Config dans .env : PRESENCE_DEVICE_IP / _PORT,
 * PRESENCE_API_URL, PRESENCE_API_SECRET.
 */

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

require_once __DIR__ . '/../src/functions.php';

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "vendor/ absent : lancez `composer install` à la racine du projet.\n");
    exit(1);
}
require_once $autoload;

load_env(__DIR__ . '/../.env');

$ip     = defined('PRESENCE_DEVICE_IP') ? PRESENCE_DEVICE_IP : '192.168.1.201';
$port   = defined('PRESENCE_DEVICE_PORT') ? (int) PRESENCE_DEVICE_PORT : 4370;
$apiUrl = defined('PRESENCE_API_URL') ? PRESENCE_API_URL : '';
$secret = defined('PRESENCE_API_SECRET') ? PRESENCE_API_SECRET : '';

if ($apiUrl === '' || $secret === '') {
    fwrite(STDERR, "PRESENCE_API_URL / PRESENCE_API_SECRET manquants dans .env\n");
    exit(1);
}

// Verrou anti-exécution concurrente (5 min).
$lock = __DIR__ . '/collect.lock';
if (file_exists($lock) && (time() - filemtime($lock) < 300)) {
    fwrite(STDERR, "Collecte déjà en cours ou trop récente.\n");
    exit(0);
}
file_put_contents($lock, (string) time());

try {
    $zk = new ZKTeco($ip, $port);
    if (!$zk->connect()) {
        fwrite(STDERR, "[ERREUR] Pointeuse injoignable ($ip:$port). Vérifiez IP/port/pare-feu.\n");
        exit(1);
    }

    $logs = $zk->getAttendances();
    $zk->disconnect();

    // Normalisation vers le contrat de l'API d'ingestion.
    $batch = [];
    foreach ($logs as $log) {
        $batch[] = [
            'zk_id'      => $log['user_id'] ?? null,
            'date_heure' => $log['record_time'] ?? null,
            'type'       => $log['type'] ?? 0,
        ];
    }

    if (!$batch) {
        echo "[INFO] Aucun pointage à envoyer.\n";
        exit(0);
    }

    $body = json_encode($batch);
    $signature = hash_hmac('sha256', $body, $secret);

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Presence-Signature: ' . $signature,
        ],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
        fwrite(STDERR, "[ERREUR] Envoi vers l'API échoué : $curlErr\n");
        exit(1);
    }
    if ($code !== 200) {
        fwrite(STDERR, "[ERREUR] L'API a répondu HTTP $code : $resp\n");
        exit(1);
    }

    echo "[OK] " . count($batch) . " pointage(s) envoyé(s). Réponse : $resp\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "[EXCEPTION] " . $e->getMessage() . "\n");
    exit(1);
} finally {
    if (file_exists($lock)) {
        unlink($lock);
    }
}
