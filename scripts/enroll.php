<?php
/**
 * Enrôlement biométrique — À EXÉCUTER SUR LE PC LOCAL qui voit la pointeuse.
 *
 * Lie un employé à un identifiant pointeuse (zk_id) et le pousse sur la machine
 * ZKTeco. Dialogue avec evolution en ligne via l'API signée (pas de MySQL distant).
 *
 *   php scripts\enroll.php             -> liste les employés non enrôlés
 *   php scripts\enroll.php <employe_id> -> enrôle cet employé
 *
 * Prérequis : `composer install`, extension `sockets`, .env renseigné
 * (PRESENCE_DEVICE_IP/_PORT, PRESENCE_API_URL, PRESENCE_API_SECRET).
 */

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

require_once __DIR__ . '/../src/functions.php';

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "vendor/ absent : lancez `composer install`.\n");
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

// Base de l'API (on retire le dernier segment de PRESENCE_API_URL, ex: /pointages).
$apiBase = preg_replace('#/[^/]*$#', '', $apiUrl);

/**
 * POST JSON signé HMAC vers l'API. Renvoie [httpCode, donnéesDécodées].
 */
function signedPost($url, $secret, array $data) {
    $body = json_encode($data);
    $sig = hash_hmac('sha256', $body, $secret);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Presence-Signature: ' . $sig,
        ],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode((string) $resp, true)];
}

// Récupère la liste des employés non enrôlés + le plus grand zk_id.
[$code, $pending] = signedPost($apiBase . '/pendingEmployees', $secret, []);
if ($code !== 200 || !is_array($pending)) {
    fwrite(STDERR, "[ERREUR] API pendingEmployees (HTTP $code).\n");
    exit(1);
}

$employees = $pending['employees'] ?? [];
$maxZk = (int) ($pending['max_zk_id'] ?? 0);
$arg = $argv[1] ?? null;

// Mode liste.
if ($arg === null) {
    if (!$employees) {
        echo "Aucun employé à enrôler (tous ont déjà un zk_id).\n";
        exit(0);
    }
    echo "Employés non enrôlés :\n";
    foreach ($employees as $e) {
        echo "  [{$e['id']}] {$e['prenom']} {$e['nom']} (matricule: " . ($e['matricule'] ?? '-') . ")\n";
    }
    echo "\nUsage : php scripts\\enroll.php <employe_id>\n";
    exit(0);
}

// Mode enrôlement.
$target = null;
foreach ($employees as $e) {
    if ((string) $e['id'] === (string) $arg) {
        $target = $e;
        break;
    }
}
if (!$target) {
    fwrite(STDERR, "Employé $arg introuvable parmi les non enrôlés.\n");
    exit(1);
}

$zkId = $maxZk + 1;
$fullName = trim($target['prenom'] . ' ' . $target['nom']);

// 1) Pousser l'utilisateur sur la pointeuse.
$zk = new ZKTeco($ip, $port);
if (!$zk->connect()) {
    fwrite(STDERR, "[ERREUR] Pointeuse injoignable ($ip:$port).\n");
    exit(1);
}
// uid, userid, name, password, role
$zk->setUser($zkId, $zkId, $fullName, '', 0);
$zk->disconnect();

// 2) Persister le zk_id côté serveur en ligne.
[$c2, $r2] = signedPost($apiBase . '/setZkId', $secret, ['employe_id' => (int) $arg, 'zk_id' => $zkId]);
if ($c2 !== 200 || empty($r2['success'])) {
    fwrite(STDERR, "[ERREUR] Persistance du zk_id échouée (HTTP $c2). L'utilisateur est sur la pointeuse mais pas enregistré en base — relancez.\n");
    exit(1);
}

echo "[OK] {$fullName} enrôlé (zk_id = {$zkId}). Il peut désormais pointer.\n";
