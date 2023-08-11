<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use RectorPrefix202308\Nette\Utils\FileSystem as UtilsFileSystem;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\Util\ArrayParametersMerger;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Core\ValueObjectFactory\Application\FileFactory;
use Rector\Parallel\Application\ParallelFileProcessor;
use Rector\Parallel\ValueObject\Bridge;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use RectorPrefix202308\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202308\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202308\Symplify\EasyParallel\CpuCoreCountProvider;
use RectorPrefix202308\Symplify\EasyParallel\Exception\ParallelShouldNotHappenException;
use RectorPrefix202308\Symplify\EasyParallel\ScheduleFactory;
use Throwable;
use RectorPrefix202308\Webmozart\Assert\Assert;
final class ApplicationFileProcessor
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Rector\Core\ValueObjectFactory\Application\FileFactory
     */
    private $fileFactory;
    /**
     * @readonly
     * @var \Rector\Core\Util\ArrayParametersMerger
     */
    private $arrayParametersMerger;
    /**
     * @readonly
     * @var \Rector\Parallel\Application\ParallelFileProcessor
     */
    private $parallelFileProcessor;
    /**
     * @readonly
     * @var \Symplify\EasyParallel\ScheduleFactory
     */
    private $scheduleFactory;
    /**
     * @readonly
     * @var \Symplify\EasyParallel\CpuCoreCountProvider
     */
    private $cpuCoreCountProvider;
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @var FileProcessorInterface[]
     * @readonly
     */
    private $fileProcessors;
    /**
     * @var string
     */
    private const ARGV = 'argv';
    /**
     * @var SystemError[]
     */
    private $systemErrors = [];
    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(SymfonyStyle $symfonyStyle, FileFactory $fileFactory, ArrayParametersMerger $arrayParametersMerger, ParallelFileProcessor $parallelFileProcessor, ScheduleFactory $scheduleFactory, CpuCoreCountProvider $cpuCoreCountProvider, ChangedFilesDetector $changedFilesDetector, CurrentFileProvider $currentFileProvider, iterable $fileProcessors)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->fileFactory = $fileFactory;
        $this->arrayParametersMerger = $arrayParametersMerger;
        $this->parallelFileProcessor = $parallelFileProcessor;
        $this->scheduleFactory = $scheduleFactory;
        $this->cpuCoreCountProvider = $cpuCoreCountProvider;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->currentFileProvider = $currentFileProvider;
        $this->fileProcessors = $fileProcessors;
        $fileProcessorClasses = [];
        foreach ($fileProcessors as $fileProcessor) {
            $fileProcessorClasses[] = \get_class($fileProcessor);
        }
        Assert::uniqueValues($fileProcessorClasses);
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function run(Configuration $configuration, InputInterface $input) : array
    {
        $filePaths = $this->fileFactory->findFilesInPaths($configuration->getPaths(), $configuration);
        // no files found
        if ($filePaths === []) {
            return [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => []];
        }
        $this->configureCustomErrorHandler();
        if ($configuration->isParallel()) {
            $systemErrorsAndFileDiffs = $this->runParallel($filePaths, $configuration, $input);
        } else {
            $systemErrorsAndFileDiffs = $this->processFiles($filePaths, $configuration, \false);
        }
        $systemErrorsAndFileDiffs[Bridge::SYSTEM_ERRORS] = \array_merge($systemErrorsAndFileDiffs[Bridge::SYSTEM_ERRORS], $this->systemErrors);
        $this->restoreErrorHandler();
        return $systemErrorsAndFileDiffs;
    }
    /**
     * @param string[] $filePaths
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[], system_errors_count: int}
     */
    public function processFiles(array $filePaths, Configuration $configuration, bool $isParallel = \true) : array
    {
        $shouldShowProgressBar = $configuration->shouldShowProgressBar();
        // progress bar on parallel handled on runParallel()
        if (!$isParallel && $shouldShowProgressBar) {
            $fileCount = \count($filePaths);
            $this->symfonyStyle->progressStart($fileCount);
            $this->symfonyStyle->progressAdvance(0);
        }
        $systemErrorsAndFileDiffs = [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => [], Bridge::SYSTEM_ERRORS_COUNT => 0];
        foreach ($filePaths as $filePath) {
            $file = new File($filePath, UtilsFileSystem::read($filePath));
            try {
                $systemErrorsAndFileDiffs = $this->processFile($file, $systemErrorsAndFileDiffs, $configuration);
                // progress bar +1,
                // progress bar on parallel handled on runParallel()
                if (!$isParallel && $shouldShowProgressBar) {
                    $this->symfonyStyle->progressAdvance();
                }
            } catch (Throwable $throwable) {
                $this->changedFilesDetector->invalidateFile($filePath);
                if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
                    throw $throwable;
                }
                $systemErrorsAndFileDiffs[Bridge::SYSTEM_ERRORS][] = $this->resolveSystemError($throwable, $filePath);
            }
        }
        return $systemErrorsAndFileDiffs;
    }
    /**
     * @param array{system_errors: SystemError[], file_diffs: FileDiff[], system_errors_count: int} $systemErrorsAndFileDiffs
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[], system_errors_count: int}
     */
    private function processFile(File $file, array $systemErrorsAndFileDiffs, Configuration $configuration) : array
    {
        $this->currentFileProvider->setFile($file);
        foreach ($this->fileProcessors as $fileProcessor) {
            if (!$fileProcessor->supports($file, $configuration)) {
                continue;
            }
            $result = $fileProcessor->process($file, $configuration);
            $systemErrorsAndFileDiffs = $this->arrayParametersMerger->merge($systemErrorsAndFileDiffs, $result);
        }
        if ($systemErrorsAndFileDiffs[Bridge::SYSTEM_ERRORS] !== []) {
            $this->changedFilesDetector->invalidateFile($file->getFilePath());
        } elseif (!$configuration->isDryRun() || $systemErrorsAndFileDiffs[Bridge::FILE_DIFFS] === []) {
            $this->changedFilesDetector->cacheFile($file->getFilePath());
        }
        return $systemErrorsAndFileDiffs;
    }
    /**
     * @param string|\Rector\Core\ValueObject\Application\File $filePath
     */
    private function resolveSystemError(Throwable $throwable, $filePath) : SystemError
    {
        $errorMessage = \sprintf('System error: "%s"', $throwable->getMessage()) . \PHP_EOL;
        $filePath = $filePath instanceof File ? $filePath->getFilePath() : $filePath;
        if ($this->symfonyStyle->isDebug()) {
            return new SystemError($errorMessage . \PHP_EOL . 'Stack trace:' . \PHP_EOL . $throwable->getTraceAsString(), $filePath, $throwable->getLine());
        }
        $errorMessage .= 'Run Rector with "--debug" option and post the report here: https://github.com/rectorphp/rector/issues/new';
        return new SystemError($errorMessage, $filePath, $throwable->getLine());
    }
    /**
     * Inspired by @see https://github.com/phpstan/phpstan-src/blob/89af4e7db257750cdee5d4259ad312941b6b25e8/src/Analyser/Analyser.php#L134
     */
    private function configureCustomErrorHandler() : void
    {
        $errorHandlerCallback = function (int $code, string $message, string $file, int $line) : bool {
            if ((\error_reporting() & $code) === 0) {
                // silence @ operator
                return \true;
            }
            // not relevant for us
            if (\in_array($code, [\E_DEPRECATED, \E_WARNING], \true)) {
                return \true;
            }
            $this->systemErrors[] = new SystemError($message, $file, $line);
            return \true;
        };
        \set_error_handler($errorHandlerCallback);
    }
    private function restoreErrorHandler() : void
    {
        \restore_error_handler();
    }
    /**
     * @param string[] $filePaths
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    private function runParallel(array $filePaths, Configuration $configuration, InputInterface $input) : array
    {
        $schedule = $this->scheduleFactory->create($this->cpuCoreCountProvider->provide(), SimpleParameterProvider::provideIntParameter(Option::PARALLEL_JOB_SIZE), SimpleParameterProvider::provideIntParameter(Option::PARALLEL_MAX_NUMBER_OF_PROCESSES), $filePaths);
        $postFileCallback = static function (int $stepCount) : void {
        };
        if ($configuration->shouldShowProgressBar()) {
            $fileCount = \count($filePaths);
            $this->symfonyStyle->progressStart($fileCount);
            $this->symfonyStyle->progressAdvance(0);
            $postFileCallback = function (int $stepCount) : void {
                $this->symfonyStyle->progressAdvance($stepCount);
                // running in parallel here → nothing else to do
            };
        }
        $mainScript = $this->resolveCalledRectorBinary();
        if ($mainScript === null) {
            throw new ParallelShouldNotHappenException('[parallel] Main script was not found');
        }
        // mimics see https://github.com/phpstan/phpstan-src/commit/9124c66dcc55a222e21b1717ba5f60771f7dda92#diff-387b8f04e0db7a06678eb52ce0c0d0aff73e0d7d8fc5df834d0a5fbec198e5daR139
        return $this->parallelFileProcessor->process($schedule, $mainScript, $postFileCallback, $input);
    }
    /**
     * Path to called "rector" binary file, e.g. "vendor/bin/rector" returns "vendor/bin/rector" This is needed to re-call the
     * ecs binary in sub-process in the same location.
     */
    private function resolveCalledRectorBinary() : ?string
    {
        if (!isset($_SERVER[self::ARGV][0])) {
            return null;
        }
        $potentialEcsBinaryPath = $_SERVER[self::ARGV][0];
        if (!\file_exists($potentialEcsBinaryPath)) {
            return null;
        }
        return $potentialEcsBinaryPath;
    }
}
