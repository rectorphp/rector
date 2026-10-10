<?php

declare (strict_types=1);
namespace Rector\Parallel\Experimental;

use RectorPrefix202610\Clue\React\NDJson\Decoder;
use RectorPrefix202610\Clue\React\NDJson\Encoder;
use RectorPrefix202610\Nette\Utils\Random;
use RectorPrefix202610\React\EventLoop\StreamSelectLoop;
use RectorPrefix202610\React\Socket\ConnectionInterface;
use RectorPrefix202610\React\Socket\TcpServer;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Console\Command\ProcessCommand;
use Rector\Parallel\Application\ParallelResultCollector;
use Rector\Parallel\Command\WorkerCommandLineFactory;
use Rector\Parallel\Enum\Action;
use Rector\Parallel\Enum\Content;
use Rector\Parallel\Enum\ReactCommand;
use Rector\Parallel\Enum\ReactEvent;
use Rector\Parallel\Experimental\ValueObject\BucketSchedule;
use Rector\Parallel\ValueObject\Bridge;
use Rector\Parallel\ValueObject\ParallelProcess;
use Rector\Parallel\ValueObject\ProcessPool;
use Rector\ValueObject\Error\SystemError;
use Rector\ValueObject\ProcessResult;
use RectorPrefix202610\Symfony\Component\Console\Command\Command;
use RectorPrefix202610\Symfony\Component\Console\Input\InputInterface;
use Throwable;
/**
 * @experimental Alternative to @see \Rector\Parallel\Application\ParallelFileProcessor, used by
 * "--lpt".
 *
 * Two differences, both on purpose:
 *
 * 1. a worker pulls only from its own bucket, decided up front by @see LptScheduleFactory, instead of
 *    from a shared job pool;
 * 2. a worker is never killed and respawned mid-run, so its warm in-memory caches (reflection, parsed
 *    AST, node scope resolver) survive the whole bucket. The default processor recycles a worker every
 *    MAX_CHUNKS_PER_WORKER chunks, which throws those caches away every 128 files at jobSize 16.
 *
 * The bucket is still handed over in small chunks: a worker response is what advances the progress bar
 * and re-arms the per-job timeout. One single request per worker would freeze the progress bar and put
 * the whole bucket under one 120s timeout.
 */
final class ExperimentalParallelFileProcessor
{
    /**
     * @readonly
     */
    private WorkerCommandLineFactory $workerCommandLineFactory;
    /**
     * @var int
     */
    private const SYSTEM_ERROR_LIMIT = 50;
    private ProcessPool $processPool;
    public function __construct(WorkerCommandLineFactory $workerCommandLineFactory)
    {
        $this->workerCommandLineFactory = $workerCommandLineFactory;
    }
    /**
     * @param callable(int $stepCount): void $postFileCallback Used for progress bar jump
     */
    public function process(BucketSchedule $bucketSchedule, string $mainScript, callable $postFileCallback, InputInterface $input): ProcessResult
    {
        // reversed, so that array_pop() hands out the biggest files first
        $jobsPerWorker = array_map(static fn(array $jobs): array => array_reverse($jobs), $bucketSchedule->getJobsPerWorker());
        /** @var array<string, int> $bucketKeyByIdentifier */
        $bucketKeyByIdentifier = [];
        $streamSelectLoop = new StreamSelectLoop();
        $parallelResultCollector = new ParallelResultCollector();
        $tcpServer = new TcpServer('127.0.0.1:0', $streamSelectLoop);
        $this->processPool = new ProcessPool($tcpServer);
        $tcpServer->on(ReactEvent::CONNECTION, function (ConnectionInterface $connection) use (&$jobsPerWorker, &$bucketKeyByIdentifier): void {
            $inDecoder = new Decoder($connection, \true, 512, 0, 4 * 1024 * 1024);
            $outEncoder = new Encoder($connection);
            $inDecoder->on(ReactEvent::DATA, function (array $data) use (&$jobsPerWorker, &$bucketKeyByIdentifier, $inDecoder, $outEncoder): void {
                $action = $data[ReactCommand::ACTION];
                if ($action !== Action::HELLO) {
                    return;
                }
                $processIdentifier = $data[Option::PARALLEL_IDENTIFIER];
                $parallelProcess = $this->processPool->getProcess($processIdentifier);
                $parallelProcess->bindConnection($inDecoder, $outEncoder);
                $jobsChunk = $this->popJobChunk($processIdentifier, $jobsPerWorker, $bucketKeyByIdentifier);
                if ($jobsChunk === null) {
                    $this->processPool->quitProcess($processIdentifier);
                    return;
                }
                $parallelProcess->request([ReactCommand::ACTION => Action::MAIN, Content::FILES => $jobsChunk]);
            });
        });
        /** @var string $serverAddress */
        $serverAddress = $tcpServer->getAddress();
        /** @var int $serverPort */
        $serverPort = parse_url($serverAddress, \PHP_URL_PORT);
        $systemErrorsCount = 0;
        $reachedSystemErrorsCountLimit = \false;
        $handleErrorCallable = function (Throwable $throwable) use ($parallelResultCollector, &$systemErrorsCount, &$reachedSystemErrorsCountLimit): void {
            $parallelResultCollector->collectSystemError(new SystemError($throwable->getMessage(), $throwable->getFile(), $throwable->getLine()));
            ++$systemErrorsCount;
            $reachedSystemErrorsCountLimit = \true;
            $this->processPool->quitAll();
            // give the child processes time to actually die, they can still write to cache meanwhile
            // @see https://github.com/rectorphp/rector-src/pull/3834/files#r1231696531
            sleep(1);
        };
        $timeoutInSeconds = SimpleParameterProvider::provideIntParameter(Option::PARALLEL_JOB_TIMEOUT_IN_SECONDS);
        $processSpawner = function (int $bucketKey) use ($parallelResultCollector, &$jobsPerWorker, &$bucketKeyByIdentifier, $postFileCallback, &$systemErrorsCount, &$reachedInternalErrorsCountLimit, $mainScript, $input, $serverPort, $streamSelectLoop, $timeoutInSeconds, $handleErrorCallable): void {
            $processIdentifier = Random::generate();
            $bucketKeyByIdentifier[$processIdentifier] = $bucketKey;
            $workerCommandLine = $this->workerCommandLineFactory->create($mainScript, ProcessCommand::class, 'worker', $input, $processIdentifier, $serverPort);
            $parallelProcess = new ParallelProcess($workerCommandLine, $streamSelectLoop, $timeoutInSeconds);
            $parallelProcess->start(
                // 1. callable on data
                function (array $json) use ($parallelProcess, $parallelResultCollector, &$jobsPerWorker, &$bucketKeyByIdentifier, $postFileCallback, &$systemErrorsCount, &$reachedInternalErrorsCountLimit, $processIdentifier): void {
                    /** @var array{
                     *      total_changed: int,
                     *      system_errors: mixed[],
                     *      file_diffs: array<string, mixed>,
                     *      files_count: int,
                     *      system_errors_count: int,
                     *      used_skips: array<string, string[]>
                     * } $json */
                    $postFileCallback($parallelResultCollector->collectWorkerResult($json));
                    $systemErrorsCount += $json[Bridge::SYSTEM_ERRORS_COUNT];
                    if ($systemErrorsCount >= self::SYSTEM_ERROR_LIMIT) {
                        $reachedInternalErrorsCountLimit = \true;
                        $this->processPool->quitAll();
                    }
                    $jobsChunk = $this->popJobChunk($processIdentifier, $jobsPerWorker, $bucketKeyByIdentifier);
                    if ($jobsChunk === null) {
                        // bucket done, this worker has nothing left to do
                        $this->processPool->quitProcess($processIdentifier);
                        return;
                    }
                    $parallelProcess->request([ReactCommand::ACTION => Action::MAIN, Content::FILES => $jobsChunk]);
                },
                // 2. callable on error
                $handleErrorCallable,
                // 3. callable on exit
                function ($exitCode, string $stdErr) use ($parallelResultCollector, $processIdentifier): void {
                    $this->processPool->tryQuitProcess($processIdentifier);
                    if ($exitCode === Command::SUCCESS) {
                        return;
                    }
                    if ($exitCode === null) {
                        return;
                    }
                    $parallelResultCollector->collectSystemError(new SystemError('Child process error: ' . $stdErr));
                }
            );
            $this->processPool->attachProcess($processIdentifier, $parallelProcess);
        };
        foreach (array_keys($jobsPerWorker) as $bucketKey) {
            $processSpawner($bucketKey);
        }
        $streamSelectLoop->run();
        if ($reachedSystemErrorsCountLimit) {
            $parallelResultCollector->collectSystemError(new SystemError(sprintf('Reached system errors count limit of %d, exiting...', self::SYSTEM_ERROR_LIMIT)));
        }
        return $parallelResultCollector->createProcessResult();
    }
    /**
     * @param array<int, array<int, array<string>>> $jobsPerWorker
     * @param array<string, int> $bucketKeyByIdentifier
     * @return array<string>|null
     */
    private function popJobChunk(string $processIdentifier, array &$jobsPerWorker, array $bucketKeyByIdentifier): ?array
    {
        $bucketKey = $bucketKeyByIdentifier[$processIdentifier] ?? null;
        if ($bucketKey !== null && ($jobsPerWorker[$bucketKey] ?? []) !== []) {
            return array_pop($jobsPerWorker[$bucketKey]);
        }
        if (getenv('RECTOR_LPT_NO_STEAL') !== \false) {
            return null;
        }
        return $this->stealJobChunk($jobsPerWorker);
    }
    /**
     * Own bucket drained while others still have work. Byte size only estimates the cost of a file, and
     * not every core is equally fast - on a big.LITTLE CPU (Apple silicon, recent Intel) a bucket handed
     * to an efficiency core takes several times longer. Take over a chunk from whoever has the most left,
     * so the run is not held up by one straggler.
     *
     * @param array<int, array<int, array<string>>> $jobsPerWorker
     * @return array<string>|null
     */
    private function stealJobChunk(array &$jobsPerWorker): ?array
    {
        $fattestBucketKey = null;
        $fattestJobCount = 0;
        foreach ($jobsPerWorker as $currentBucketKey => $jobs) {
            $jobCount = count($jobs);
            if ($jobCount <= $fattestJobCount) {
                continue;
            }
            $fattestJobCount = $jobCount;
            $fattestBucketKey = $currentBucketKey;
        }
        if ($fattestBucketKey === null) {
            return null;
        }
        return array_pop($jobsPerWorker[$fattestBucketKey]);
    }
}
