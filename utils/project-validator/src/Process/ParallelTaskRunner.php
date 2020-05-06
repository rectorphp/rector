<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Process;

use Nette\Utils\Strings;
use Rector\Utils\ProjectValidator\Exception\ProcessResultInvalidException;
use Rector\Utils\ProjectValidator\ValueObject\SetTask;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Throwable;

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
     * @var bool
     */
    private $isSuccessful = true;

    /**
     * @var int
     */
    private $finishedProcessCount = 0;

    /**
     * @var Process[]
     */
    private $runningProcesses = [];

    /**
     * @var SetTask[]
     */
    private $remainingTasks = [];

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
        $this->initialize();

        $this->printInfo($tasks, $maxProcesses);

        $this->remainingTasks = $tasks;
        $total = count($tasks);

        do {
            $this->sleepIfNecessary($maxProcesses, $sleepInSeconds);
            $this->startProcess($maxProcesses, $total);
            $this->evaluateRunningProcesses($total);

            $someProcessesAreStillRunning = count($this->runningProcesses) > 0;
            $notAllProcessesAreStartedYet = count($this->remainingTasks) > 0;
        } while ($someProcessesAreStillRunning || $notAllProcessesAreStartedYet);

        return $this->isSuccessful;
    }

    private function initialize(): void
    {
        $this->finishedProcessCount = 0;
        $this->isSuccessful = true;
        $this->remainingTasks = [];
    }

    /**
     * @param SetTask[] $setTasks
     */
    private function printInfo(array $setTasks, int $maxProcesses): void
    {
        $message = sprintf('Running %d sets with %d parallel processes', count($setTasks), $maxProcesses);

        $this->symfonyStyle->writeln($message);
    }

    /**
     * We should sleep when the processes are running in order to not
     * exhaust system resources. But we only wanna do this when
     * we can't start another processes:
     * either because none are left or
     * because we reached the threshold of allowed processes
     */
    private function sleepIfNecessary(int $maxProcesses, int $secondsToSleep): void
    {
        $noMoreProcessesAreLeft = count($this->remainingTasks) === 0;
        $maxNumberOfProcessesAreRunning = count($this->runningProcesses) >= $maxProcesses;

        if ($noMoreProcessesAreLeft || $maxNumberOfProcessesAreRunning) {
            sleep($secondsToSleep);
        }
    }

    private function startProcess(int $maxProcesses, int $total): void
    {
        if ($this->canStartAnotherProcess($maxProcesses)) {
            /** @var string $setName */
            $setName = array_key_first($this->remainingTasks);
            $task = array_shift($this->remainingTasks);

            try {
                $process = $this->createProcessFromSetTask($task);
                $process->start();
                $this->runningProcesses[$setName] = $process;
            } catch (Throwable $throwable) {
                $this->isSuccessful = false;
                $this->printError($setName, $throwable->getMessage(), $total);
            }
        }
    }

    private function evaluateRunningProcesses(int $total): void
    {
        foreach ($this->runningProcesses as $setName => $process) {
            if ($process->isRunning()) {
                continue;
            }

            ++$this->finishedProcessCount;

            unset($this->runningProcesses[$setName]);

            try {
                $this->evaluateProcess($process);
                $this->printSuccess($setName, $total);
            } catch (Throwable $throwable) {
                $this->isSuccessful = false;
                $this->printError($setName, $process->getOutput() . $process->getErrorOutput(), $total);
            }
        }
    }

    private function canStartAnotherProcess(int $max): bool
    {
        $hasOpenTasks = count($this->remainingTasks) > 0;
        if (! $hasOpenTasks) {
            return false;
        }

        return count($this->runningProcesses) < $max;
    }

    private function createProcessFromSetTask(SetTask $setTask): Process
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

    private function printError(string $set, string $output, int $totalTasks): void
    {
        $message = sprintf('(%d/%d) Set "%s" failed: %s', $this->finishedProcessCount, $totalTasks, $set, $output);
        $this->symfonyStyle->error($message);
    }

    private function evaluateProcess(Process $process): void
    {
        if ($process->isSuccessful()) {
            return;
        }

        // If the process was not successful, there might
        // OR an actual error due to an exception
        // The latter case is determined via regex
        // EITHER be a "possible correction" from rector

        $fullOutput = array_filter([$process->getOutput(), $process->getErrorOutput()]);

        $ouptput = implode("\n", $fullOutput);

        $actualErrorHappened = Strings::match($ouptput, '#(Fatal error)|(\[ERROR\])#');

        if (! $actualErrorHappened) {
            return;
        }

        throw new ProcessResultInvalidException($ouptput);
    }

    private function printSuccess(string $set, int $totalTasks): void
    {
        $message = sprintf('(%d/%d) Set "%s" is OK', $this->finishedProcessCount, $totalTasks, $set);
        $this->symfonyStyle->success($message);
    }
}
