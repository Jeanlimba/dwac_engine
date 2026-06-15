<?php
function check_local_fixed($host) {
    $is_local = false;
    $hostname = explode(':', $host)[0];
    
    if ($hostname === 'localhost' || $hostname === '127.0.0.1' || 
        (strlen($hostname) > 5 && substr($hostname, -5) === '.test') || 
        (strlen($hostname) > 6 && substr($hostname, -6) === '.local') ||
        (strlen($hostname) > 10 && substr($hostname, -10) === '.localhost')) {
        $is_local = true;
    }
    return $is_local;
}

$hosts = ['localhost', '127.0.0.1', 'evolution.test', 'evolution.local', 'localhost:8000', '127.0.0.1:8080', 'evolution.test:8443', 'my.localhost', 'my.localhost:3000'];

foreach ($hosts as $h) {
    echo "$h: " . (check_local_fixed($h) ? "LOCAL" : "ONLINE") . "\n";
}
