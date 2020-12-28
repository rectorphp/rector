<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\NodeScopeResolver;
use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\PostRunnerInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\SmartFileSystem\SmartFileInfo;
use Throwable;

/**
 * Rector cycle has 3 steps:
 *
 * 1. parse all files to nodes
 *
 * 2. run Rectors on all files and their nodes
 *
 * 3. print changed content to file or to string diff with "--dry-run"
 */
final class RectorApplication
{
    /**
     * Why 4? One for each cycle, so user sees some activity all the time:
     *
     * 1) parsing files
     * 2) main rectoring
     * 3) post-rectoring (removing files, importing names)
     * 4) printing
     *
     * @var int
     */
    private const PROGRESS_BAR_STEP_MULTIPLIER = 4;

    /**
     * @var SmartFileInfo[]
     */
    private $notParsedFiles = [];

    /**
     * @var PostRunnerInterface[]
     */
    private $postRunners = [];

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    /**
     * @var RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;

    /**
     * @var RemovedAndAddedFilesProcessor
     */
    private $removedAndAddedFilesProcessor;

    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    /**
     * @param PostRunnerInterface[] $postRunners
     */
    public function __construct(
        Configuration $configuration,
        ErrorAndDiffCollector $errorAndDiffCollector,
        FileProcessor $fileProcessor,
        NodeScopeResolver $nodeScopeResolver,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        RemovedAndAddedFilesProcessor $removedAndAddedFilesProcessor,
        SymfonyStyle $symfonyStyle,
        PrivatesAccessor $privatesAccessor,
        array $postRunners
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->configuration = $configuration;
        $this->fileProcessor = $fileProcessor;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->removedAndAddedFilesProcessor = $removedAndAddedFilesProcessor;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->privatesAccessor = $privatesAccessor;
        $this->postRunners = $postRunners;
    }

    /**
     * @param SmartFileInfo[] $phpFileInfos
     */
    public function runOnFileInfos(array $phpFileInfos): void
    {
        $fileCount = count($phpFileInfos);
        if ($fileCount === 0) {
            return;
        }

        $this->prepareProgressBar($fileCount);

        // PHPStan has to know about all files!
        $this->configurePHPStanNodeScopeResolver($phpFileInfos);

        // 1. parse files to nodes
        $this->parseFileInfosToNodes($phpFileInfos);

        // 2. change nodes with Rectors
        $this->refactorNodesWithRectors($phpFileInfos);

        // 3. apply post rectors
        foreach ($phpFileInfos as $phpFileInfo) {
            $this->tryCatchWrapper($phpFileInfo, function (SmartFileInfo $smartFileInfo): void {
                $this->fileProcessor->postFileRefactor($smartFileInfo);
            }, 'post rectors');
        }

        // 4. print to file or string
        foreach ($phpFileInfos as $phpFileInfo) {
            $this->tryCatchWrapper($phpFileInfo, function (SmartFileInfo $smartFileInfo): void {
                $this->printFileInfo($smartFileInfo);
            }, 'printing');
        }

        if ($this->configuration->shouldShowProgressBar()) {
            $this->symfonyStyle->newLine(2);
        }

        // 4. remove and add files
        $this->removedAndAddedFilesProcessor->run();

        // 5. various extensions on finish
        foreach ($this->postRunners as $postRunner) {
            $postRunner->run();
        }
    }

    private function prepareProgressBar(int $fileCount): void
    {
        if ($this->symfonyStyle->isVerbose()) {
            return;
        }

        if (! $this->configuration->shouldShowProgressBar()) {
            return;
        }

        $this->configureStepCount($fileCount);
    }

    /**
     * @param SmartFileInfo[] $fileInfos
     */
    private function configurePHPStanNodeScopeResolver(array $fileInfos): void
    {
        $filePaths = [];
        foreach ($fileInfos as $fileInfo) {
            $filePaths[] = $fileInfo->getPathname();
        }

        $this->nodeScopeResolver->setAnalysedFiles($filePaths);
    }

    /**
     * @param SmartFileInfo[] $phpFileInfos
     */
    private function parseFileInfosToNodes(array $phpFileInfos): void
    {
        foreach ($phpFileInfos as $phpFileInfo) {
            $this->tryCatchWrapper($phpFileInfo, function (SmartFileInfo $smartFileInfo): void {
                $this->fileProcessor->parseFileInfoToLocalCache($smartFileInfo);
            }, 'parsing');
        }
    }

    /**
     * @param SmartFileInfo[] $phpFileInfos
     */
    private function refactorNodesWithRectors(array $phpFileInfos): void
    {
        foreach ($phpFileInfos as $phpFileInfo) {
            $this->tryCatchWrapper($phpFileInfo, function (SmartFileInfo $smartFileInfo): void {
                $this->fileProcessor->refactor($smartFileInfo);
            }, 'refactoring');
        }
    }

    private function tryCatchWrapper(SmartFileInfo $smartFileInfo, callable $callback, string $phase): void
    {
        $this->advance($smartFileInfo, $phase);

        try {
            if (in_array($smartFileInfo, $this->notParsedFiles, true)) {
                // we cannot process this file
                return;
            }

            $callback($smartFileInfo);
        } catch (AnalysedCodeException $analysedCodeException) {
            $this->notParsedFiles[] = $smartFileInfo;

            $this->errorAndDiffCollector->addAutoloadError($analysedCodeException, $smartFileInfo);
        } catch (Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose()) {
                throw $throwable;
            }

            $this->errorAndDiffCollector->addThrowableWithFileInfo($throwable, $smartFileInfo);
        }
    }

    private function printFileInfo(SmartFileInfo $fileInfo): void
    {
        if ($this->removedAndAddedFilesCollector->isFileRemoved($fileInfo)) {
            // skip, because this file exists no more
            return;
        }

        $oldContents = $fileInfo->getContents();

        $newContent = $this->configuration->isDryRun() ? $this->fileProcessor->printToString($fileInfo)
            : $this->fileProcessor->printToFile($fileInfo);

        $this->errorAndDiffCollector->addFileDiff($fileInfo, $newContent, $oldContents);
    }

    /**
     * This prevent CI report flood with 1 file = 1 line in progress bar
     */
    private function configureStepCount(int $fileCount): void
    {
        $this->symfonyStyle->progressStart($fileCount * self::PROGRESS_BAR_STEP_MULTIPLIER);

        $progressBar = $this->privatesAccessor->getPrivateProperty($this->symfonyStyle, 'progressBar');
        if (! $progressBar instanceof ProgressBar) {
            throw new ShouldNotHappenException();
        }

        if ($progressBar->getMaxSteps() < 40) {
            return;
        }

        $redrawFrequency = (int) ($progressBar->getMaxSteps() / 20);
        $progressBar->setRedrawFrequency($redrawFrequency);
    }

    private function advance(SmartFileInfo $smartFileInfo, string $phase): void
    {
        if ($this->symfonyStyle->isVerbose()) {
            $relativeFilePath = $smartFileInfo->getRelativeFilePathFromDirectory(getcwd());
            $message = sprintf('[%s] %s', $phase, $relativeFilePath);
            $this->symfonyStyle->writeln($message);
        } elseif ($this->configuration->shouldShowProgressBar()) {
            $this->symfonyStyle->progressAdvance();
        }
    }
}
