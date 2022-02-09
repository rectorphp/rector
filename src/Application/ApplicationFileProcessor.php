<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use PHPStan\Analyser\NodeScopeResolver;
use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Core\ValueObjectFactory\Application\FileFactory;
use Rector\FileFormatter\FileFormatter;
use Rector\Parallel\Application\ParallelFileProcessor;
use Rector\Parallel\ValueObject\Bridge;
use RectorPrefix20220209\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220209\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20220209\Symplify\EasyParallel\CpuCoreCountProvider;
use RectorPrefix20220209\Symplify\EasyParallel\Exception\ParallelShouldNotHappenException;
use RectorPrefix20220209\Symplify\EasyParallel\FileSystem\FilePathNormalizer;
use RectorPrefix20220209\Symplify\EasyParallel\ScheduleFactory;
use RectorPrefix20220209\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20220209\Symplify\PackageBuilder\Yaml\ParametersMerger;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20220209\Symplify\SmartFileSystem\SmartFileSystem;
use RectorPrefix20220209\Webmozart\Assert\Assert;
final class ApplicationFileProcessor
{
    /**
     * @var string
     */
    private const ARGV = 'argv';
    /**
     * @var SystemError[]
     */
    private $systemErrors = [];
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileDecorator\FileDiffFileDecorator
     */
    private $fileDiffFileDecorator;
    /**
     * @readonly
     * @var \Rector\FileFormatter\FileFormatter
     */
    private $fileFormatter;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor
     */
    private $removedAndAddedFilesProcessor;
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
     * @var \PHPStan\Analyser\NodeScopeResolver
     */
    private $nodeScopeResolver;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Yaml\ParametersMerger
     */
    private $parametersMerger;
    /**
     * @readonly
     * @var \Rector\Parallel\Application\ParallelFileProcessor
     */
    private $parallelFileProcessor;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Symplify\EasyParallel\ScheduleFactory
     */
    private $scheduleFactory;
    /**
     * @readonly
     * @var \Symplify\EasyParallel\FileSystem\FilePathNormalizer
     */
    private $filePathNormalizer;
    /**
     * @readonly
     * @var \Symplify\EasyParallel\CpuCoreCountProvider
     */
    private $cpuCoreCountProvider;
    /**
     * @var FileProcessorInterface[]
     * @readonly
     */
    private $fileProcessors = [];
    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(\RectorPrefix20220209\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Rector\Core\Application\FileDecorator\FileDiffFileDecorator $fileDiffFileDecorator, \Rector\FileFormatter\FileFormatter $fileFormatter, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor $removedAndAddedFilesProcessor, \RectorPrefix20220209\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, \Rector\Core\ValueObjectFactory\Application\FileFactory $fileFactory, \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver, \RectorPrefix20220209\Symplify\PackageBuilder\Yaml\ParametersMerger $parametersMerger, \Rector\Parallel\Application\ParallelFileProcessor $parallelFileProcessor, \RectorPrefix20220209\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \RectorPrefix20220209\Symplify\EasyParallel\ScheduleFactory $scheduleFactory, \RectorPrefix20220209\Symplify\EasyParallel\FileSystem\FilePathNormalizer $filePathNormalizer, \RectorPrefix20220209\Symplify\EasyParallel\CpuCoreCountProvider $cpuCoreCountProvider, array $fileProcessors = [])
    {
        $this->smartFileSystem = $smartFileSystem;
        $this->fileDiffFileDecorator = $fileDiffFileDecorator;
        $this->fileFormatter = $fileFormatter;
        $this->removedAndAddedFilesProcessor = $removedAndAddedFilesProcessor;
        $this->symfonyStyle = $symfonyStyle;
        $this->fileFactory = $fileFactory;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->parametersMerger = $parametersMerger;
        $this->parallelFileProcessor = $parallelFileProcessor;
        $this->parameterProvider = $parameterProvider;
        $this->scheduleFactory = $scheduleFactory;
        $this->filePathNormalizer = $filePathNormalizer;
        $this->cpuCoreCountProvider = $cpuCoreCountProvider;
        $this->fileProcessors = $fileProcessors;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function run(\Rector\Core\ValueObject\Configuration $configuration, \RectorPrefix20220209\Symfony\Component\Console\Input\InputInterface $input) : array
    {
        $fileInfos = $this->fileFactory->createFileInfosFromPaths($configuration->getPaths(), $configuration);
        // no files found
        if ($fileInfos === []) {
            return [\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => [], \Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => []];
        }
        $this->configureCustomErrorHandler();
        if ($configuration->isParallel()) {
            $systemErrorsAndFileDiffs = $this->runParallel($fileInfos, $configuration, $input);
        } else {
            // 1. collect all files from files+dirs provided paths
            $files = $this->fileFactory->createFromPaths($configuration->getPaths(), $configuration);
            // 2. PHPStan has to know about all files too
            $this->configurePHPStanNodeScopeResolver($files);
            $systemErrorsAndFileDiffs = $this->processFiles($files, $configuration);
            $this->fileFormatter->format($files);
            $this->fileDiffFileDecorator->decorate($files);
            $this->printFiles($files, $configuration);
        }
        $systemErrorsAndFileDiffs[\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS] = \array_merge($systemErrorsAndFileDiffs[\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS], $this->systemErrors);
        $this->restoreErrorHandler();
        return $systemErrorsAndFileDiffs;
    }
    /**
     * @internal Use only for tests
     *
     * @param File[] $files
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function processFiles(array $files, \Rector\Core\ValueObject\Configuration $configuration) : array
    {
        if ($configuration->shouldShowProgressBar()) {
            $fileCount = \count($files);
            $this->symfonyStyle->progressStart($fileCount);
        }
        $systemErrorsAndFileDiffs = [\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => [], \Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => []];
        foreach ($files as $file) {
            foreach ($this->fileProcessors as $fileProcessor) {
                if (!$fileProcessor->supports($file, $configuration)) {
                    continue;
                }
                $result = $fileProcessor->process($file, $configuration);
                $systemErrorsAndFileDiffs = $this->parametersMerger->merge($systemErrorsAndFileDiffs, $result);
            }
            // progress bar +1
            if ($configuration->shouldShowProgressBar()) {
                $this->symfonyStyle->progressAdvance();
            }
        }
        $this->removedAndAddedFilesProcessor->run($configuration);
        return $systemErrorsAndFileDiffs;
    }
    /**
     * @param File[] $files
     */
    private function printFiles(array $files, \Rector\Core\ValueObject\Configuration $configuration) : void
    {
        if ($configuration->isDryRun()) {
            return;
        }
        foreach ($files as $file) {
            if (!$file->hasChanged()) {
                continue;
            }
            $this->printFile($file);
        }
    }
    private function printFile(\Rector\Core\ValueObject\Application\File $file) : void
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $this->smartFileSystem->dumpFile($smartFileInfo->getPathname(), $file->getFileContent());
        $this->smartFileSystem->chmod($smartFileInfo->getRealPath(), $smartFileInfo->getPerms());
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
            $this->systemErrors[] = new \Rector\Core\ValueObject\Error\SystemError($message, $file, $line);
            return \true;
        };
        \set_error_handler($errorHandlerCallback);
    }
    private function restoreErrorHandler() : void
    {
        \restore_error_handler();
    }
    /**
     * @param SmartFileInfo[] $fileInfos
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    private function runParallel(array $fileInfos, \Rector\Core\ValueObject\Configuration $configuration, \RectorPrefix20220209\Symfony\Component\Console\Input\InputInterface $input) : array
    {
        // must be a string, otherwise the serialization returns empty arrays
        $filePaths = $this->filePathNormalizer->resolveFilePathsFromFileInfos($fileInfos);
        $schedule = $this->scheduleFactory->create($this->cpuCoreCountProvider->provide(), $this->parameterProvider->provideIntParameter(\Rector\Core\Configuration\Option::PARALLEL_JOB_SIZE), $this->parameterProvider->provideIntParameter(\Rector\Core\Configuration\Option::PARALLEL_MAX_NUMBER_OF_PROCESSES), $filePaths);
        // for progress bar
        $isProgressBarStarted = \false;
        $postFileCallback = function (int $stepCount) use(&$isProgressBarStarted, $filePaths, $configuration) : void {
            if (!$configuration->shouldShowProgressBar()) {
                return;
            }
            if (!$isProgressBarStarted) {
                $fileCount = \count($filePaths);
                $this->symfonyStyle->progressStart($fileCount);
                $isProgressBarStarted = \true;
            }
            $this->symfonyStyle->progressAdvance($stepCount);
            // running in parallel here → nothing else to do
        };
        $mainScript = $this->resolveCalledRectorBinary();
        if ($mainScript === null) {
            throw new \RectorPrefix20220209\Symplify\EasyParallel\Exception\ParallelShouldNotHappenException('[parallel] Main script was not found');
        }
        // mimics see https://github.com/phpstan/phpstan-src/commit/9124c66dcc55a222e21b1717ba5f60771f7dda92#diff-387b8f04e0db7a06678eb52ce0c0d0aff73e0d7d8fc5df834d0a5fbec198e5daR139
        return $this->parallelFileProcessor->process($schedule, $mainScript, $postFileCallback, $input);
    }
    /**
     * Path to called "ecs" binary file, e.g. "vendor/bin/ecs" returns "vendor/bin/ecs" This is needed to re-call the
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
    /**
     * @param File[] $files
     */
    private function configurePHPStanNodeScopeResolver(array $files) : void
    {
        $filePaths = $this->resolvePhpFilePaths($files);
        $this->nodeScopeResolver->setAnalysedFiles($filePaths);
    }
    /**
     * @param File[] $files
     * @return string[]
     */
    private function resolvePhpFilePaths(array $files) : array
    {
        \RectorPrefix20220209\Webmozart\Assert\Assert::allIsAOf($files, \Rector\Core\ValueObject\Application\File::class);
        $filePaths = [];
        foreach ($files as $file) {
            $smartFileInfo = $file->getSmartFileInfo();
            $pathName = $smartFileInfo->getPathname();
            if (\substr_compare($pathName, '.php', -\strlen('.php')) === 0) {
                $filePaths[] = $pathName;
            }
        }
        return $filePaths;
    }
}
