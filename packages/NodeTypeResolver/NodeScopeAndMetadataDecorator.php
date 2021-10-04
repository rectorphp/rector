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
    /**
     * @var string
     */
    private const OPTION_PRESERVE_ORIGINAL_NAMES = 'preserveOriginalNames';

    /**
     * @var string
     */
    private const OPTION_REPLACE_NODES = 'replaceNodes';

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
            self::OPTION_PRESERVE_ORIGINAL_NAMES => true,
            // required by PHPStan
            self::OPTION_REPLACE_NODES => true,
        ]));

        /** @var Stmt[] $nodes */
        $nodes = $nodeTraverser->traverse($nodes);

        $smartFileInfo = $file->getSmartFileInfo();

        $nodes = $this->phpStanNodeScopeResolver->processNodes($nodes, $smartFileInfo);

        $nodeTraverserForPreservingName = new NodeTraverser();

        $preservingNameResolver = new NameResolver(null, [
            self::OPTION_PRESERVE_ORIGINAL_NAMES => true,
            // this option would override old non-fqn-namespaced nodes otherwise, so it needs to be disabled
            self::OPTION_REPLACE_NODES => false,
        ]);

        $nodeTraverserForPreservingName->addVisitor($preservingNameResolver);
        $nodes = $nodeTraverserForPreservingName->traverse($nodes);

        $nodeTraverserForFormatPreservePrinting = new NodeTraverser();
        // needed also for format preserving printing
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->cloningVisitor);
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->nodeConnectingVisitor);
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->functionMethodAndClassNodeVisitor);
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->namespaceNodeVisitor);
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->functionLikeParamArgPositionNodeVisitor);

        $fileNodeVisitor = new FileNodeVisitor($file);
        $nodeTraverserForFormatPreservePrinting->addVisitor($fileNodeVisitor);

        $nodes = $nodeTraverserForFormatPreservePrinting->traverse($nodes);

        // this split is needed, so nodes have names, classes and namespaces
        $nodeTraverserForStmtNodeVisitor = new NodeTraverser();
        $nodeTraverserForStmtNodeVisitor->addVisitor($this->statementNodeVisitor);

        return $nodeTraverserForStmtNodeVisitor->traverse($nodes);
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
