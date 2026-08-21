<?php
// Sécurité : utilitaire réservé à la ligne de commande (jamais via le web).
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
require_once '../config/database.php';

$stmt = $pdo->query("SELECT id, username, password FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($users);
