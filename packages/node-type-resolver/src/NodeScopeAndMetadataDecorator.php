<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use Rector\Core\Configuration\Configuration;
use Rector\NodeCollector\NodeVisitor\NodeCollectorNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FileInfoNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FirstLevelNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NodeScopeAndMetadataDecorator
{
    /**
     * @var PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;

    /**
     * @var CloningVisitor
     */
    private $cloningVisitor;

    /**
     * @var FunctionMethodAndClassNodeVisitor
     */
    private $functionMethodAndClassNodeVisitor;

    /**
     * @var NamespaceNodeVisitor
     */
    private $namespaceNodeVisitor;

    /**
     * @var StatementNodeVisitor
     */
    private $statementNodeVisitor;

    /**
     * @var FileInfoNodeVisitor
     */
    private $fileInfoNodeVisitor;

    /**
     * @var NodeCollectorNodeVisitor
     */
    private $nodeCollectorNodeVisitor;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var NodeConnectingVisitor
     */
    private $nodeConnectingVisitor;

    /**
     * @var FunctionLikeParamArgPositionNodeVisitor
     */
    private $functionLikeParamArgPositionNodeVisitor;

    /**
     * @var FirstLevelNodeVisitor
     */
    private $firstLevelNodeVisitor;

    public function __construct(
        CloningVisitor $cloningVisitor,
        Configuration $configuration,
        FileInfoNodeVisitor $fileInfoNodeVisitor,
        FunctionMethodAndClassNodeVisitor $functionMethodAndClassNodeVisitor,
        NamespaceNodeVisitor $namespaceNodeVisitor,
        NodeCollectorNodeVisitor $nodeCollectorNodeVisitor,
        PHPStanNodeScopeResolver $phpStanNodeScopeResolver,
        StatementNodeVisitor $statementNodeVisitor,
        NodeConnectingVisitor $nodeConnectingVisitor,
        FunctionLikeParamArgPositionNodeVisitor $functionLikeParamArgPositionNodeVisitor,
        FirstLevelNodeVisitor $firstLevelNodeVisitor
    ) {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->cloningVisitor = $cloningVisitor;
        $this->functionMethodAndClassNodeVisitor = $functionMethodAndClassNodeVisitor;
        $this->namespaceNodeVisitor = $namespaceNodeVisitor;
        $this->statementNodeVisitor = $statementNodeVisitor;
        $this->fileInfoNodeVisitor = $fileInfoNodeVisitor;
        $this->nodeCollectorNodeVisitor = $nodeCollectorNodeVisitor;
        $this->configuration = $configuration;
        $this->nodeConnectingVisitor = $nodeConnectingVisitor;
        $this->functionLikeParamArgPositionNodeVisitor = $functionLikeParamArgPositionNodeVisitor;
        $this->firstLevelNodeVisitor = $firstLevelNodeVisitor;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function decorateNodesFromFile(array $nodes, SmartFileInfo $smartFileInfo, bool $needsScope = false): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver(null, [
            'preserveOriginalNames' => true,
            // required by PHPStan
            'replaceNodes' => true,
        ]));
        $nodes = $nodeTraverser->traverse($nodes);

        // node scoping is needed only for Scope
        if ($needsScope || $this->configuration->areAnyPhpRectorsLoaded()) {
            $nodes = $this->phpStanNodeScopeResolver->processNodes($nodes, $smartFileInfo);
        }

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
        $nodeTraverser->addVisitor($this->firstLevelNodeVisitor);
        $nodeTraverser->addVisitor($this->functionLikeParamArgPositionNodeVisitor);

        $nodes = $nodeTraverser->traverse($nodes);

        // this split is needed, so nodes have names, classes and namespaces
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->statementNodeVisitor);
        $nodeTraverser->addVisitor($this->fileInfoNodeVisitor);
        $nodeTraverser->addVisitor($this->nodeCollectorNodeVisitor);

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
