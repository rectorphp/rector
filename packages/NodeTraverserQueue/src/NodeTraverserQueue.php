<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\NodeTypeResolver\NodeVisitor\MetadataNodeVisitor;
use Rector\Parser\Parser;
use Rector\PhpParser\NodeVisitor\ParentAndNextNodeAddingNodeVisitor;
use Symfony\Component\Finder\SplFileInfo;

final class NodeTraverserQueue
{
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
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var CloningVisitor
     */
    private $cloningVisitor;

    /**
     * @var ParentAndNextNodeAddingNodeVisitor
     */
    private $parentAndNextNodeAddingNodeVisitor;

    /**
     * @var MetadataNodeVisitor
     */
    private $metadataNodeVisitor;

    public function __construct(
        Parser $parser,
        Lexer $lexer,
        RectorNodeTraverser $rectorNodeTraverser,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        NameResolver $nameResolver,
        CloningVisitor $cloningVisitor,
        ParentAndNextNodeAddingNodeVisitor $parentAndNextNodeAddingNodeVisitor,
        MetadataNodeVisitor $metadataNodeVisitor
    ) {
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->nameResolver = $nameResolver;
        $this->cloningVisitor = $cloningVisitor;
        $this->parentAndNextNodeAddingNodeVisitor = $parentAndNextNodeAddingNodeVisitor;
        $this->metadataNodeVisitor = $metadataNodeVisitor;
    }

    # adds current class and method to all nodes via attribute

    /**
     * @return mixed[]
     */
    public function processFileInfo(SplFileInfo $fileInfo): array
    {
        $oldStmts = $this->parser->parseFile($fileInfo->getRealPath());
        $oldTokens = $this->lexer->getTokens();

        $newStmts = $this->nodeScopeAndMetadataDecorator->processNodesAndSplFileInfo($oldStmts, $fileInfo);

        $nodeTraverser = (new NodeTraverser());
        $nodeTraverser->addVisitor($this->nameResolver);
        $newStmts = $nodeTraverser->traverse($newStmts);

        $nodeTraverser = (new NodeTraverser());
        $nodeTraverser->addVisitor($this->cloningVisitor);
        $newStmts = $nodeTraverser->traverse($newStmts);

        $nodeTraverser = (new NodeTraverser());
        $nodeTraverser->addVisitor($this->parentAndNextNodeAddingNodeVisitor);
        $newStmts = $nodeTraverser->traverse($newStmts);

        $nodeTraverser = (new NodeTraverser());
        $nodeTraverser->addVisitor($this->metadataNodeVisitor);
        $newStmts = $nodeTraverser->traverse($newStmts);

        $newStmts = $this->rectorNodeTraverser->traverse($newStmts);

        return [$newStmts, $oldStmts, $oldTokens];
    }
}
