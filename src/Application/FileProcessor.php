<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use PhpParser\Lexer;
use PhpParser\Node;
use Rector\ChangesReporting\Collector\AffectedFilesCollector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Core\Stubs\StubLoader;
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
        FormatPerservingPrinter $formatPerservingPrinter,
        Parser $parser,
        Lexer $lexer,
        RectorNodeTraverser $rectorNodeTraverser,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        CurrentFileInfoProvider $currentFileInfoProvider,
        StubLoader $stubLoader,
        AffectedFilesCollector $affectedFilesCollector,
        PostFileProcessor $postFileProcessor,
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

        [$newStmts, $oldStmts, $oldTokens] = $this->parseAndTraverseFileInfoToNodes($smartFileInfo);
        if ($newStmts === null) {
            throw new ShouldNotHappenException(sprintf(
                'Parsing of file "%s" went wrong. Might be caused by inlinced html. Does it have full "<?php" openings? Try re-run with --debug option to find out more.',
                $smartFileInfo->getRealPath()
            ));
        }

        // store tokens by absolute path, so we don't have to print them right now
        $this->tokensByFilePathStorage->addForRealPath($smartFileInfo, $newStmts, $oldStmts, $oldTokens);
    }

    public function printToFile(SmartFileInfo $smartFileInfo): string
    {
        [$newStmts, $oldStmts, $oldTokens] = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);
        return $this->formatPerservingPrinter->printToFile($smartFileInfo, $newStmts, $oldStmts, $oldTokens);
    }

    /**
     * See https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516.
     */
    public function printToString(SmartFileInfo $smartFileInfo): string
    {
        $this->makeSureFileIsParsed($smartFileInfo);

        [$newStmts, $oldStmts, $oldTokens] = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);
        return $this->formatPerservingPrinter->printToString($newStmts, $oldStmts, $oldTokens);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $this->stubLoader->loadStubs();
        $this->currentFileInfoProvider->setCurrentFileInfo($smartFileInfo);

        $this->makeSureFileIsParsed($smartFileInfo);

        [$newStmts, $oldStmts, $oldTokens] = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);

        $this->currentFileInfoProvider->setCurrentStmt($newStmts);

        $newStmts = $this->rectorNodeTraverser->traverse($newStmts);

        // this is needed for new tokens added in "afterTraverse()"
        $this->tokensByFilePathStorage->addForRealPath($smartFileInfo, $newStmts, $oldStmts, $oldTokens);

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

        [$newStmts, $oldStmts, $oldTokens] = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);

        $this->currentFileInfoProvider->setCurrentStmt($newStmts);

        $newStmts = $this->postFileProcessor->traverse($newStmts);

        // this is needed for new tokens added in "afterTraverse()"
        $this->tokensByFilePathStorage->addForRealPath($smartFileInfo, $newStmts, $oldStmts, $oldTokens);
    }

    /**
     * @return Node[][]|mixed[]
     */
    private function parseAndTraverseFileInfoToNodes(SmartFileInfo $smartFileInfo): array
    {
        $oldStmts = $this->parser->parseFileInfo($smartFileInfo);
        $oldTokens = $this->lexer->getTokens();

        // needed for \Rector\NodeTypeResolver\PHPStan\Scope\NodeScopeResolver
        $this->tokensByFilePathStorage->addForRealPath($smartFileInfo, $oldStmts, $oldStmts, $oldTokens);

        $newStmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($oldStmts, $smartFileInfo);

        return [$newStmts, $oldStmts, $oldTokens];
    }

    private function makeSureFileIsParsed(SmartFileInfo $smartFileInfo): void
    {
        if ($this->tokensByFilePathStorage->hasForFileInfo($smartFileInfo)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'File %s was not preparsed, so it cannot be printed.%sCheck "%s" method.',
            $smartFileInfo->getRealPath(),
            PHP_EOL,
            self::class . '::parseFileInfoToLocalCache()'
        ));
    }
}
