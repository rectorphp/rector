<?php

declare(strict_types=1);

namespace Rector\Core\Application\FileProcessor;

use PHPStan\AnalysedCodeException;
use Rector\ChangesReporting\ValueObjectFactory\ErrorFactory;
use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Application\RectorError;
use Rector\Core\ValueObject\Configuration;
use Rector\PostRector\Application\PostFileProcessor;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Throwable;

final class PhpFileProcessor implements FileProcessorInterface
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
     * @var File[]
     */
    private array $notParsedFiles = [];

    public function __construct(
        private FormatPerservingPrinter $formatPerservingPrinter,
        private FileProcessor $fileProcessor,
        private RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        private SymfonyStyle $symfonyStyle,
        private PrivatesAccessor $privatesAccessor,
        private FileDiffFileDecorator $fileDiffFileDecorator,
        private CurrentFileProvider $currentFileProvider,
        private PostFileProcessor $postFileProcessor,
        private ErrorFactory $errorFactory
    ) {
    }

    /**
     * @param File[] $files
     */
    public function process(array $files, Configuration $configuration): void
    {
        $fileCount = count($files);
        if ($fileCount === 0) {
            return;
        }

        $this->prepareProgressBar($fileCount, $configuration);

        // 1. parse files to nodes
        foreach ($files as $file) {
            $this->tryCatchWrapper($file, function (File $file): void {
                $this->fileProcessor->parseFileInfoToLocalCache($file);
            }, 'parsing', $configuration);
        }

        // 2. change nodes with Rectors
        $this->refactorNodesWithRectors($files, $configuration);

        // 3. apply post rectors
        foreach ($files as $file) {
            $this->tryCatchWrapper($file, function (File $file): void {
                $newStmts = $this->postFileProcessor->traverse($file->getNewStmts());

                // this is needed for new tokens added in "afterTraverse()"
                $file->changeNewStmts($newStmts);
            }, 'post rectors', $configuration);
        }

        // 4. print to file or string
        foreach ($files as $file) {
            $this->currentFileProvider->setFile($file);

            // cannot print file with errors, as print would break everything to original nodes
            if ($file->hasErrors()) {
                $this->printFileErrors($file);
                $this->advance($file, 'printing skipped due error', $configuration);
                continue;
            }

            $this->tryCatchWrapper($file, function (File $file) use ($configuration): void {
                $this->printFile($file, $configuration);
            }, 'printing', $configuration);
        }

        if ($configuration->shouldShowProgressBar()) {
            $this->symfonyStyle->newLine(2);
        }
    }

    public function supports(File $file, Configuration $configuration): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->hasSuffixes($configuration->getFileExtensions());
    }

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array
    {
        return ['php'];
    }

    private function prepareProgressBar(int $fileCount, Configuration $configuration): void
    {
        if ($this->symfonyStyle->isVerbose()) {
            return;
        }

        if (! $configuration->shouldShowProgressBar()) {
            return;
        }

        $this->configureStepCount($fileCount);
    }

    /**
     * @param File[] $files
     */
    private function refactorNodesWithRectors(array $files, Configuration $configuration): void
    {
        foreach ($files as $file) {
            $this->currentFileProvider->setFile($file);

            $this->tryCatchWrapper($file, function (File $file): void {
                $this->fileProcessor->refactor($file);
            }, 'refactoring', $configuration);
        }
    }

    private function tryCatchWrapper(File $file, callable $callback, string $phase, Configuration $configuration): void
    {
        $this->currentFileProvider->setFile($file);

        $this->advance($file, $phase, $configuration);

        try {
            if (in_array($file, $this->notParsedFiles, true)) {
                // we cannot process this file
                return;
            }

            $callback($file);
        } catch (ShouldNotHappenException $shouldNotHappenException) {
            throw $shouldNotHappenException;
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

    private function printFile(File $file, Configuration $configuration): void
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if ($this->removedAndAddedFilesCollector->isFileRemoved($smartFileInfo)) {
            // skip, because this file exists no more
            return;
        }

        $newContent = $configuration->isDryRun()
            ? $this->formatPerservingPrinter->printParsedStmstAndTokensToString($file)
            : $this->formatPerservingPrinter->printParsedStmstAndTokens($file);

        $file->changeFileContent($newContent);
        $this->fileDiffFileDecorator->decorate([$file]);
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

    private function advance(File $file, string $phase, Configuration $configuration): void
    {
        if ($this->symfonyStyle->isVerbose()) {
            $smartFileInfo = $file->getSmartFileInfo();
            $relativeFilePath = $smartFileInfo->getRelativeFilePathFromDirectory(getcwd());
            $message = sprintf('[%s] %s', $phase, $relativeFilePath);
            $this->symfonyStyle->writeln($message);
        } elseif ($configuration->shouldShowProgressBar()) {
            $this->symfonyStyle->progressAdvance();
        }
    }

    private function printFileErrors(File $file): void
    {
        foreach ($file->getErrors() as $rectorError) {
            $this->symfonyStyle->error($rectorError->getMessage());
        }
    }
}
