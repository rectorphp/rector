<?php declare(strict_types=1);

namespace Rector\Application;

use PHPStan\AnalysedCodeException;
use Rector\Configuration\Configuration;
use Rector\Error\ExceptionCorrector;
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
     * @var ExceptionCorrector
     */
    private $exceptionCorrector;

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
     * @var FilesToReprintCollector
     */
    private $filesToReprintCollector;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        FileSystemFileProcessor $fileSystemFileProcessor,
        ExceptionCorrector $exceptionCorrector,
        ErrorAndDiffCollector $errorAndDiffCollector,
        Configuration $configuration,
        FileProcessor $fileProcessor,
        FilesToReprintCollector $filesToReprintCollector
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->fileSystemFileProcessor = $fileSystemFileProcessor;
        $this->exceptionCorrector = $exceptionCorrector;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->configuration = $configuration;
        $this->fileProcessor = $fileProcessor;
        $this->filesToReprintCollector = $filesToReprintCollector;
    }

    /**
     * @param SmartFileInfo[] $fileInfos
     */
    public function runOnFileInfos(array $fileInfos): void
    {
        $totalFiles = count($fileInfos);
        if (! $this->symfonyStyle->isVerbose()) {
            $this->symfonyStyle->progressStart($totalFiles);
        }

        foreach ($fileInfos as $fileInfo) {
            $this->processFileInfo($fileInfo);
            if ($this->symfonyStyle->isVerbose()) {
                $this->symfonyStyle->writeln($fileInfo->getRealPath());
            } else {
                $this->symfonyStyle->progressAdvance();
            }
        }

        $this->symfonyStyle->newLine(2);
    }

    private function processFileInfo(SmartFileInfo $fileInfo): void
    {
        try {
            $this->processFile($fileInfo);
            $this->fileSystemFileProcessor->processFileInfo($fileInfo);
        } catch (AnalysedCodeException $analysedCodeException) {
            if ($this->configuration->shouldHideAutoloadErrors()) {
                return;
            }

            $this->errorAndDiffCollector->addAutoloadError($analysedCodeException, $fileInfo);
        } catch (Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose()) {
                throw $throwable;
            }

            $rectorClass = $this->exceptionCorrector->matchRectorClass($throwable);
            if ($rectorClass) {
                $this->errorAndDiffCollector->addErrorWithRectorMessage($rectorClass, $throwable->getMessage());
            } else {
                $this->errorAndDiffCollector->addError(
                    new Error($fileInfo, $throwable->getMessage(), $throwable->getCode())
                );
            }
        }
    }

    private function processFile(SmartFileInfo $fileInfo): void
    {
        $oldContent = $fileInfo->getContents();

        if ($this->configuration->isDryRun()) {
            $newContent = $this->fileProcessor->processFileToString($fileInfo);

            /** @var string $rectorClass */
            foreach ($this->filesToReprintCollector->getFileInfosAndRectorClasses() as $rectorClassToFileInfo) {
                [$rectorClass, $fileInfoToReprint] = $rectorClassToFileInfo;

                $reprintedOldContent = $fileInfoToReprint->getContents();
                $reprintedNewContent = $this->fileProcessor->reprintToString($fileInfoToReprint);
                $this->errorAndDiffCollector->addFileDiff(
                    $fileInfoToReprint,
                    $reprintedNewContent,
                    $reprintedOldContent,
                    $rectorClass
                );
            }
        } else {
            $newContent = $this->fileProcessor->processFile($fileInfo);

            /** @var string $rectorClass */
            foreach ($this->filesToReprintCollector->getFileInfosAndRectorClasses() as $rectorClassToFileInfo) {
                [$rectorClass, $fileInfoToReprint] = $rectorClassToFileInfo;

                $reprintedOldContent = $fileInfoToReprint->getContents();
                $reprintedNewContent = $this->fileProcessor->reprintFile($fileInfoToReprint);
                $this->errorAndDiffCollector->addFileDiff(
                    $fileInfoToReprint,
                    $reprintedNewContent,
                    $reprintedOldContent,
                    $rectorClass
                );
            }
        }

        $this->errorAndDiffCollector->addFileDiff($fileInfo, $newContent, $oldContent);
        $this->filesToReprintCollector->reset();
    }
}
