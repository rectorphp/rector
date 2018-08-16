<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use Rector\NodeTypeResolver\NodeVisitor\ClassAndMethodNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\ParentAndNextNodeVisitor;
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
     * @var ClassAndMethodNodeVisitor
     */
    private $classAndMethodNodeVisitor;

    /**
     * @var NamespaceNodeVisitor
     */
    private $namespaceNodeVisitor;

    public function __construct(
        NodeScopeResolver $nodeScopeResolver,
        ParentAndNextNodeVisitor $parentAndNextNodeVisitor,
        CloningVisitor $cloningVisitor,
        ClassAndMethodNodeVisitor $classAndMethodNodeVisitor,
        NamespaceNodeVisitor $namespaceNodeVisitor
    ) {
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->parentAndNextNodeVisitor = $parentAndNextNodeVisitor;
        $this->cloningVisitor = $cloningVisitor;
        $this->classAndMethodNodeVisitor = $classAndMethodNodeVisitor;
        $this->namespaceNodeVisitor = $namespaceNodeVisitor;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function decorateNodesFromFile(array $nodes, string $filePath): array
    {
        $nodeTraverser = new NodeTraverser();
        // specially rewrite nodes for PHPStan
        $nodeTraverser->addVisitor(new NameResolver());
        $nodes = $nodeTraverser->traverse($nodes);

        $nodes = $this->nodeScopeResolver->processNodes($nodes, $filePath);

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver(null, [
            // this option would override old non-fqn-namespaced nodes otherwise, so it needs to be disabled
            'replaceNodes' => false,
        ]));
        $nodes = $nodeTraverser->traverse($nodes);

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->cloningVisitor); // needed also for format preserving printing
        $nodeTraverser->addVisitor($this->parentAndNextNodeVisitor);
        $nodeTraverser->addVisitor($this->classAndMethodNodeVisitor);
        $nodeTraverser->addVisitor($this->namespaceNodeVisitor);

        return $nodeTraverser->traverse($nodes);
    }
}
