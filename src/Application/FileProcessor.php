<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use PhpParser\Lexer;
use Rector\ChangesReporting\Collector\AffectedFilesCollector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\CustomNode\FileNode;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Core\Stubs\StubLoader;
use Rector\Core\ValueObject\Application\ParsedStmtsAndTokens;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\PostRector\Application\PostFileProcessor;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileProcessor
{
    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;

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
     * @var StubLoader
     */
    private $stubLoader;

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

    public function __construct(
        AffectedFilesCollector $affectedFilesCollector,
        CurrentFileInfoProvider $currentFileInfoProvider,
        FormatPerservingPrinter $formatPerservingPrinter,
        Lexer $lexer,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        Parser $parser,
        PostFileProcessor $postFileProcessor,
        RectorNodeTraverser $rectorNodeTraverser,
        StubLoader $stubLoader,
        TokensByFilePathStorage $tokensByFilePathStorage
    ) {
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
        $this->stubLoader = $stubLoader;
        $this->affectedFilesCollector = $affectedFilesCollector;
        $this->postFileProcessor = $postFileProcessor;
        $this->tokensByFilePathStorage = $tokensByFilePathStorage;
    }

    public function parseFileInfoToLocalCache(SmartFileInfo $smartFileInfo): void
    {
        if ($this->tokensByFilePathStorage->hasForFileInfo($smartFileInfo)) {
            return;
        }

        $this->currentFileInfoProvider->setCurrentFileInfo($smartFileInfo);

        // store tokens by absolute path, so we don't have to print them right now
        $parsedStmtsAndTokens = $this->parseAndTraverseFileInfoToNodes($smartFileInfo);
        $this->tokensByFilePathStorage->addForRealPath($smartFileInfo, $parsedStmtsAndTokens);
    }

    public function printToFile(SmartFileInfo $smartFileInfo): string
    {
        $parsedStmtsAndTokens = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);
        return $this->formatPerservingPrinter->printParsedStmstAndTokens($smartFileInfo, $parsedStmtsAndTokens);
    }

    /**
     * See https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516.
     */
    public function printToString(SmartFileInfo $smartFileInfo): string
    {
        $this->makeSureFileIsParsed($smartFileInfo);

        $parsedStmtsAndTokens = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);

        return $this->formatPerservingPrinter->printParsedStmstAndTokensToString($parsedStmtsAndTokens);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $this->stubLoader->loadStubs();
        $this->currentFileInfoProvider->setCurrentFileInfo($smartFileInfo);

        $this->makeSureFileIsParsed($smartFileInfo);

        $parsedStmtsAndTokens = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);

        $this->currentFileInfoProvider->setCurrentStmts($parsedStmtsAndTokens->getNewStmts());

        // run file node only if
        $fileNode = new FileNode($smartFileInfo, $parsedStmtsAndTokens->getNewStmts());
        $result = $this->rectorNodeTraverser->traverseFileNode($fileNode);

        $newStmts = $this->rectorNodeTraverser->traverse($parsedStmtsAndTokens->getNewStmts());

        // this is needed for new tokens added in "afterTraverse()"
        $parsedStmtsAndTokens->updateNewStmts($newStmts);

        $this->affectedFilesCollector->removeFromList($smartFileInfo);
        while ($otherTouchedFile = $this->affectedFilesCollector->getNext()) {
            $this->refactor($otherTouchedFile);
        }
    }

    public function postFileRefactor(SmartFileInfo $smartFileInfo): void
    {
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

    private function makeSureFileIsParsed(SmartFileInfo $smartFileInfo): void
    {
        if ($this->tokensByFilePathStorage->hasForFileInfo($smartFileInfo)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'File "%s" was not preparsed, so it cannot be printed.%sCheck "%s" method.',
            $smartFileInfo->getRealPath(),
            PHP_EOL,
            self::class . '::parseFileInfoToLocalCache()'
        ));
    }
}
