<?php
function check_local($host) {
    $is_local = false;
    if ($host === 'localhost' || $host === '127.0.0.1' || 
        (strlen($host) > 5 && substr($host, -5) === '.test') || 
        (strlen($host) > 6 && substr($host, -6) === '.local')) {
        $is_local = true;
    }
    return $is_local;
}

$hosts = ['localhost', '127.0.0.1', 'evolution.test', 'evolution.local', 'localhost:8000', '127.0.0.1:8080', 'evolution.test:8443'];

foreach ($hosts as $h) {
    echo "$h: " . (check_local($h) ? "LOCAL" : "ONLINE") . "\n";
}
