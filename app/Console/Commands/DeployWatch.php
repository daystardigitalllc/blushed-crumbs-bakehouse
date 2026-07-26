<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DeployWatch extends Command
{
    protected $signature = 'deploy:watch';

    protected $description = 'Pull the latest code via git and run post-deploy steps if anything new landed';

    public function handle(): int
    {
        $basePath = base_path();

        $before = trim((new Process(['git', 'rev-parse', 'HEAD'], $basePath))->mustRun()->getOutput());

        $pull = new Process(['git', 'pull', 'origin', 'main'], $basePath);
        $pull->setTimeout(120);
        $pull->run(function ($type, $buffer) {
            $this->getOutput()->write($buffer);
        });

        if (!$pull->isSuccessful()) {
            $this->error('git pull failed — likely a conflict on the server that needs manual attention. Not running post-deploy steps.');

            return self::FAILURE;
        }

        $after = trim((new Process(['git', 'rev-parse', 'HEAD'], $basePath))->mustRun()->getOutput());

        if ($before === $after) {
            return self::SUCCESS;
        }

        $this->info("Deploying {$before} -> {$after}");

        $process = new Process(['bash', 'deploy/post-deploy.sh'], $basePath);
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) {
            $this->getOutput()->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->error('Deploy script failed.');

            return self::FAILURE;
        }

        $this->info('Deploy finished successfully.');

        return self::SUCCESS;
    }
}
