<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\NodeVisitorFactory;

use PhpParser\Node;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\Node\NodeVisitor\NodeRemovingNodeVisitor;
use Rector\PhpParser\Node\Resolver\NameResolver;

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
