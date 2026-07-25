<?php

chdir(__DIR__ . '/..');

$logFile = __DIR__ . '/../storage/logs/deploy.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

passthru('php artisan deploy:watch >> ' . escapeshellarg($logFile) . ' 2>&1', $exitCode);
exit($exitCode);
