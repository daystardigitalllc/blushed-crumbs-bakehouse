<?php

chdir(__DIR__ . '/..');

$logFile = __DIR__ . '/../storage/logs/deploy.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

passthru('php artisan deploy:watch >> ' . escapeshellarg($logFile) . ' 2>&1', $exitCode);

$queueLogFile = __DIR__ . '/../storage/logs/queue.log';
passthru('php artisan queue:work --stop-when-empty --max-time=250 >> ' . escapeshellarg($queueLogFile) . ' 2>&1');

exit($exitCode);
