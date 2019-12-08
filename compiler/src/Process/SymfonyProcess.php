<?php

declare(strict_types=1);

namespace Rector\Compiler\Process;

use Rector\Compiler\Contract\Process\ProcessInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

final class SymfonyProcess implements ProcessInterface
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @param string[] $command
     */
    public function __construct(array $command, string $cwd, OutputInterface $output)
    {
        $this->process = (new Process($command, $cwd, null, null, null))
            ->mustRun(static function (string $type, string $buffer) use ($output): void {
                $output->write($buffer);
            });
    }

    public function getProcess(): Process
    {
        return $this->process;
    }
}
