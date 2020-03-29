<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Process;

use Iterator;
use Nette\Utils\Strings;
use Rector\Utils\ProjectValidator\Exception\ProcessResultInvalidException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Throwable;

final class ParallelTaskRun
{
    /**
     * @var SymfonyStyle
     */
    private SymfonyStyle $symfonyStyle;

    /**
     * @var int
     */
    private int $maxProcesses;

    /**
     * @var int
     */
    private int $sleepInSeconds;

    /**
     * @var Process[]
     */
    private $runningProcesses = [];

    /**
     * @var int
     */
    private $finished;

    /**
     * @var int
     */
    private $total;

    /**
     * @var bool
     */
    private $success = false;

    public function __construct(SymfonyStyle $symfonyStyle, int $total, int $maxProcesses, int $sleepInSeconds)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->total = $total;
        $this->maxProcesses = $maxProcesses;
        $this->sleepInSeconds = $sleepInSeconds;
    }

    /**
     * @param Iterator<Process> $processes
     */
    public function __invoke(Iterator $processes): bool
    {
        $this->reset();

        do {
            $this->sleepIfNecessary($processes);

            $this->startProcess($processes);

            $this->evaluateRunningProcesses();
        } while ($this->notAllTasksAreFinished($processes));

        return $this->success;
    }

    private function reset(): void
    {
        $this->runningProcesses = [];
        $this->finished = 0;
        $this->success = true;
    }

    /**
     * We should sleep when the processes are running in order to not
     * exhaust system resources. But we only wanna do this when
     * we can't start another processes:
     * either because none are left or
     * because we reached the threshold of allowed processes
     *
     * @param Iterator<Process> $processes
     */
    private function sleepIfNecessary(Iterator $processes): void
    {
        $noMoreProcessesAreLeft = ! $processes->valid();
        $maxNumberOfProcessesAreRunning = count($this->runningProcesses) >= $this->maxProcesses;
        if ($noMoreProcessesAreLeft || $maxNumberOfProcessesAreRunning) {
            sleep($this->sleepInSeconds);
        }
    }

    /**
     * @param Iterator<Process> $processes
     */
    private function startProcess(Iterator $processes): void
    {
        if ($this->canStartAnotherProcess($processes)) {
            $setName = $processes->key();
            /** @var Process $process */
            $process = $processes->current();
            $processes->next();

            try {
                $process->start();
                $this->runningProcesses[$setName] = $process;
            } catch (Throwable $throwable) {
                $this->fail($setName, $throwable->getMessage());
            }
        }
    }

    private function evaluateRunningProcesses(): void
    {
        foreach ($this->runningProcesses as $setName => $process) {
            if (! $process->isRunning()) {
                unset($this->runningProcesses[$setName]);

                try {
                    $this->evaluateProcess($process);
                    $this->succeed($setName);
                } catch (Throwable $throwable) {
                    $this->fail($setName, $process->getOutput() . $process->getErrorOutput());
                }
            }
        }
    }

    /**
     * @param Iterator<Process> $processes
     */
    private function canStartAnotherProcess(Iterator $processes): bool
    {
        $hasOpenTasks = $processes->valid();
        $moreProcessesCanBeStarted = count($this->runningProcesses) < $this->maxProcesses;
        return $hasOpenTasks && $moreProcessesCanBeStarted;
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

    /**
     * @param Iterator<Process> $processes
     */
    private function notAllTasksAreFinished(Iterator $processes): bool
    {
        $someProcessesAreStillRunning = count($this->runningProcesses) > 0;
        $notAllProcessesAreStartedYet = $processes->valid();
        return $someProcessesAreStillRunning || $notAllProcessesAreStartedYet;
    }

    private function fail(string $setName, string $errorMessage): void
    {
        $this->success = false;
        $this->finished++;
        $this->printError($setName, $errorMessage);
    }

    private function succeed(string $setName): void
    {
        $this->finished++;
        $this->printSuccess($setName);
    }

    private function printSuccess(string $set): void
    {
        $message = sprintf('(%d/%d) ✔ Set "%s" is OK', $this->finished, $this->total, $set);
        $this->symfonyStyle->writeln($message);
    }

    private function printError(string $set, string $output): void
    {
        $message = sprintf('(%d/%d) ❌ Set "%s" failed: %s', $this->finished, $this->total, $set, $output);
        $this->symfonyStyle->writeln($message);
    }
}
