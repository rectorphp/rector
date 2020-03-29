<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Process;

use Generator;
use Rector\Utils\ProjectValidator\ValueObject\SetTask;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class ParallelTaskRunner
{
    /**
     * @var string
     */
    private $phpExecutable;

    /**
     * @var string
     */
    private $rectorExecutable;

    /**
     * @var string
     */
    private $cwd;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->phpExecutable = 'php';
        $this->rectorExecutable = getcwd() . '/bin/rector';
        $this->cwd = getcwd();
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @param SetTask[] $tasks
     */
    public function run(array $tasks, int $maxProcesses, int $sleepInSeconds): bool
    {
        $this->printInfo($tasks, $maxProcesses);

        $processGenerator = function (array $tasks): Generator {
            foreach ($tasks as $setName => $task) {
                yield $setName => $this->createProcess($task);
            }
        };

        $processes = $processGenerator($tasks);

        $taskRun = new ParallelTaskRun($this->symfonyStyle, count($tasks), $maxProcesses, $sleepInSeconds);

        return $taskRun($processes);
    }

    /**
     * @param SetTask[] $setTasks
     */
    private function printInfo(array $setTasks, int $maxProcesses): void
    {
        $message = sprintf('Running %d sets with %d parallel processes', count($setTasks), $maxProcesses);

        $this->symfonyStyle->writeln($message);
    }

    private function createProcess(SetTask $setTask): Process
    {
        $command = [
            $this->phpExecutable,
            $this->rectorExecutable,
            'process',
            $setTask->getPathToFile(),
            '--set',
            $setTask->getSetName(),
            '--dry-run',
        ];

        return new Process($command, $this->cwd);
    }
}
