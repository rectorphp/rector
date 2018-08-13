<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rector\NodeTypeResolver\NodeVisitor\MetadataNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeScopeResolver;
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

    public function __construct(NodeScopeResolver $nodeScopeResolver, MetadataNodeVisitor $metadataNodeVisitor)
    {
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->metadataNodeVisitor = $metadataNodeVisitor;
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
            'replaceNodes' => false,
        ]));
        $nodeTraverser->addVisitor($this->metadataNodeVisitor);

        return $nodeTraverser->traverse($nodes);
    }
}
