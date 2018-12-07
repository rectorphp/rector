<?php declare(strict_types=1);

namespace Rector\Application;

use PhpParser\Lexer;
use PhpParser\Node;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\PhpParser\Parser\Parser;
use Rector\PhpParser\Printer\FormatPerservingPrinter;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

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
     * @var mixed[][]
     */
    private $tokensByFilePath = [];

    public function __construct(
        FormatPerservingPrinter $formatPerservingPrinter,
        Parser $parser,
        Lexer $lexer,
        RectorNodeTraverser $rectorNodeTraverser,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        CurrentFileInfoProvider $currentFileInfoProvider
    ) {
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
    }

    public function processFile(SmartFileInfo $smartFileInfo): string
    {
        $this->currentFileInfoProvider->setCurrentFileInfo($smartFileInfo);

        [$newStmts, $oldStmts, $oldTokens] = $this->parseAndTraverseFileInfoToNodes($smartFileInfo);

        // store tokens by absolute path, so we don't have to print them right now
        $this->tokensByFilePath[$smartFileInfo->getRealPath()] = [$newStmts, $oldStmts, $oldTokens];

        return $this->formatPerservingPrinter->printToFile($smartFileInfo, $newStmts, $oldStmts, $oldTokens);
    }

    public function reprintFile(SmartFileInfo $smartFileInfo): string
    {
        // restore tokens
        [$newStmts, $oldStmts, $oldTokens] = $this->tokensByFilePath[$smartFileInfo->getRealPath()];

        return $this->formatPerservingPrinter->printToFile($smartFileInfo, $newStmts, $oldStmts, $oldTokens);
    }

    /**
     * See https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516.
     */
    public function processFileToString(SmartFileInfo $smartFileInfo): string
    {
        $this->currentFileInfoProvider->setCurrentFileInfo($smartFileInfo);

        [$newStmts, $oldStmts, $oldTokens] = $this->parseAndTraverseFileInfoToNodes($smartFileInfo);

        // store tokens by absolute path, so we don't have to print them right now
        $this->tokensByFilePath[$smartFileInfo->getRealPath()] = [$newStmts, $oldStmts, $oldTokens];

        return $this->formatPerservingPrinter->printToString($newStmts, $oldStmts, $oldTokens);
    }

    /**
     * See https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516.
     */
    public function reprintToString(SmartFileInfo $smartFileInfo): string
    {
        // restore tokens
        [$newStmts, $oldStmts, $oldTokens] = $this->tokensByFilePath[$smartFileInfo->getRealPath()];

        return $this->formatPerservingPrinter->printToString($newStmts, $oldStmts, $oldTokens);
    }

    /**
     * @return Node[][]|mixed[]
     */
    private function parseAndTraverseFileInfoToNodes(SmartFileInfo $smartFileInfo): array
    {
        $oldStmts = $this->parser->parseFile($smartFileInfo->getRealPath());
        $oldTokens = $this->lexer->getTokens();

        $newStmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile(
            $oldStmts,
            $smartFileInfo->getRealPath()
        );
        $newStmts = $this->rectorNodeTraverser->traverse($newStmts);

        return [$newStmts, $oldStmts, $oldTokens];
    }
}
