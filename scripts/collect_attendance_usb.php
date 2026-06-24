<?php
/**
 * ============================================================================
 * COLLECTEUR DE PRÉSENCE — VARIANTE USB (SDK ZKTeco via COM)   [SQUELETTE]
 * ============================================================================
 * À EXÉCUTER SUR LE PC LOCAL relié à la pointeuse par câble USB.
 * Lit les pointages via le SDK Windows ZKTeco (zkemkeeper, COM) et les pousse,
 * signés HMAC, vers l'API d'ingestion d'evolution en ligne (identique à la
 * variante réseau collect_attendance.php).
 *
 * ⚠️ PRÉREQUIS STRICTS (à installer sur le PC, non testables à distance) :
 *   1. PHP **32 bits** (le SDK zkemkeeper est 32 bits ; un PHP 64 bits ne peut
 *      pas le charger). Exécuter CE script avec ce PHP 32 bits.
 *   2. Extension `com_dotnet` activée (php.ini : extension=com_dotnet).
 *   3. SDK ZKTeco "Standalone SDK" installé + DLL enregistrée :
 *        regsvr32 zkemkeeper.dll   (depuis un invite admin, version 32 bits)
 *   4. Pilote USB de la pointeuse installé. Le modèle DOIT supporter la
 *      communication USB↔PC (beaucoup de ZKTeco n'utilisent l'USB que pour une
 *      clé : dans ce cas, cette voie est impossible).
 *
 * ⚠️ À FINALISER selon le modèle : la MÉTHODE DE CONNEXION (voir plus bas).
 *    En USB, la pointeuse se présente le plus souvent comme un PORT COM virtuel
 *    (voir le Gestionnaire de périphériques quand elle est branchée).
 * ============================================================================
 */

require_once __DIR__ . '/../src/functions.php';
load_env(__DIR__ . '/../.env');

if (!class_exists('COM')) {
    fwrite(STDERR, "Extension com_dotnet absente (et PHP 32 bits requis). Voir l'en-tête du script.\n");
    exit(1);
}

$apiUrl = defined('PRESENCE_API_URL') ? PRESENCE_API_URL : '';
$secret = defined('PRESENCE_API_SECRET') ? PRESENCE_API_SECRET : '';
if ($apiUrl === '' || $secret === '') {
    fwrite(STDERR, "PRESENCE_API_URL / PRESENCE_API_SECRET manquants dans .env\n");
    exit(1);
}

// Paramètres de connexion USB (port COM virtuel) — À AJUSTER selon le modèle.
$comPort   = defined('PRESENCE_COM_PORT') ? (int) PRESENCE_COM_PORT : 1;   // ex: COM3 -> 3
$baudRate  = defined('PRESENCE_COM_BAUD') ? (int) PRESENCE_COM_BAUD : 115200;
$machineNo = 1;

try {
    $zk = new COM('zkemkeeper.ZKEM');

    // --- CONNEXION (à confirmer selon le modèle) ---
    // Cas le plus courant en USB : port COM virtuel.
    $connected = $zk->Connect_Com($comPort, $machineNo, $baudRate);
    // Alternative si le modèle expose une IP via USB (RNDIS) :
    //   $connected = $zk->Connect_Net('192.168.1.201', 4370);
    if (!$connected) {
        fwrite(STDERR, "[ERREUR] Connexion à la pointeuse échouée (USB/COM$comPort). Vérifiez pilote, port, modèle.\n");
        exit(1);
    }

    // Geler le terminal pendant la lecture, puis charger tous les logs.
    $zk->EnableDevice($machineNo, false);
    $zk->ReadAllGLogData($machineNo);

    // Paramètres de sortie passés par référence (VARIANT).
    $enroll = new VARIANT(); $verify = new VARIANT(); $inout = new VARIANT();
    $y = new VARIANT(); $mo = new VARIANT(); $d = new VARIANT();
    $h = new VARIANT(); $mi = new VARIANT(); $s = new VARIANT(); $wc = new VARIANT();

    $batch = [];
    while ($zk->SSR_GetGeneralLogData($machineNo, $enroll, $verify, $inout, $y, $mo, $d, $h, $mi, $s, $wc)) {
        $dateHeure = sprintf(
            '%04d-%02d-%02d %02d:%02d:%02d',
            (int) $y->value, (int) $mo->value, (int) $d->value,
            (int) $h->value, (int) $mi->value, (int) $s->value
        );
        $batch[] = [
            'zk_id'      => (string) $enroll->value, // = userid sur la pointeuse
            'date_heure' => $dateHeure,
            'type'       => (int) $inout->value,
        ];
    }

    $zk->EnableDevice($machineNo, true);
    $zk->Disconnect();

    if (!$batch) {
        echo "[INFO] Aucun pointage à envoyer.\n";
        exit(0);
    }

    // --- Envoi signé vers l'API (identique à la variante réseau) ---
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
    curl_close($ch);

    if ($resp === false || $code !== 200) {
        fwrite(STDERR, "[ERREUR] Envoi API (HTTP $code) : $resp\n");
        exit(1);
    }
    echo "[OK] " . count($batch) . " pointage(s) envoyé(s). Réponse : $resp\n";

} catch (\Throwable $e) {
    fwrite(STDERR, "[EXCEPTION] " . $e->getMessage() . "\n");
    exit(1);
}
