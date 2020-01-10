<?php

declare(strict_types=1);

namespace Rector\Compiler\Process;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

final class InstantlyRunningSymfonyProcess
{
    /**
     * @param string[] $command
     */
    public function __construct(array $command, string $cwd, OutputInterface $output)
    {
        $process = new Process($command, $cwd, null, null, null);

        $process->mustRun(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });
    }
}
