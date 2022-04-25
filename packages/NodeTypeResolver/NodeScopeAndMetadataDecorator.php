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
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;

final class NodeScopeAndMetadataDecorator
{
    public function __construct(
        private readonly CloningVisitor $cloningVisitor,
        private readonly NamespaceNodeVisitor $namespaceNodeVisitor,
        private readonly PHPStanNodeScopeResolver $phpStanNodeScopeResolver,
        private readonly NodeConnectingVisitor $nodeConnectingVisitor,
        private readonly FunctionLikeParamArgPositionNodeVisitor $functionLikeParamArgPositionNodeVisitor
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

        $nodeTraverser = new NodeTraverser();
        // needed also for format preserving printing
        $nodeTraverser->addVisitor($this->cloningVisitor);

        // this one has to be run again to re-connect nodes with new attributes
        $nodeTraverser->addVisitor($this->nodeConnectingVisitor);

        $nodeTraverser->addVisitor($this->namespaceNodeVisitor);
        $nodeTraverser->addVisitor($this->functionLikeParamArgPositionNodeVisitor);

        return $nodeTraverser->traverse($stmts);
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function decorateStmtsFromString(array $stmts): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->nodeConnectingVisitor);

        return $nodeTraverser->traverse($stmts);
    }
}
