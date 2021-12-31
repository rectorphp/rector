<?php

declare (strict_types=1);
namespace Rector\Parallel\Application;

use Closure;
use RectorPrefix20211231\Clue\React\NDJson\Decoder;
use RectorPrefix20211231\Clue\React\NDJson\Encoder;
use RectorPrefix20211231\Nette\Utils\Random;
use RectorPrefix20211231\React\EventLoop\StreamSelectLoop;
use RectorPrefix20211231\React\Socket\ConnectionInterface;
use RectorPrefix20211231\React\Socket\TcpServer;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Command\ProcessCommand;
use Rector\Core\Console\Command\WorkerCommand;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\Command\WorkerCommandLineFactory;
use Rector\Parallel\ValueObject\Bridge;
use RectorPrefix20211231\Symfony\Component\Console\Command\Command;
use RectorPrefix20211231\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20211231\Symplify\EasyParallel\Enum\Action;
use RectorPrefix20211231\Symplify\EasyParallel\Enum\Content;
use RectorPrefix20211231\Symplify\EasyParallel\Enum\ReactCommand;
use RectorPrefix20211231\Symplify\EasyParallel\Enum\ReactEvent;
use RectorPrefix20211231\Symplify\EasyParallel\ValueObject\ParallelProcess;
use RectorPrefix20211231\Symplify\EasyParallel\ValueObject\ProcessPool;
use RectorPrefix20211231\Symplify\EasyParallel\ValueObject\Schedule;
use RectorPrefix20211231\Symplify\PackageBuilder\Console\Command\CommandNaming;
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
    public const TIMEOUT_IN_SECONDS = 60;
    /**
     * @var int
     */
    private const SYSTEM_ERROR_COUNT_LIMIT = 20;
    /**
     * @var \Symplify\EasyParallel\ValueObject\ProcessPool|null
     */
    private $processPool = null;
    /**
     * @readonly
     * @var \Rector\Parallel\Command\WorkerCommandLineFactory
     */
    private $workerCommandLineFactory;
    public function __construct(\Rector\Parallel\Command\WorkerCommandLineFactory $workerCommandLineFactory)
    {
        $this->workerCommandLineFactory = $workerCommandLineFactory;
    }
    /**
     * @param Closure(int): void|null $postFileCallback Used for progress bar jump
     * @return mixed[]
     */
    public function process(\RectorPrefix20211231\Symplify\EasyParallel\ValueObject\Schedule $schedule, string $mainScript, \Closure $postFileCallback, ?string $projectConfigFile, \RectorPrefix20211231\Symfony\Component\Console\Input\InputInterface $input) : array
    {
        $jobs = \array_reverse($schedule->getJobs());
        $streamSelectLoop = new \RectorPrefix20211231\React\EventLoop\StreamSelectLoop();
        // basic properties setup
        $numberOfProcesses = $schedule->getNumberOfProcesses();
        // initial counters
        $fileDiffs = [];
        $systemErrors = [];
        $tcpServer = new \RectorPrefix20211231\React\Socket\TcpServer('127.0.0.1:0', $streamSelectLoop);
        $this->processPool = new \RectorPrefix20211231\Symplify\EasyParallel\ValueObject\ProcessPool($tcpServer);
        $tcpServer->on(\RectorPrefix20211231\Symplify\EasyParallel\Enum\ReactEvent::CONNECTION, function (\RectorPrefix20211231\React\Socket\ConnectionInterface $connection) use(&$jobs) : void {
            $inDecoder = new \RectorPrefix20211231\Clue\React\NDJson\Decoder($connection, \true, 512, 0, 4 * 1024 * 1024);
            $outEncoder = new \RectorPrefix20211231\Clue\React\NDJson\Encoder($connection);
            $inDecoder->on(\RectorPrefix20211231\Symplify\EasyParallel\Enum\ReactEvent::DATA, function (array $data) use(&$jobs, $inDecoder, $outEncoder) : void {
                $action = $data[\RectorPrefix20211231\Symplify\EasyParallel\Enum\ReactCommand::ACTION];
                if ($action !== \RectorPrefix20211231\Symplify\EasyParallel\Enum\Action::HELLO) {
                    return;
                }
                $processIdentifier = $data[\Rector\Core\Configuration\Option::PARALLEL_IDENTIFIER];
                $parallelProcess = $this->processPool->getProcess($processIdentifier);
                $parallelProcess->bindConnection($inDecoder, $outEncoder);
                if ($jobs === []) {
                    $this->processPool->quitProcess($processIdentifier);
                    return;
                }
                $job = \array_pop($jobs);
                $parallelProcess->request([\RectorPrefix20211231\Symplify\EasyParallel\Enum\ReactCommand::ACTION => \RectorPrefix20211231\Symplify\EasyParallel\Enum\Action::MAIN, \RectorPrefix20211231\Symplify\EasyParallel\Enum\Content::FILES => $job]);
            });
        });
        /** @var string $serverAddress */
        $serverAddress = $tcpServer->getAddress();
        /** @var int $serverPort */
        $serverPort = \parse_url($serverAddress, \PHP_URL_PORT);
        $systemErrorsCount = 0;
        $reachedSystemErrorsCountLimit = \false;
        $handleErrorCallable = function (\Throwable $throwable) use(&$systemErrors, &$systemErrorsCount, &$reachedSystemErrorsCountLimit) : void {
            $systemErrors[] = new \Rector\Core\ValueObject\Error\SystemError($throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
            ++$systemErrorsCount;
            $reachedSystemErrorsCountLimit = \true;
            $this->processPool->quitAll();
        };
        for ($i = 0; $i < $numberOfProcesses; ++$i) {
            // nothing else to process, stop now
            if ($jobs === []) {
                break;
            }
            $processIdentifier = \RectorPrefix20211231\Nette\Utils\Random::generate();
            $workerCommandLine = $this->workerCommandLineFactory->create($mainScript, \Rector\Core\Console\Command\ProcessCommand::class, \RectorPrefix20211231\Symplify\PackageBuilder\Console\Command\CommandNaming::classToName(\Rector\Core\Console\Command\WorkerCommand::class), $projectConfigFile, $input, $processIdentifier, $serverPort);
            $parallelProcess = new \RectorPrefix20211231\Symplify\EasyParallel\ValueObject\ParallelProcess($workerCommandLine, $streamSelectLoop, self::TIMEOUT_IN_SECONDS);
            $parallelProcess->start(
                // 1. callable on data
                function (array $json) use($parallelProcess, &$systemErrors, &$fileDiffs, &$jobs, $postFileCallback, &$systemErrorsCount, &$reachedInternalErrorsCountLimit, $processIdentifier) : void {
                    // decode arrays to objects
                    foreach ($json[\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS] as $jsonError) {
                        if (\is_string($jsonError)) {
                            $systemErrors[] = 'System error: ' . $jsonError;
                            continue;
                        }
                        $systemErrors[] = \Rector\Core\ValueObject\Error\SystemError::decode($jsonError);
                    }
                    foreach ($json[\Rector\Parallel\ValueObject\Bridge::FILE_DIFFS] as $jsonError) {
                        $fileDiffs[] = \Rector\Core\ValueObject\Reporting\FileDiff::decode($jsonError);
                    }
                    $postFileCallback($json[\Rector\Parallel\ValueObject\Bridge::FILES_COUNT]);
                    $systemErrorsCount += $json[\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS_COUNT];
                    if ($systemErrorsCount >= self::SYSTEM_ERROR_COUNT_LIMIT) {
                        $reachedInternalErrorsCountLimit = \true;
                        $this->processPool->quitAll();
                    }
                    if ($jobs === []) {
                        $this->processPool->quitProcess($processIdentifier);
                        return;
                    }
                    $job = \array_pop($jobs);
                    $parallelProcess->request([\RectorPrefix20211231\Symplify\EasyParallel\Enum\ReactCommand::ACTION => \RectorPrefix20211231\Symplify\EasyParallel\Enum\Action::MAIN, \RectorPrefix20211231\Symplify\EasyParallel\Enum\Content::FILES => $job]);
                },
                // 2. callable on error
                $handleErrorCallable,
                // 3. callable on exit
                function ($exitCode, string $stdErr) use(&$systemErrors, $processIdentifier) : void {
                    $this->processPool->tryQuitProcess($processIdentifier);
                    if ($exitCode === \RectorPrefix20211231\Symfony\Component\Console\Command\Command::SUCCESS) {
                        return;
                    }
                    if ($exitCode === null) {
                        return;
                    }
                    $systemErrors[] = 'Child process error: ' . $stdErr;
                }
            );
            $this->processPool->attachProcess($processIdentifier, $parallelProcess);
        }
        $streamSelectLoop->run();
        if ($reachedSystemErrorsCountLimit) {
            $systemErrors[] = \sprintf('Reached system errors count limit of %d, exiting...', self::SYSTEM_ERROR_COUNT_LIMIT);
        }
        return [\Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => $fileDiffs, \Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => $systemErrors, \Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS_COUNT => \count($systemErrors)];
    }
}
