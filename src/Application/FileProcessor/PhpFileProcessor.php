<?php

declare(strict_types=1);

namespace Rector\Core\Application\FileProcessor;

use PHPStan\AnalysedCodeException;
use Rector\ChangesReporting\ValueObjectFactory\ErrorFactory;
use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Enum\ApplicationPhase;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Application\RectorError;
use Rector\Core\ValueObject\Configuration;
use Rector\PostRector\Application\PostFileProcessor;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class PhpFileProcessor implements FileProcessorInterface
{
    /**
     * @var File[]
     */
    private array $notParsedFiles = [];

    public function __construct(
        private FormatPerservingPrinter $formatPerservingPrinter,
        private FileProcessor $fileProcessor,
        private RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        private SymfonyStyle $symfonyStyle,
        private FileDiffFileDecorator $fileDiffFileDecorator,
        private CurrentFileProvider $currentFileProvider,
        private PostFileProcessor $postFileProcessor,
        private ErrorFactory $errorFactory
    ) {
    }

    public function process(File $file, Configuration $configuration): void
    {
        // 1. parse files to nodes
        $this->tryCatchWrapper($file, function (File $file): void {
            $this->fileProcessor->parseFileInfoToLocalCache($file);
        }, ApplicationPhase::PARSING());

        // 2. change nodes with Rectors
        $this->refactorNodesWithRectors($file);

        // 3. apply post rectors
        $this->tryCatchWrapper($file, function (File $file): void {
            $newStmts = $this->postFileProcessor->traverse($file->getNewStmts());

            // this is needed for new tokens added in "afterTraverse()"
            $file->changeNewStmts($newStmts);
        }, ApplicationPhase::POST_RECTORS());

        // 4. print to file or string
        $this->currentFileProvider->setFile($file);

        if ($file->hasErrors()) {
            // cannot print file with errors, as print would b
            $this->notifyPhase($file, ApplicationPhase::PRINT_SKIP());
            return;
        }

        $this->tryCatchWrapper($file, function (File $file) use ($configuration): void {
            $this->printFile($file, $configuration);
        }, ApplicationPhase::PRINT());
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

    private function refactorNodesWithRectors(File $file): void
    {
        $this->currentFileProvider->setFile($file);

        $this->tryCatchWrapper($file, function (File $file): void {
            $this->fileProcessor->refactor($file);
        }, ApplicationPhase::REFACTORING());
    }

    private function tryCatchWrapper(File $file, callable $callback, ApplicationPhase $applicationPhase): void
    {
        $this->currentFileProvider->setFile($file);
        $this->notifyPhase($file, $applicationPhase);

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

    private function notifyPhase(File $file, ApplicationPhase $applicationPhase): void
    {
        if (! $this->symfonyStyle->isVerbose()) {
            return;
        }

        $smartFileInfo = $file->getSmartFileInfo();
        $relativeFilePath = $smartFileInfo->getRelativeFilePathFromDirectory(getcwd());
        $message = sprintf('[%s] %s', $applicationPhase, $relativeFilePath);
        $this->symfonyStyle->writeln($message);
    }
}
