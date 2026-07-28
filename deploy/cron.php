<?php

chdir(__DIR__ . '/..');

$logFile = __DIR__ . '/../storage/logs/deploy.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

passthru('php artisan deploy:watch >> ' . escapeshellarg($logFile) . ' 2>&1', $exitCode);

// Pinned to the default queue — onboarding's ingest/ai-import work runs on a
// separate Forge daemon (see the onboarding-rebuild plan, Phase 3) so a burst
// of extraction batches can't starve ordinary app jobs like mail or domain
// verification queued here.
$queueLogFile = __DIR__ . '/../storage/logs/queue.log';
passthru('php artisan queue:work --queue=default --stop-when-empty --max-time=250 >> ' . escapeshellarg($queueLogFile) . ' 2>&1');

exit($exitCode);
