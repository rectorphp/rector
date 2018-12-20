<?php declare(strict_types=1);

namespace Rector\Application;

use Nette\Utils\FileSystem;
use PHPStan\AnalysedCodeException;
use Rector\Configuration\Configuration;
use Rector\FileSystemRector\FileSystemFileProcessor;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
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
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var FileSystemFileProcessor
     */
    private $fileSystemFileProcessor;

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
     * @var RemovedFilesCollector
     */
    private $removedFilesCollector;

    /**
     * @var SmartFileInfo[]
     */
    private $notParsedFiles = [];

    public function __construct(
        SymfonyStyle $symfonyStyle,
        FileSystemFileProcessor $fileSystemFileProcessor,
        ErrorAndDiffCollector $errorAndDiffCollector,
        Configuration $configuration,
        FileProcessor $fileProcessor,
        RemovedFilesCollector $removedFilesCollector
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->fileSystemFileProcessor = $fileSystemFileProcessor;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->configuration = $configuration;
        $this->fileProcessor = $fileProcessor;
        $this->removedFilesCollector = $removedFilesCollector;
    }

    /**
     * @param SmartFileInfo[] $fileInfos
     */
    public function runOnFileInfos(array $fileInfos): void
    {
        $totalFiles = count($fileInfos);

        if (! $this->symfonyStyle->isVerbose()) {
            // why 3? one for each cycle, so user sees some activity all the time
            $this->symfonyStyle->progressStart($totalFiles * 3);
        }

        // 1. parse files to nodes
        foreach ($fileInfos as $fileInfo) {
            $this->tryCatchWrapper($fileInfo, function (SmartFileInfo $smartFileInfo): void {
                $this->fileProcessor->parseFileInfoToLocalCache($smartFileInfo);
            });
        }

        // 2. change nodes with Rectors
        foreach ($fileInfos as $fileInfo) {
            $this->tryCatchWrapper($fileInfo, function (SmartFileInfo $smartFileInfo): void {
                $this->fileProcessor->refactor($smartFileInfo);
            });
        }

        // 3. print to file or string
        foreach ($fileInfos as $fileInfo) {
            $this->tryCatchWrapper($fileInfo, function (SmartFileInfo $smartFileInfo): void {
                $this->processFileInfo($smartFileInfo);
            });
        }

        $this->symfonyStyle->newLine(2);
    }

    private function advance(SmartFileInfo $smartFileInfo): void
    {
        if ($this->symfonyStyle->isVerbose()) {
            $this->symfonyStyle->writeln($smartFileInfo->getRealPath());
        } else {
            $this->symfonyStyle->progressAdvance();
        }
    }

    private function tryCatchWrapper(SmartFileInfo $smartFileInfo, callable $callback): void
    {
        $this->advance($smartFileInfo);

        try {
            if (in_array($smartFileInfo, $this->notParsedFiles, true)) {
                // we cannot process this file
                return;
            }

            $callback($smartFileInfo);
        } catch (AnalysedCodeException $analysedCodeException) {
            if ($this->configuration->shouldHideAutoloadErrors()) {
                return;
            }

            $this->notParsedFiles[] = $smartFileInfo;

            $this->errorAndDiffCollector->addAutoloadError($analysedCodeException, $smartFileInfo);
        } catch (Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose()) {
                throw $throwable;
            }

            $this->errorAndDiffCollector->addThrowableWithFileInfo($throwable, $smartFileInfo);
        }
    }

    private function processFileInfo(SmartFileInfo $fileInfo): void
    {
        if ($this->removedFilesCollector->hasFile($fileInfo)) {
            if (! $this->configuration->isDryRun()) {
                FileSystem::delete($fileInfo->getRealPath());
            }
        } else {
            $oldContent = $fileInfo->getContents();

            if ($this->configuration->isDryRun()) {
                $newContent = $this->fileProcessor->printToString($fileInfo);
            } else {
                $newContent = $this->fileProcessor->printToFile($fileInfo);
            }

            $this->errorAndDiffCollector->addFileDiff($fileInfo, $newContent, $oldContent);

            $this->fileSystemFileProcessor->processFileInfo($fileInfo);
        }
    }
}
