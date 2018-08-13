<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use Rector\NodeTypeResolver\NodeVisitor\MetadataNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\ParentAndNextNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeScopeResolver;

final class NodeScopeAndMetadataDecorator
{
    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    /**
     * @var MetadataNodeVisitor
     */
    private $metadataNodeVisitor;

    /**
     * @var CloningVisitor
     */
    private $cloningVisitor;

    /**
     * @var ParentAndNextNodeVisitor
     */
    private $parentAndNextNodeVisitor;

    public function __construct(
        NodeScopeResolver $nodeScopeResolver,
        MetadataNodeVisitor $metadataNodeVisitor,
        ParentAndNextNodeVisitor $parentAndNextNodeVisitor,
        CloningVisitor $cloningVisitor
    ) {
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->metadataNodeVisitor = $metadataNodeVisitor;
        $this->parentAndNextNodeVisitor = $parentAndNextNodeVisitor;
        $this->cloningVisitor = $cloningVisitor;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function decorateNodesAndFile(array $nodes, string $filePath): array
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
        $nodes = $nodeTraverser->traverse($nodes);

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->metadataNodeVisitor);

        return $nodeTraverser->traverse($nodes);
    }
}
