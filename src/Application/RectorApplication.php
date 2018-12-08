<?php declare(strict_types=1);

namespace Rector\Application;

use PHPStan\AnalysedCodeException;
use Rector\Configuration\Configuration;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\Error\ExceptionCorrector;
use Rector\FileSystemRector\FileSystemFileProcessor;
use Rector\Reporting\FileDiff;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Throwable;

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
     * @var ErrorCollector
     */
    private $errorCollector;

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

    /**
     * @var FileDiff[]
     */
    private $fileDiffs = [];

    /**
     * @var DifferAndFormatter
     */
    private $differAndFormatter;

    /**
     * @var AppliedRectorCollector
     */
    private $appliedRectorCollector;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        FileSystemFileProcessor $fileSystemFileProcessor,
        ExceptionCorrector $exceptionCorrector,
        ErrorCollector $errorCollector,
        Configuration $configuration,
        FileProcessor $fileProcessor,
        FilesToReprintCollector $filesToReprintCollector,
        DifferAndFormatter $differAndFormatter,
        AppliedRectorCollector $appliedRectorCollector
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->fileSystemFileProcessor = $fileSystemFileProcessor;
        $this->exceptionCorrector = $exceptionCorrector;
        $this->errorCollector = $errorCollector;
        $this->configuration = $configuration;
        $this->fileProcessor = $fileProcessor;
        $this->filesToReprintCollector = $filesToReprintCollector;
        $this->differAndFormatter = $differAndFormatter;
        $this->appliedRectorCollector = $appliedRectorCollector;
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

            $message = $this->exceptionCorrector->getAutoloadExceptionMessageAndAddLocation($analysedCodeException);

            $this->errorCollector->addError(new Error($fileInfo, $message));
        } catch (Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose()) {
                throw $throwable;
            }

            $rectorClass = $this->exceptionCorrector->matchRectorClass($throwable);
            if ($rectorClass) {
                $this->errorCollector->addErrorWithRectorMessage($rectorClass, $throwable->getMessage());
            } else {
                $this->errorCollector->addError(new Error($fileInfo, $throwable->getMessage(), $throwable->getCode()));
            }
        }
    }

    private function processFile(SmartFileInfo $fileInfo): void
    {
        $oldContent = $fileInfo->getContents();

        if ($this->configuration->isDryRun()) {
            $newContent = $this->fileProcessor->processFileToString($fileInfo);

            foreach ($this->filesToReprintCollector->getFileInfos() as $fileInfoToReprint) {
                $reprintedOldContent = $fileInfoToReprint->getContents();
                $reprintedNewContent = $this->fileProcessor->reprintToString($fileInfoToReprint);
                $this->recordFileDiff($fileInfoToReprint, $reprintedNewContent, $reprintedOldContent);
            }
        } else {
            $newContent = $this->fileProcessor->processFile($fileInfo);

            foreach ($this->filesToReprintCollector->getFileInfos() as $fileInfoToReprint) {
                $reprintedOldContent = $fileInfoToReprint->getContents();
                $reprintedNewContent = $this->fileProcessor->reprintFile($fileInfoToReprint);
                $this->recordFileDiff($fileInfoToReprint, $reprintedNewContent, $reprintedOldContent);
            }
        }

        $this->recordFileDiff($fileInfo, $newContent, $oldContent);
        $this->filesToReprintCollector->reset();
    }

    /**
     * @todo move to error collector
     */
    private function recordFileDiff(SmartFileInfo $fileInfo, string $newContent, string $oldContent): void
    {
        if ($newContent === $oldContent) {
            return;
        }

        // always keep the most recent diff
        $this->fileDiffs[$fileInfo->getRealPath()] = new FileDiff(
            $fileInfo->getRealPath(),
            $this->differAndFormatter->diffAndFormat($oldContent, $newContent),
            $this->appliedRectorCollector->getRectorClasses()
        );
    }
}
