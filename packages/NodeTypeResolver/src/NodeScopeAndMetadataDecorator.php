<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use Rector\NodeTypeResolver\NodeVisitor\MetadataNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeScopeResolver;
use Rector\PhpParser\NodeVisitor\ParentAndNextNodeAddingNodeVisitor;
use Symfony\Component\Finder\SplFileInfo;

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
     * @var ParentAndNextNodeAddingNodeVisitor
     */
    private $parentAndNextNodeAddingNodeVisitor;

    /**
     * @var CloningVisitor
     */
    private $cloningVisitor;

    public function __construct(
        NodeScopeResolver $nodeScopeResolver,
        MetadataNodeVisitor $metadataNodeVisitor,
        ParentAndNextNodeAddingNodeVisitor $parentAndNextNodeAddingNodeVisitor,
        CloningVisitor $cloningVisitor
    ) {
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->metadataNodeVisitor = $metadataNodeVisitor;
        $this->parentAndNextNodeAddingNodeVisitor = $parentAndNextNodeAddingNodeVisitor;
        $this->cloningVisitor = $cloningVisitor;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function processNodesAndSplFileInfo(array $nodes, SplFileInfo $splFileInfo): array
    {
        $nodeTraverser = new NodeTraverser();
        // specially rewrite nodes for PHPStan
        $nodeTraverser->addVisitor(new NameResolver());
        $nodes = $nodeTraverser->traverse($nodes);

        $nodes = $this->nodeScopeResolver->processNodes($nodes, $splFileInfo);

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver(null, [
            // this option would override old non-fqn-namespaced nodes otherwise, so it needs to be disabled
            'replaceNodes' => false,
        ]));
        $nodes = $nodeTraverser->traverse($nodes);

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->cloningVisitor); // needed also for format preserving printing
        $nodeTraverser->addVisitor($this->parentAndNextNodeAddingNodeVisitor);
        $nodes = $nodeTraverser->traverse($nodes);

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->metadataNodeVisitor);

        return $nodeTraverser->traverse($nodes);
    }
}
