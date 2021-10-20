<?php

declare (strict_types=1);
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
use RectorPrefix20211020\Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
final class PhpFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var File[]
     */
    private $notParsedFiles = [];
    /**
     * @var \Rector\Core\PhpParser\Printer\FormatPerservingPrinter
     */
    private $formatPerservingPrinter;
    /**
     * @var \Rector\Core\Application\FileProcessor
     */
    private $fileProcessor;
    /**
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var \Rector\Core\Application\FileDecorator\FileDiffFileDecorator
     */
    private $fileDiffFileDecorator;
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @var \Rector\PostRector\Application\PostFileProcessor
     */
    private $postFileProcessor;
    /**
     * @var \Rector\ChangesReporting\ValueObjectFactory\ErrorFactory
     */
    private $errorFactory;
    public function __construct(\Rector\Core\PhpParser\Printer\FormatPerservingPrinter $formatPerservingPrinter, \Rector\Core\Application\FileProcessor $fileProcessor, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector, \RectorPrefix20211020\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, \Rector\Core\Application\FileDecorator\FileDiffFileDecorator $fileDiffFileDecorator, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\PostRector\Application\PostFileProcessor $postFileProcessor, \Rector\ChangesReporting\ValueObjectFactory\ErrorFactory $errorFactory)
    {
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->fileProcessor = $fileProcessor;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->symfonyStyle = $symfonyStyle;
        $this->fileDiffFileDecorator = $fileDiffFileDecorator;
        $this->currentFileProvider = $currentFileProvider;
        $this->postFileProcessor = $postFileProcessor;
        $this->errorFactory = $errorFactory;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function process($file, $configuration) : void
    {
        // 1. parse files to nodes
        $this->tryCatchWrapper($file, function (\Rector\Core\ValueObject\Application\File $file) : void {
            $this->fileProcessor->parseFileInfoToLocalCache($file);
        }, \Rector\Core\Enum\ApplicationPhase::PARSING());
        // 2. change nodes with Rectors
        $loopCounter = 0;
        do {
            ++$loopCounter;
            if ($loopCounter === 10) {
                // ensure no infinite loop
                break;
            }
            $file->changeHasChanged(\false);
            $this->refactorNodesWithRectors($file);
            // 3. apply post rectors
            $this->tryCatchWrapper($file, function (\Rector\Core\ValueObject\Application\File $file) : void {
                $newStmts = $this->postFileProcessor->traverse($file->getNewStmts());
                // this is needed for new tokens added in "afterTraverse()"
                $file->changeNewStmts($newStmts);
            }, \Rector\Core\Enum\ApplicationPhase::POST_RECTORS());
            // 4. print to file or string
            $this->currentFileProvider->setFile($file);
            // cannot print file with errors, as print would break everything to original nodes
            if ($file->hasErrors()) {
                // cannot print file with errors, as print would b
                $this->notifyPhase($file, \Rector\Core\Enum\ApplicationPhase::PRINT_SKIP());
                continue;
            }
            // important to detect if file has changed
            $this->tryCatchWrapper($file, function (\Rector\Core\ValueObject\Application\File $file) use($configuration) : void {
                $this->printFile($file, $configuration);
            }, \Rector\Core\Enum\ApplicationPhase::PRINT());
        } while ($file->hasChanged());
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function supports($file, $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->hasSuffixes($configuration->getFileExtensions());
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return ['php'];
    }
    private function refactorNodesWithRectors(\Rector\Core\ValueObject\Application\File $file) : void
    {
        $this->currentFileProvider->setFile($file);
        $this->tryCatchWrapper($file, function (\Rector\Core\ValueObject\Application\File $file) : void {
            $this->fileProcessor->refactor($file);
        }, \Rector\Core\Enum\ApplicationPhase::REFACTORING());
    }
    private function tryCatchWrapper(\Rector\Core\ValueObject\Application\File $file, callable $callback, \Rector\Core\Enum\ApplicationPhase $applicationPhase) : void
    {
        $this->currentFileProvider->setFile($file);
        $this->notifyPhase($file, $applicationPhase);
        try {
            if (\in_array($file, $this->notParsedFiles, \true)) {
                // we cannot process this file
                return;
            }
            $callback($file);
        } catch (\Rector\Core\Exception\ShouldNotHappenException $shouldNotHappenException) {
            throw $shouldNotHappenException;
        } catch (\PHPStan\AnalysedCodeException $analysedCodeException) {
            // inform about missing classes in tests
            if (\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
                throw $analysedCodeException;
            }
            $this->notParsedFiles[] = $file;
            $error = $this->errorFactory->createAutoloadError($analysedCodeException, $file->getSmartFileInfo());
            $file->addRectorError($error);
        } catch (\Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose() || \Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
                throw $throwable;
            }
            $rectorError = new \Rector\Core\ValueObject\Application\RectorError($throwable->getMessage(), $file->getSmartFileInfo(), $throwable->getLine());
            $file->addRectorError($rectorError);
        }
    }
    private function printFile(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : void
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if ($this->removedAndAddedFilesCollector->isFileRemoved($smartFileInfo)) {
            // skip, because this file exists no more
            return;
        }
        $newContent = $configuration->isDryRun() ? $this->formatPerservingPrinter->printParsedStmstAndTokensToString($file) : $this->formatPerservingPrinter->printParsedStmstAndTokens($file);
        $file->changeFileContent($newContent);
        $this->fileDiffFileDecorator->decorate([$file]);
    }
    private function notifyPhase(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\Enum\ApplicationPhase $applicationPhase) : void
    {
        if (!$this->symfonyStyle->isVerbose()) {
            return;
        }
        $smartFileInfo = $file->getSmartFileInfo();
        $relativeFilePath = $smartFileInfo->getRelativeFilePathFromDirectory(\getcwd());
        $message = \sprintf('[%s] %s', $applicationPhase, $relativeFilePath);
        $this->symfonyStyle->writeln($message);
    }
}
