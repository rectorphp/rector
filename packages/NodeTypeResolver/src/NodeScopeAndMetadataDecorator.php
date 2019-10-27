<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use Rector\Configuration\Configuration;
use Rector\NodeTypeResolver\NodeVisitor\FileInfoNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\NodeCollectorNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\ParentAndNextNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeScopeResolver;

final class NodeScopeAndMetadataDecorator
{
    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    /**
     * @var CloningVisitor
     */
    private $cloningVisitor;

    /**
     * @var ParentAndNextNodeVisitor
     */
    private $parentAndNextNodeVisitor;

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

    public function __construct(
        NodeScopeResolver $nodeScopeResolver,
        ParentAndNextNodeVisitor $parentAndNextNodeVisitor,
        CloningVisitor $cloningVisitor,
        FunctionMethodAndClassNodeVisitor $functionMethodAndClassNodeVisitor,
        NamespaceNodeVisitor $namespaceNodeVisitor,
        StatementNodeVisitor $statementNodeVisitor,
        FileInfoNodeVisitor $fileInfoNodeVisitor,
        NodeCollectorNodeVisitor $nodeCollectorNodeVisitor,
        Configuration $configuration
    ) {
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->parentAndNextNodeVisitor = $parentAndNextNodeVisitor;
        $this->cloningVisitor = $cloningVisitor;
        $this->functionMethodAndClassNodeVisitor = $functionMethodAndClassNodeVisitor;
        $this->namespaceNodeVisitor = $namespaceNodeVisitor;
        $this->statementNodeVisitor = $statementNodeVisitor;
        $this->fileInfoNodeVisitor = $fileInfoNodeVisitor;
        $this->nodeCollectorNodeVisitor = $nodeCollectorNodeVisitor;
        $this->configuration = $configuration;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function decorateNodesFromFile(array $nodes, string $filePath, bool $needsScope = false): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver(null, [
            'preserveOriginalNames' => true,
            'replaceNodes' => true, // required by PHPStan
        ]));
        $nodes = $nodeTraverser->traverse($nodes);

        // node scoping is needed only for Scope
        if ($needsScope || $this->configuration->areAnyPhpRectorsLoaded()) {
            $nodes = $this->nodeScopeResolver->processNodes($nodes, $filePath);
        }

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver(null, [
            'preserveOriginalNames' => true,
            // this option would override old non-fqn-namespaced nodes otherwise, so it needs to be disabled
            'replaceNodes' => false,
        ]));
        $nodes = $nodeTraverser->traverse($nodes);

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->cloningVisitor); // needed also for format preserving printing
        $nodeTraverser->addVisitor($this->parentAndNextNodeVisitor);
        $nodeTraverser->addVisitor($this->functionMethodAndClassNodeVisitor);
        $nodeTraverser->addVisitor($this->namespaceNodeVisitor);

        $nodes = $nodeTraverser->traverse($nodes);

        // this split is needed, so nodes have names, classes and namespaces
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->statementNodeVisitor);
        $nodeTraverser->addVisitor($this->fileInfoNodeVisitor);
        $nodeTraverser->addVisitor($this->nodeCollectorNodeVisitor);

        return $nodeTraverser->traverse($nodes);
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function decorateNodesFromString(array $nodes): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->parentAndNextNodeVisitor);
        $nodeTraverser->addVisitor($this->functionMethodAndClassNodeVisitor);
        $nodeTraverser->addVisitor($this->statementNodeVisitor);

        return $nodeTraverser->traverse($nodes);
    }
}
