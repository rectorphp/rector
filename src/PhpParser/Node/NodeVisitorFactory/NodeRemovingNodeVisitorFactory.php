<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\NodeVisitorFactory;

use PhpParser\Node;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\NodeVisitor\NodeRemovingNodeVisitor;
use Rector\Core\PhpParser\Node\Resolver\NameResolver;

final class NodeRemovingNodeVisitorFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NodeFactory $nodeFactory, NameResolver $nameResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nameResolver = $nameResolver;
    }

    /**
     * @param Node[] $nodesToRemove
     */
    public function createFromNodesToRemove(array $nodesToRemove): NodeRemovingNodeVisitor
    {
        return new NodeRemovingNodeVisitor($nodesToRemove, $this->nodeFactory, $this->nameResolver);
    }
}
