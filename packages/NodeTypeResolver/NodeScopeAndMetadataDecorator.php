<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;

final class NodeScopeAndMetadataDecorator
{
    public function __construct(
        private CloningVisitor $cloningVisitor,
        private NamespaceNodeVisitor $namespaceNodeVisitor,
        private PHPStanNodeScopeResolver $phpStanNodeScopeResolver,
        private StatementNodeVisitor $statementNodeVisitor,
        private NodeConnectingVisitor $nodeConnectingVisitor,
        private FunctionLikeParamArgPositionNodeVisitor $functionLikeParamArgPositionNodeVisitor
    ) {
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function decorateNodesFromFile(File $file, array $stmts): array
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $stmts = $this->phpStanNodeScopeResolver->processNodes($stmts, $smartFileInfo);

        $nodeTraverserForFormatPreservePrinting = new NodeTraverser();
        // needed also for format preserving printing
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->cloningVisitor);

        // this one has to be run again to re-connect nodes with new attributes
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->nodeConnectingVisitor);

        $nodeTraverserForFormatPreservePrinting->addVisitor($this->namespaceNodeVisitor);
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->functionLikeParamArgPositionNodeVisitor);

        $stmts = $nodeTraverserForFormatPreservePrinting->traverse($stmts);

        // this split is needed, so nodes have names, classes and namespaces
        $nodeTraverserForStmtNodeVisitor = new NodeTraverser();
        $nodeTraverserForStmtNodeVisitor->addVisitor($this->statementNodeVisitor);

        return $nodeTraverserForStmtNodeVisitor->traverse($stmts);
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function decorateStmtsFromString(array $stmts): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->nodeConnectingVisitor);
        $nodeTraverser->addVisitor($this->statementNodeVisitor);

        return $nodeTraverser->traverse($stmts);
    }
}
