<?php

declare (strict_types=1);
namespace Rector\Parallel\Application;

use RectorPrefix202212\Clue\React\NDJson\Decoder;
use RectorPrefix202212\Clue\React\NDJson\Encoder;
use RectorPrefix202212\Nette\Utils\Random;
use RectorPrefix202212\React\EventLoop\StreamSelectLoop;
use RectorPrefix202212\React\Socket\ConnectionInterface;
use RectorPrefix202212\React\Socket\TcpServer;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\Console\Command\ProcessCommand;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\Command\WorkerCommandLineFactory;
use Rector\Parallel\ValueObject\Bridge;
use RectorPrefix202212\Symfony\Component\Console\Command\Command;
use RectorPrefix202212\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202212\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202212\Symplify\EasyParallel\Enum\Action;
use RectorPrefix202212\Symplify\EasyParallel\Enum\Content;
use RectorPrefix202212\Symplify\EasyParallel\Enum\ReactCommand;
use RectorPrefix202212\Symplify\EasyParallel\Enum\ReactEvent;
use RectorPrefix202212\Symplify\EasyParallel\ValueObject\ParallelProcess;
use RectorPrefix202212\Symplify\EasyParallel\ValueObject\ProcessPool;
use RectorPrefix202212\Symplify\EasyParallel\ValueObject\Schedule;
use Throwable;
/**
 * Inspired from @see
 * https://github.com/phpstan/phpstan-src/commit/9124c66dcc55a222e21b1717ba5f60771f7dda92#diff-39c7a3b0cbb217bbfff96fbb454e6e5e60c74cf92fbb0f9d246b8bebbaad2bb0
 *
 * https://github.com/phpstan/phpstan-src/commit/b84acd2e3eadf66189a64fdbc6dd18ff76323f67#diff-7f625777f1ce5384046df08abffd6c911cfbb1cfc8fcb2bdeaf78f337689e3e2R150
 */
final class ParallelFileProcessor
{
    /**
     * @var int
     */
    private const SYSTEM_ERROR_LIMIT = 50;
    /**
     * @var \Symplify\EasyParallel\ValueObject\ProcessPool|null
     */
    private $processPool = null;
    /**
     * @readonly
     * @var \Rector\Parallel\Command\WorkerCommandLineFactory
     */
    private $workerCommandLineFactory;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    public function __construct(WorkerCommandLineFactory $workerCommandLineFactory, ParameterProvider $parameterProvider)
    {
        $this->workerCommandLineFactory = $workerCommandLineFactory;
        $this->parameterProvider = $parameterProvider;
    }
    /**
     * @param callable(int $stepCount): void $postFileCallback Used for progress bar jump
     * @return array{file_diffs: SerializableInterface[], system_errors: SerializableInterface[], system_errors_count: int}
     */
    public function process(Schedule $schedule, string $mainScript, callable $postFileCallback, InputInterface $input) : array
    {
        $jobs = \array_reverse($schedule->getJobs());
        $streamSelectLoop = new StreamSelectLoop();
        // basic properties setup
        $numberOfProcesses = $schedule->getNumberOfProcesses();
        // initial counters
        $fileDiffs = [];
        /** @var SystemError[] $systemErrors */
        $systemErrors = [];
        $tcpServer = new TcpServer('127.0.0.1:0', $streamSelectLoop);
        $this->processPool = new ProcessPool($tcpServer);
        $tcpServer->on(ReactEvent::CONNECTION, function (ConnectionInterface $connection) use(&$jobs) : void {
            $inDecoder = new Decoder($connection, \true, 512, 0, 4 * 1024 * 1024);
            $outEncoder = new Encoder($connection);
            $inDecoder->on(ReactEvent::DATA, function (array $data) use(&$jobs, $inDecoder, $outEncoder) : void {
                $action = $data[ReactCommand::ACTION];
                if ($action !== Action::HELLO) {
                    return;
                }
                $processIdentifier = $data[Option::PARALLEL_IDENTIFIER];
                $parallelProcess = $this->processPool->getProcess($processIdentifier);
                $parallelProcess->bindConnection($inDecoder, $outEncoder);
                if ($jobs === []) {
                    $this->processPool->quitProcess($processIdentifier);
                    return;
                }
                $job = \array_pop($jobs);
                $parallelProcess->request([ReactCommand::ACTION => Action::MAIN, Content::FILES => $job]);
            });
        });
        /** @var string $serverAddress */
        $serverAddress = $tcpServer->getAddress();
        /** @var int $serverPort */
        $serverPort = \parse_url($serverAddress, \PHP_URL_PORT);
        $systemErrorsCount = 0;
        $reachedSystemErrorsCountLimit = \false;
        $handleErrorCallable = function (Throwable $throwable) use(&$systemErrors, &$systemErrorsCount, &$reachedSystemErrorsCountLimit) : void {
            $systemErrors[] = new SystemError($throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
            ++$systemErrorsCount;
            $reachedSystemErrorsCountLimit = \true;
            $this->processPool->quitAll();
        };
        $timeoutInSeconds = $this->parameterProvider->provideIntParameter(Option::PARALLEL_TIMEOUT_IN_SECONDS);
        for ($i = 0; $i < $numberOfProcesses; ++$i) {
            // nothing else to process, stop now
            if ($jobs === []) {
                break;
            }
            $processIdentifier = Random::generate();
            $workerCommandLine = $this->workerCommandLineFactory->create($mainScript, ProcessCommand::class, 'worker', $input, $processIdentifier, $serverPort);
            $parallelProcess = new ParallelProcess($workerCommandLine, $streamSelectLoop, $timeoutInSeconds);
            $parallelProcess->start(
                // 1. callable on data
                function (array $json) use($parallelProcess, &$systemErrors, &$fileDiffs, &$jobs, $postFileCallback, &$systemErrorsCount, &$reachedInternalErrorsCountLimit, $processIdentifier) : void {
                    // decode arrays to objects
                    foreach ($json[Bridge::SYSTEM_ERRORS] as $jsonError) {
                        if (\is_string($jsonError)) {
                            $systemErrors[] = new SystemError('System error: ' . $jsonError);
                            continue;
                        }
                        $systemErrors[] = SystemError::decode($jsonError);
                    }
                    foreach ($json[Bridge::FILE_DIFFS] as $jsonError) {
                        $fileDiffs[] = FileDiff::decode($jsonError);
                    }
                    $postFileCallback($json[Bridge::FILES_COUNT]);
                    $systemErrorsCount += $json[Bridge::SYSTEM_ERRORS_COUNT];
                    if ($systemErrorsCount >= self::SYSTEM_ERROR_LIMIT) {
                        $reachedInternalErrorsCountLimit = \true;
                        $this->processPool->quitAll();
                    }
                    if ($jobs === []) {
                        $this->processPool->quitProcess($processIdentifier);
                        return;
                    }
                    $job = \array_pop($jobs);
                    $parallelProcess->request([ReactCommand::ACTION => Action::MAIN, Content::FILES => $job]);
                },
                // 2. callable on error
                $handleErrorCallable,
                // 3. callable on exit
                function ($exitCode, string $stdErr) use(&$systemErrors, $processIdentifier) : void {
                    $this->processPool->tryQuitProcess($processIdentifier);
                    if ($exitCode === Command::SUCCESS) {
                        return;
                    }
                    if ($exitCode === null) {
                        return;
                    }
                    $systemErrors[] = new SystemError('Child process error: ' . $stdErr);
                }
            );
            $this->processPool->attachProcess($processIdentifier, $parallelProcess);
        }
        $streamSelectLoop->run();
        if ($reachedSystemErrorsCountLimit) {
            $systemErrors[] = new SystemError(\sprintf('Reached system errors count limit of %d, exiting...', self::SYSTEM_ERROR_LIMIT));
        }
        return [Bridge::FILE_DIFFS => $fileDiffs, Bridge::SYSTEM_ERRORS => $systemErrors, Bridge::SYSTEM_ERRORS_COUNT => \count($systemErrors)];
    }
}
