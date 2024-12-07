<?php

declare (strict_types=1);
namespace Rector\Application;

use RectorPrefix202412\Nette\Utils\FileSystem;
use PHPStan\AnalysedCodeException;
use PHPStan\Parser\ParserErrorsException;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\ChangesReporting\ValueObjectFactory\ErrorFactory;
use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Exception\ShouldNotHappenException;
use Rector\FileSystem\FilePathHelper;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\PhpParser\Parser\ParserErrors;
use Rector\PhpParser\Parser\RectorParser;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\PostRector\Application\PostFileProcessor;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Rector\ValueObject\Application\File;
use Rector\ValueObject\Configuration;
use Rector\ValueObject\Error\SystemError;
use Rector\ValueObject\FileProcessResult;
use RectorPrefix202412\Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
final class FileProcessor
{
    /**
     * @readonly
     */
    private BetterStandardPrinter $betterStandardPrinter;
    /**
     * @readonly
     */
    private RectorNodeTraverser $rectorNodeTraverser;
    /**
     * @readonly
     */
    private SymfonyStyle $symfonyStyle;
    /**
     * @readonly
     */
    private FileDiffFactory $fileDiffFactory;
    /**
     * @readonly
     */
    private ChangedFilesDetector $changedFilesDetector;
    /**
     * @readonly
     */
    private ErrorFactory $errorFactory;
    /**
     * @readonly
     */
    private FilePathHelper $filePathHelper;
    /**
     * @readonly
     */
    private PostFileProcessor $postFileProcessor;
    /**
     * @readonly
     */
    private RectorParser $rectorParser;
    /**
     * @readonly
     */
    private NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator;
    public function __construct(BetterStandardPrinter $betterStandardPrinter, RectorNodeTraverser $rectorNodeTraverser, SymfonyStyle $symfonyStyle, FileDiffFactory $fileDiffFactory, ChangedFilesDetector $changedFilesDetector, ErrorFactory $errorFactory, FilePathHelper $filePathHelper, PostFileProcessor $postFileProcessor, RectorParser $rectorParser, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->symfonyStyle = $symfonyStyle;
        $this->fileDiffFactory = $fileDiffFactory;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->errorFactory = $errorFactory;
        $this->filePathHelper = $filePathHelper;
        $this->postFileProcessor = $postFileProcessor;
        $this->rectorParser = $rectorParser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
    }
    public function processFile(File $file, Configuration $configuration) : FileProcessResult
    {
        // 1. parse files to nodes
        $parsingSystemError = $this->parseFileAndDecorateNodes($file);
        if ($parsingSystemError instanceof SystemError) {
            // we cannot process this file as the parsing and type resolving itself went wrong
            return new FileProcessResult([$parsingSystemError], null);
        }
        $fileHasChanged = \false;
        $filePath = $file->getFilePath();
        // 2. change nodes with Rectors
        $rectorWithLineChanges = null;
        do {
            $file->changeHasChanged(\false);
            $newStmts = $this->rectorNodeTraverser->traverse($file->getNewStmts());
            // apply post rectors
            $postNewStmts = $this->postFileProcessor->traverse($newStmts, $file);
            // this is needed for new tokens added in "afterTraverse()"
            $file->changeNewStmts($postNewStmts);
            // 3. print to file or string
            // important to detect if file has changed
            $this->printFile($file, $configuration, $filePath);
            $fileHasChangedInCurrentPass = $file->hasChanged();
            if ($fileHasChangedInCurrentPass) {
                $file->setFileDiff($this->fileDiffFactory->createTempFileDiff($file));
                $rectorWithLineChanges = $file->getRectorWithLineChanges();
                $fileHasChanged = \true;
            }
        } while ($fileHasChangedInCurrentPass);
        // 5. add as cacheable if not changed at all
        if (!$fileHasChanged) {
            $this->changedFilesDetector->addCachableFile($filePath);
        }
        if ($configuration->shouldShowDiffs() && $rectorWithLineChanges !== null) {
            $currentFileDiff = $this->fileDiffFactory->createFileDiffWithLineChanges($file, $file->getOriginalFileContent(), $file->getFileContent(), $rectorWithLineChanges);
            $file->setFileDiff($currentFileDiff);
        }
        return new FileProcessResult([], $file->getFileDiff());
    }
    private function parseFileAndDecorateNodes(File $file) : ?SystemError
    {
        try {
            try {
                $this->parseFileNodes($file);
            } catch (ParserErrorsException $exception) {
                $this->parseFileNodes($file, \false);
            }
        } catch (ShouldNotHappenException $shouldNotHappenException) {
            throw $shouldNotHappenException;
        } catch (AnalysedCodeException $analysedCodeException) {
            // inform about missing classes in tests
            if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
                throw $analysedCodeException;
            }
            return $this->errorFactory->createAutoloadError($analysedCodeException, $file->getFilePath());
        } catch (Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose() || StaticPHPUnitEnvironment::isPHPUnitRun()) {
                throw $throwable;
            }
            $relativeFilePath = $this->filePathHelper->relativePath($file->getFilePath());
            if ($throwable instanceof ParserErrorsException) {
                $throwable = new ParserErrors($throwable);
            }
            return new SystemError($throwable->getMessage(), $relativeFilePath, $throwable->getLine());
        }
        return null;
    }
    private function printFile(File $file, Configuration $configuration, string $filePath) : void
    {
        // only save to string first, no need to print to file when not needed
        $newContent = $this->betterStandardPrinter->printFormatPreserving($file->getNewStmts(), $file->getOldStmts(), $file->getOldTokens());
        // change file content early to make $file->hasChanged() based on new content
        $file->changeFileContent($newContent);
        if ($configuration->isDryRun()) {
            return;
        }
        if (!$file->hasChanged()) {
            return;
        }
        FileSystem::write($filePath, $newContent, null);
    }
    private function parseFileNodes(File $file, bool $forNewestSupportedVersion = \true) : void
    {
        // store tokens by original file content, so we don't have to print them right now
        $stmtsAndTokens = $this->rectorParser->parseFileContentToStmtsAndTokens($file->getOriginalFileContent(), $forNewestSupportedVersion);
        $oldStmts = $stmtsAndTokens->getStmts();
        $oldTokens = $stmtsAndTokens->getTokens();
        $newStmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file->getFilePath(), $oldStmts);
        $file->hydrateStmtsAndTokens($newStmts, $oldStmts, $oldTokens);
    }
}
