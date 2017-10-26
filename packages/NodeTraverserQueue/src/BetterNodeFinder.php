<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue;

use PhpParser\Node;
use PhpParser\NodeFinder;
use Rector\Node\Attribute;

final class BetterNodeFinder
{
    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    public function __construct(NodeFinder $nodeFinder)
    {
        $this->nodeFinder = $nodeFinder;
    }

    public function findFirstAncestorInstanceOf(Node $node, string $type): ?Node
    {
        /** @var Node|null $currentNode */
        $currentNode = $node->getAttribute(Attribute::PARENT_NODE);

        while ($currentNode !== null) {
            if ($currentNode instanceof $type) {
                return $currentNode;
            }

            $currentNode = $currentNode->getAttribute(Attribute::PARENT_NODE);
        }

        return $currentNode;
    }

    /**
     * @param Node|Node[] $nodes
     * @return Node[]
     */
    public function findInstanceOf($nodes, string $type): array
    {
        return $this->nodeFinder->findInstanceOf($nodes, $type);
    }

    /**
     * @param Node|Node[] $nodes
     */
    public function findFirstInstanceOf($nodes, string $type): ?Node
    {
        return $this->nodeFinder->findFirstInstanceOf($nodes, $type);
    }
}
