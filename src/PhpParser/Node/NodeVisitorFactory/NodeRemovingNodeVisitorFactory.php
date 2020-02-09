<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\NodeVisitorFactory;

use PhpParser\Node;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\NodeVisitor\NodeRemovingNodeVisitor;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;

final class NodeRemovingNodeVisitorFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param Node[] $nodesToRemove
     */
    public function createFromNodesToRemove(array $nodesToRemove): NodeRemovingNodeVisitor
    {
        return new NodeRemovingNodeVisitor($nodesToRemove, $this->nodeFactory, $this->nodeNameResolver);
    }
}
