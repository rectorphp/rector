<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeVisitor\FileNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;

final class NodeScopeAndMetadataDecorator
{
    public function __construct(
        private CloningVisitor $cloningVisitor,
        private FunctionMethodAndClassNodeVisitor $functionMethodAndClassNodeVisitor,
        private NamespaceNodeVisitor $namespaceNodeVisitor,
        private PHPStanNodeScopeResolver $phpStanNodeScopeResolver,
        private StatementNodeVisitor $statementNodeVisitor,
        private NodeConnectingVisitor $nodeConnectingVisitor,
        private FunctionLikeParamArgPositionNodeVisitor $functionLikeParamArgPositionNodeVisitor
    ) {
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function decorateNodesFromFile(File $file, array $nodes): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver(null, [
            'preserveOriginalNames' => true,
            // required by PHPStan
            'replaceNodes' => true,
        ]));

        /** @var Stmt[] $nodes */
        $nodes = $nodeTraverser->traverse($nodes);

        $smartFileInfo = $file->getSmartFileInfo();
        $nodes = $this->phpStanNodeScopeResolver->processNodes($nodes, $smartFileInfo);

        $nodeTraverser = new NodeTraverser();

        $preservingNameResolver = new NameResolver(null, [
            'preserveOriginalNames' => true,
            // this option would override old non-fqn-namespaced nodes otherwise, so it needs to be disabled
            'replaceNodes' => false,
        ]);

        $nodeTraverser->addVisitor($preservingNameResolver);
        $nodes = $nodeTraverser->traverse($nodes);

        $nodeTraverser = new NodeTraverser();
        // needed also for format preserving printing
        $nodeTraverser->addVisitor($this->cloningVisitor);
        $nodeTraverser->addVisitor($this->nodeConnectingVisitor);
        $nodeTraverser->addVisitor($this->functionMethodAndClassNodeVisitor);
        $nodeTraverser->addVisitor($this->namespaceNodeVisitor);
        $nodeTraverser->addVisitor($this->functionLikeParamArgPositionNodeVisitor);

        $fileNodeVisitor = new FileNodeVisitor($file);
        $nodeTraverser->addVisitor($fileNodeVisitor);

        $nodes = $nodeTraverser->traverse($nodes);

        // this split is needed, so nodes have names, classes and namespaces
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->statementNodeVisitor);

        return $nodeTraverser->traverse($nodes);
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function decorateNodesFromString(array $nodes): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->nodeConnectingVisitor);
        $nodeTraverser->addVisitor($this->functionMethodAndClassNodeVisitor);
        $nodeTraverser->addVisitor($this->statementNodeVisitor);

        return $nodeTraverser->traverse($nodes);
    }
}
