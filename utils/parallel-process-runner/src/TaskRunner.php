<?php

declare(strict_types=1);

namespace Rector\Utils\ParallelProcessRunner;

use Nette\Utils\Strings;
use Rector\Utils\ParallelProcessRunner\Exception\ProcessResultInvalidException;
use Symfony\Component\Process\Process;
use Throwable;

final class TaskRunner
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
    private $failOnlyOnError = false;

    public function __construct(string $phpExecutable, string $rectorExecutable, string $cwd, bool $failOnlyOnError)
    {
        $this->phpExecutable = $phpExecutable;
        $this->rectorExecutable = $rectorExecutable;
        $this->cwd = $cwd;
        $this->failOnlyOnError = $failOnlyOnError;
    }

    /**
     * @param Task[] $tasks
     */
    public function run(array $tasks, int $maxProcesses = 1, int $sleepSeconds = 1): bool
    {
        $this->printInfo($tasks, $maxProcesses);

        $success = true;

        /** @var Process[] $runningProcesses */
        $runningProcesses = [];
        $remainingTasks = $tasks;
        $finished = 0;
        $total = count($tasks);
        do {
            $this->sleepIfNecessary($remainingTasks, $runningProcesses, $maxProcesses, $sleepSeconds);

            $this->startProcess($remainingTasks, $runningProcesses, $success, $maxProcesses, $total, $finished);

            $this->evaluateRunningProcesses($runningProcesses, $success, $total, $finished);

            $someProcessesAreStillRunning = count($runningProcesses) > 0;
            $notAllProcessesAreStartedYet = count($remainingTasks) > 0;
        } while ($someProcessesAreStillRunning || $notAllProcessesAreStartedYet);

        return $success;
    }

    /**
     * We should sleep when the processes are running in order to not
     * exhaust system resources. But we only wanna do this when
     * we can't start another processes:
     * either because none are left or
     * because we reached the threshold of allowed processes
     *
     * @param string[] $taskIdsToRuns
     * @param Process[] $runningProcesses
     */
    private function sleepIfNecessary(
        array $taskIdsToRuns,
        array $runningProcesses,
        int $maxProcesses,
        int $secondsToSleep
    ): void {
        $noMoreProcessesAreLeft = count($taskIdsToRuns) === 0;
        $maxNumberOfProcessesAreRunning = count($runningProcesses) >= $maxProcesses;
        if ($noMoreProcessesAreLeft || $maxNumberOfProcessesAreRunning) {
            sleep($secondsToSleep);
        }
    }

    /**
     * @param Task[] $remainingTasks
     * @param Task[] $runningProcesses
     */
    private function startProcess(
        array &$remainingTasks,
        array &$runningProcesses,
        bool &$success,
        int $maxProcesses,
        int $total,
        int $finished
    ): void {
        if ($this->canStartAnotherProcess($remainingTasks, $runningProcesses, $maxProcesses)) {
            $setName = array_key_first($remainingTasks);
            $task = array_shift($remainingTasks);

            try {
                $process = $this->createProcess($task);
                $process->start();
                $runningProcesses[$setName] = $process;
            } catch (Throwable $throwable) {
                $success = false;
                $this->printError($setName, $throwable->getMessage(), $total, $finished);
            }
        }
    }

    private function evaluateRunningProcesses(
        array &$runningProcesses,
        bool &$success,
        int $total,
        int &$finished
    ): void {
        foreach ($runningProcesses as $setName => $process) {
            if (! $process->isRunning()) {
                $finished++;
                unset($runningProcesses[$setName]);

                try {
                    $this->evaluateProcess($process);
                    $this->printSuccess($setName, $total, $finished);
                } catch (Throwable $throwable) {
                    $success = false;
                    $this->printError(
                        $setName,
                        $process->getOutput() . $process->getErrorOutput(),
                        $total,
                        $finished
                    );
                }
            }
        }
    }

    private function canStartAnotherProcess(array $remainingTasks, array $runningProcesses, int $max): bool
    {
        $hasOpenTasks = count($remainingTasks) > 0;
        $moreProcessesCanBeStarted = count($runningProcesses) < $max;
        return $hasOpenTasks && $moreProcessesCanBeStarted;
    }

    private function createProcess(Task $task): Process
    {
        $command = [
            $this->phpExecutable,
            $this->rectorExecutable,
            'process',
            $task->getPathToFile(),
            '--set',
            $task->getSetName(),
            '--dry-run',
        ];

        return new Process($command, $this->cwd);
    }

    private function printSuccess(string $set, int $totalTasks, int $finishedTasks): void
    {
        echo sprintf('(%d/%d) ✔ Set "%s" is OK' . PHP_EOL, $finishedTasks, $totalTasks, $set);
    }

    private function printError(string $set, string $output, int $totalTasks, int $finishedTasks): void
    {
        echo sprintf('(%d/%d) ❌ Set "%s" failed: %s' . PHP_EOL, $finishedTasks, $totalTasks, $set, $output);
    }

    private function printInfo(array $tasks, int $maxProcesses): void
    {
        echo sprintf('Running %d sets with %d parallel processes' . PHP_EOL . PHP_EOL, count($tasks), $maxProcesses);
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

        if ($this->failOnlyOnError && ! $actualErrorHappened) {
            return;
        }

        throw new ProcessResultInvalidException($ouptput);
    }
}
