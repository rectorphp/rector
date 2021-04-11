<?php

declare(strict_types=1);

namespace Rector\Core\Application\FileProcessor;

use PhpParser\Lexer;
use PHPStan\AnalysedCodeException;
use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\ChangesReporting\Collector\AffectedFilesCollector;
use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Application\TokensByFilePathStorage;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\CustomNode\FileNode;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Application\ParsedStmtsAndTokens;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\PostRector\Application\PostFileProcessor;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\SmartFileSystem\SmartFileInfo;
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
     * @var Parser
     */
    private $parser;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    /**
     * @var NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @var AffectedFilesCollector
     */
    private $affectedFilesCollector;

    /**
     * @var PostFileProcessor
     */
    private $postFileProcessor;

    /**
     * @var TokensByFilePathStorage
     */
    private $tokensByFilePathStorage;

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
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

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
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    /**
     * @var FileDiffFileDecorator
     */
    private $fileDiffFileDecorator;

    public function __construct(
        Configuration $configuration,
        ErrorAndDiffCollector $errorAndDiffCollector,
        FileProcessor $fileProcessor,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        RemovedAndAddedFilesProcessor $removedAndAddedFilesProcessor,
        SymfonyStyle $symfonyStyle,
        PrivatesAccessor $privatesAccessor,
        FileDiffFileDecorator $fileDiffFileDecorator,
        AffectedFilesCollector $affectedFilesCollector,
        CurrentFileInfoProvider $currentFileInfoProvider,
        Lexer $lexer,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        Parser $parser,
        PostFileProcessor $postFileProcessor,
        RectorNodeTraverser $rectorNodeTraverser,
        TokensByFilePathStorage $tokensByFilePathStorage
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->configuration = $configuration;
        $this->fileProcessor = $fileProcessor;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->removedAndAddedFilesProcessor = $removedAndAddedFilesProcessor;
        $this->privatesAccessor = $privatesAccessor;
        $this->fileDiffFileDecorator = $fileDiffFileDecorator;
        $this->configuration = $configuration;
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
        $this->affectedFilesCollector = $affectedFilesCollector;
        $this->postFileProcessor = $postFileProcessor;
        $this->tokensByFilePathStorage = $tokensByFilePathStorage;
    }

    /**
     * @param File[] $files
     */
    public function process(array $files): void
    {
        $fileCount = count($files);
        if ($fileCount === 0) {
            return;
        }

        $this->prepareProgressBar($fileCount);

        // 1. parse files to nodes
        $this->parseFileInfosToNodes($files);

        // 2. change nodes with Rectors
        $this->refactorNodesWithRectors($files);

        // 3. apply post rectors
        foreach ($files as $file) {
            $this->tryCatchWrapper($file, function (File $file): void {
                $this->fileProcessor->postFileRefactor($file);
            }, 'post rectors');
        }

        // 4. print to file or string
        foreach ($files as $file) {
            // cannot print file with errors, as print would break everything to original nodes
            if ($this->errorAndDiffCollector->hasSmartFileErrors($file)) {
                $this->advance($file, 'printing skipped due error');
                continue;
            }

            $this->tryCatchWrapper($file, function (File $file): void {
                $this->printFileInfo($file);
            }, 'printing');
        }

        if ($this->configuration->shouldShowProgressBar()) {
            $this->symfonyStyle->newLine(2);
        }

        // 4. remove and add files
        $this->removedAndAddedFilesProcessor->run();
    }

    public function supports(File $file): bool
    {
        $fileInfo = $file->getSmartFileInfo();
        return $fileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array
    {
        return $this->configuration->getFileExtensions();
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
     * @param File[] $files
     */
    private function parseFileInfosToNodes(array $files): void
    {
        foreach ($files as $file) {
            $this->tryCatchWrapper($file, function (File $file): void {
                $this->fileProcessor->parseFileInfoToLocalCache($file);
            }, 'parsing');
        }
    }

    /**
     * @param File[] $files
     */
    private function refactorNodesWithRectors(array $files): void
    {
        foreach ($files as $file) {
            $this->tryCatchWrapper($file, function (File $file): void {
                $this->fileProcessor->refactor($file);
            }, 'refactoring');
        }
    }

    private function tryCatchWrapper(File $file, callable $callback, string $phase): void
    {
        $this->advance($file, $phase);

        try {
            if (in_array($file, $this->notParsedFiles, true)) {
                // we cannot process this file
                return;
            }

            $callback($file);
        } catch (AnalysedCodeException $analysedCodeException) {
            $this->notParsedFiles[] = $file;
            $this->errorAndDiffCollector->addAutoloadError($analysedCodeException, $file);
        } catch (Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose()) {
                throw $throwable;
            }

            $fileInfo = $file->getSmartFileInfo();
            $this->errorAndDiffCollector->addThrowableWithFileInfo($throwable, $fileInfo);
        }
    }

    private function printFileInfo(File $file): void
    {
        $fileInfo = $file->getSmartFileInfo();

        if ($this->removedAndAddedFilesCollector->isFileRemoved($fileInfo)) {
            // skip, because this file exists no more
            return;
        }

        $newContent = $this->configuration->isDryRun() ? $this->fileProcessor->printToString($fileInfo)
            : $this->fileProcessor->printToFile($fileInfo);

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

    private function advance(File $file, string $phase): void
    {
        if ($this->symfonyStyle->isVerbose()) {
            $fileInfo = $file->getSmartFileInfo();
            $relativeFilePath = $fileInfo->getRelativeFilePathFromDirectory(getcwd());
            $message = sprintf('[%s] %s', $phase, $relativeFilePath);
            $this->symfonyStyle->writeln($message);
        } elseif ($this->configuration->shouldShowProgressBar()) {
            $this->symfonyStyle->progressAdvance();
        }
    }

    private function refactor(File $file): void
    {
        $this->parseFileInfoToLocalCache($file->getSmartFileInfo());
        $parsedStmtsAndTokens = $this->tokensByFilePathStorage->getForFileInfo($file->getSmartFileInfo());

        $this->currentFileInfoProvider->setCurrentStmts($parsedStmtsAndTokens->getNewStmts());

        // run file node only if
        $fileNode = new FileNode($file->getSmartFileInfo(), $parsedStmtsAndTokens->getNewStmts());
        $this->rectorNodeTraverser->traverseFileNode($fileNode);

        $newStmts = $this->rectorNodeTraverser->traverse($parsedStmtsAndTokens->getNewStmts());

        // this is needed for new tokens added in "afterTraverse()"
        $parsedStmtsAndTokens->updateNewStmts($newStmts);

        $this->affectedFilesCollector->removeFromList($file);
        while ($otherTouchedFile = $this->affectedFilesCollector->getNext()) {
            $this->refactor($otherTouchedFile);
        }
    }

    private function parseFileInfoToLocalCache(SmartFileInfo $fileInfo): void
    {
        if ($this->tokensByFilePathStorage->hasForFileInfo($fileInfo)) {
            return;
        }

        $this->currentFileInfoProvider->setCurrentFileInfo($fileInfo);

        // store tokens by absolute path, so we don't have to print them right now
        $parsedStmtsAndTokens = $this->parseAndTraverseFileInfoToNodes($fileInfo);
        $this->tokensByFilePathStorage->addForRealPath($fileInfo, $parsedStmtsAndTokens);
    }

    private function postFileRefactor(File $file): void
    {
        $smartFileInfo = $file->getSmartFileInfo();

        if (! $this->tokensByFilePathStorage->hasForFileInfo($smartFileInfo)) {
            $this->parseFileInfoToLocalCache($smartFileInfo);
        }

        $parsedStmtsAndTokens = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);

        $this->currentFileInfoProvider->setCurrentStmts($parsedStmtsAndTokens->getNewStmts());
        $this->currentFileInfoProvider->setCurrentFileInfo($smartFileInfo);

        $newStmts = $this->postFileProcessor->traverse($parsedStmtsAndTokens->getNewStmts());

        // this is needed for new tokens added in "afterTraverse()"
        $parsedStmtsAndTokens->updateNewStmts($newStmts);
    }

    private function parseAndTraverseFileInfoToNodes(SmartFileInfo $smartFileInfo): ParsedStmtsAndTokens
    {
        $oldStmts = $this->parser->parseFileInfo($smartFileInfo);
        $oldTokens = $this->lexer->getTokens();

        // needed for \Rector\NodeTypeResolver\PHPStan\Scope\NodeScopeResolver
        $parsedStmtsAndTokens = new ParsedStmtsAndTokens($oldStmts, $oldStmts, $oldTokens);
        $this->tokensByFilePathStorage->addForRealPath($smartFileInfo, $parsedStmtsAndTokens);

        $newStmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($oldStmts, $smartFileInfo);

        return new ParsedStmtsAndTokens($newStmts, $oldStmts, $oldTokens);
    }
}
