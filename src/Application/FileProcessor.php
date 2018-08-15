<?php declare(strict_types=1);

namespace Rector\Application;

use PhpParser\Lexer;
use PhpParser\Node;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\Parser\Parser;
use Rector\Printer\FormatPerservingPrinter;
use Symfony\Component\Finder\SplFileInfo;

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

    public function __construct(
        FormatPerservingPrinter $formatPerservingPrinter,
        Parser $parser,
        Lexer $lexer,
        RectorNodeTraverser $rectorNodeTraverser,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator
    ) {
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
    }

    public function processFile(SplFileInfo $fileInfo): string
    {
        [$newStmts, $oldStmts, $oldTokens] = $this->parseAndTraverseFileInfoToNodes($fileInfo);

        return $this->formatPerservingPrinter->printToFile($fileInfo, $newStmts, $oldStmts, $oldTokens);
    }

    /**
     * See https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516.
     */
    public function processFileToString(SplFileInfo $fileInfo): string
    {
        [$newStmts, $oldStmts, $oldTokens] = $this->parseAndTraverseFileInfoToNodes($fileInfo);

        return $this->formatPerservingPrinter->printToString($newStmts, $oldStmts, $oldTokens);
    }

    /**
     * @return Node[][]|mixed[]
     */
    private function parseAndTraverseFileInfoToNodes(SplFileInfo $fileInfo): array
    {
        $oldStmts = $this->parser->parseFile($fileInfo->getRealPath());
        $oldTokens = $this->lexer->getTokens();

        $newStmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($oldStmts, $fileInfo->getRealPath());
        $newStmts = $this->rectorNodeTraverser->traverse($newStmts);

        return [$newStmts, $oldStmts, $oldTokens];
    }
}
