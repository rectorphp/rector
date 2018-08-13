<?php declare(strict_types=1);

namespace Rector\Utils;

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

        return null;
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

    /**
     * @param Node|Node[] $nodes
     */
    public function findLastInstanceOf($nodes, string $type): ?Node
    {
        $foundInstances = $this->nodeFinder->findInstanceOf($nodes, $type);
        if (! $foundInstances) {
            return null;
        }

        return array_pop($foundInstances);
    }

    /**
     * @param Node|Node[] $nodes
     * @return Node[]
     */
    public function find($nodes, callable $filter): array
    {
        return $this->nodeFinder->find($nodes, $filter);
    }

    /**
     * @param Node|Node[] $nodes
     */
    public function findFirst($nodes, callable $filter): ?Node
    {
        return $this->nodeFinder->findFirst($nodes, $filter);
    }
}
