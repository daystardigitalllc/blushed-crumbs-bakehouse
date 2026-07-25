<?php

chdir(__DIR__ . '/..');
passthru('php artisan deploy:watch', $exitCode);
exit($exitCode);
