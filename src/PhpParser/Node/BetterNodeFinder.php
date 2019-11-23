<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Tests\PhpParser\Node\BetterNodeFinder\BetterNodeFinderTest
 */
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

    /**
     * @param string|string[] $type
     */
    public function findFirstParentInstanceOf(Node $node, $type): ?Node
    {
        if (! is_array($type)) {
            $type = [$type];
        }

        /** @var Node|null $parentNode */
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        if ($parentNode === null) {
            return null;
        }

        do {
            if ($this->isTypes($parentNode, $type)) {
                return $parentNode;
            }

            if ($parentNode === null) {
                return null;
            }
        } while ($parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE));

        return null;
    }

    public function findFirstAncestorInstanceOf(Node $node, string $type): ?Node
    {
        $currentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        while ($currentNode !== null) {
            if ($currentNode instanceof $type) {
                return $currentNode;
            }

            $currentNode = $currentNode->getAttribute(AttributeKey::PARENT_NODE);
        }

        return null;
    }

    /**
     * @param string[] $types
     */
    public function findFirstAncestorInstancesOf(Node $node, array $types): ?Node
    {
        $currentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        while ($currentNode !== null) {
            foreach ($types as $type) {
                if (is_a($currentNode, $type, true)) {
                    return $currentNode;
                }
            }

            $currentNode = $currentNode->getAttribute(AttributeKey::PARENT_NODE);
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
     * Excludes anonymous classes!
     *
     * @param Node[] $nodes
     * @return ClassLike[]
     */
    public function findClassLikes(array $nodes): array
    {
        return $this->find($nodes, function (Node $node): bool {
            if (! $node instanceof ClassLike) {
                return false;
            }
            // skip anonymous classes
            return ! ($node instanceof Class_ && $node->isAnonymous());
        });
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
        $node = $node instanceof Expression ? $node : $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if ($node === null) {
            return null;
        }

        $foundNode = $this->findFirst([$node], $filter);
        // we found what we need
        if ($foundNode !== null) {
            return $foundNode;
        }

        // move to next expression
        $previousStatement = $node->getAttribute(AttributeKey::PREVIOUS_STATEMENT);
        if ($previousStatement === null) {
            return null;
        }

        return $this->findFirstPrevious($previousStatement, $filter);
    }

    /**
     * @param Node|Node[] $nodes
     */
    public function findFirstClass($nodes): ?Class_
    {
        /** @var Class_[] $classes */
        $classes = $this->findInstanceOf($nodes, Class_::class);
        foreach ($classes as $class) {
            if ($class->isAnonymous()) {
                continue;
            }

            return $class;
        }

        return null;
    }

    /**
     * @param string[] $types
     */
    private function isTypes(Node $node, array $types): bool
    {
        foreach ($types as $type) {
            if (is_a($node, $type, true)) {
                return true;
            }
        }

        return false;
    }
}
