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
        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->traverse($nodes);

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->metadataNodeVisitor);
        $nodeTraverser->traverse($nodes);

        $nodes = $nodeTraverser->traverse($nodes);
        $this->nodeScopeResolver->processNodes($nodes, $splFileInfo);

        return $nodes;
    }
}
