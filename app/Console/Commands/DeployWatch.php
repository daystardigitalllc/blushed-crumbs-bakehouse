<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DeployWatch extends Command
{
    protected $signature = 'deploy:watch';

    protected $description = 'Run post-deploy steps only when new code has been pulled since the last run';

    public function handle(): int
    {
        $basePath = base_path();
        $stateFile = storage_path('.last_deployed_sha');

        $current = trim((new Process(['git', 'rev-parse', 'HEAD'], $basePath))->mustRun()->getOutput());
        $last = is_file($stateFile) ? trim(file_get_contents($stateFile)) : '';

        if ($current === $last) {
            return self::SUCCESS;
        }

        $this->info("Deploying {$last} -> {$current}");

        $process = new Process(['bash', 'deploy/post-deploy.sh'], $basePath);
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) {
            $this->getOutput()->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->error('Deploy script failed, leaving state file untouched so it retries next run.');

            return self::FAILURE;
        }

        file_put_contents($stateFile, $current);
        $this->info('Deploy finished successfully.');

        return self::SUCCESS;
    }
}
