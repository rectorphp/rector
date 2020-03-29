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

        $success = true;

        /** @var Process[] $runningProcesses */
        $runningProcesses = [];
        $remainingTasks = $tasks;
        $finished = 0;
        $total = count($tasks);

        do {
            $this->sleepIfNecessary($remainingTasks, $runningProcesses, $maxProcesses, $sleepInSeconds);

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
     * @param SetTask[] $remainingTasks
     * @param SetTask[] $runningProcesses
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
                $process = $this->createProcessFromSetTask($task);
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
            if ($process->isRunning()) {
                continue;
            }

            $finished++;
            unset($runningProcesses[$setName]);

            try {
                $this->evaluateProcess($process);
                $this->printSuccess($setName, $total, $finished);
            } catch (Throwable $throwable) {
                $success = false;
                $this->printError($setName, $process->getOutput() . $process->getErrorOutput(), $total, $finished);
            }
        }
    }

    private function canStartAnotherProcess(array $remainingTasks, array $runningProcesses, int $max): bool
    {
        $hasOpenTasks = count($remainingTasks) > 0;
        $moreProcessesCanBeStarted = count($runningProcesses) < $max;
        return $hasOpenTasks && $moreProcessesCanBeStarted;
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

    private function printSuccess(string $set, int $totalTasks, int $finishedTasks): void
    {
        $message = sprintf('(%d/%d) Set "%s" is OK', $finishedTasks, $totalTasks, $set);
        $this->symfonyStyle->success($message);
    }

    private function printError(string $set, string $output, int $totalTasks, int $finishedTasks): void
    {
        $message = sprintf('(%d/%d) Set "%s" failed: %s', $finishedTasks, $totalTasks, $set, $output);
        $this->symfonyStyle->error($message);
    }

    /**
     * @param SetTask[] $setTasks
     */
    private function printInfo(array $setTasks, int $maxProcesses): void
    {
        $message = sprintf('Running %d sets with %d parallel processes', count($setTasks), $maxProcesses);

        $this->symfonyStyle->writeln($message);
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
}
