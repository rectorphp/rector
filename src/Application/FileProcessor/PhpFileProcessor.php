<?php

declare(strict_types=1);

namespace Rector\Core\Application\FileProcessor;

use PHPStan\AnalysedCodeException;
use Rector\ChangesReporting\ValueObjectFactory\ErrorFactory;
use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Application\ApplicationProgressBarInterface;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Application\RectorError;
use Rector\PostRector\Application\PostFileProcessor;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class PhpFileProcessor implements FileProcessorInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var File[]
     */
    private $notParsedFiles = [];

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

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
     * @var FileDiffFileDecorator
     */
    private $fileDiffFileDecorator;

    /**
     * @var CurrentFileProvider
     */
    private $currentFileProvider;

    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;

    /**
     * @var PostFileProcessor
     */
    private $postFileProcessor;

    /**
     * @var ErrorFactory
     */
    private $errorFactory;

    public function __construct(
        Configuration $configuration,
        FormatPerservingPrinter $formatPerservingPrinter,
        FileProcessor $fileProcessor,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        RemovedAndAddedFilesProcessor $removedAndAddedFilesProcessor,
        SymfonyStyle $symfonyStyle,
        FileDiffFileDecorator $fileDiffFileDecorator,
        CurrentFileProvider $currentFileProvider,
        PostFileProcessor $postFileProcessor,
        ErrorFactory $errorFactory
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->configuration = $configuration;
        $this->fileProcessor = $fileProcessor;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->removedAndAddedFilesProcessor = $removedAndAddedFilesProcessor;
        $this->fileDiffFileDecorator = $fileDiffFileDecorator;
        $this->configuration = $configuration;
        $this->currentFileProvider = $currentFileProvider;
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->postFileProcessor = $postFileProcessor;
        $this->errorFactory = $errorFactory;
    }

    /**
     * @param File[] $files
     */
    public function process(array $files, ApplicationProgressBarInterface $applicationProgressBar): void
    {
        $fileCount = count($files);
        if ($fileCount === 0) {
            return;
        }

        // 1. parse files to nodes
        foreach ($files as $file) {
            $applicationProgressBar->advance($file, 'parsing');

            $this->tryCatchWrapper($file, function (File $file): void {
                $this->fileProcessor->parseFileInfoToLocalCache($file);
            });
        }

        // 2. change nodes with Rectors
        $this->refactorNodesWithRectors($files, $applicationProgressBar);

        // 3. apply post rectors
        foreach ($files as $file) {
            $applicationProgressBar->advance($file, 'post rectors');

            $this->tryCatchWrapper($file, function (File $file): void {
                $newStmts = $this->postFileProcessor->traverse($file->getNewStmts());

                // this is needed for new tokens added in "afterTraverse()"
                $file->changeNewStmts($newStmts);
            });
        }

        // 4. print to file or string
        foreach ($files as $file) {
            $this->currentFileProvider->setFile($file);

            // cannot print file with errors, as print would break everything to original nodes
            if ($file->hasErrors()) {
                $applicationProgressBar->advance($file, 'printing skipped due error');
                continue;
            }

            $applicationProgressBar->advance($file, 'printing');
            $this->tryCatchWrapper($file, function (File $file): void {
                $this->printFile($file);
            });
        }

        if ($this->configuration->shouldShowProgressBar()) {
            $this->symfonyStyle->newLine(2);
        }

        // 4. remove and add files
        $this->removedAndAddedFilesProcessor->run();
    }

    public function supports(File $file): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array
    {
        return $this->configuration->getFileExtensions();
    }

    /**
     * @param File[] $files
     */
    private function refactorNodesWithRectors(
        array $files,
        ApplicationProgressBarInterface $applicationProgressBar
    ): void {
        foreach ($files as $file) {
            $this->currentFileProvider->setFile($file);

            $applicationProgressBar->advance($file, 'refactoring');

            $this->tryCatchWrapper($file, function (File $file): void {
                $this->fileProcessor->refactor($file);
            });
        }
    }

    private function tryCatchWrapper(File $file, callable $callback): void
    {
        $this->currentFileProvider->setFile($file);

        try {
            if (in_array($file, $this->notParsedFiles, true)) {
                // we cannot process this file
                return;
            }

            $callback($file);
        } catch (AnalysedCodeException $analysedCodeException) {
            // inform about missing classes in tests
            if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
                throw $analysedCodeException;
            }

            $this->notParsedFiles[] = $file;
            $error = $this->errorFactory->createAutoloadError($analysedCodeException);
            $file->addRectorError($error);
        } catch (Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose() || StaticPHPUnitEnvironment::isPHPUnitRun()) {
                throw $throwable;
            }

            $rectorError = new RectorError($throwable->getMessage(), $throwable->getLine());
            $file->addRectorError($rectorError);
        }
    }

    private function printFile(File $file): void
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if ($this->removedAndAddedFilesCollector->isFileRemoved($smartFileInfo)) {
            // skip, because this file exists no more
            return;
        }

        $newContent = $this->configuration->isDryRun() ? $this->formatPerservingPrinter->printParsedStmstAndTokensToString(
            $file
        ) : $this->formatPerservingPrinter->printParsedStmstAndTokens($file);

        $file->changeFileContent($newContent);
        $this->fileDiffFileDecorator->decorate([$file]);
    }
}
