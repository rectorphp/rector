<?php declare(strict_types=1);

namespace Rector\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use Rector\NodeTypeResolver\Node\Attribute;

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
     * @param Node|Node[]|Stmt[] $nodes
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
        if ($foundInstances === []) {
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

    public function findFirstPrevious(Node $node, callable $filter): ?Node
    {
        $node = $node instanceof Expression ? $node : $node->getAttribute(Attribute::CURRENT_EXPRESSION);
        if ($node === null) {
            return null;
        }

        $foundNode = $this->findFirst([$node], $filter);
        // we found what we need
        if ($foundNode !== null) {
            return $foundNode;
        }

        // move to next expression
        $previousExpression = $node->getAttribute(Attribute::PREVIOUS_EXPRESSION);
        if ($previousExpression === null) {
            return null;
        }

        return $this->findFirstPrevious($previousExpression, $filter);
    }
}
