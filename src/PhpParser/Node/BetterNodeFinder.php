<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\Util\StaticInstanceOf;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Php\TypeChecker;
use Webmozart\Assert\Assert;

/**
 * @template T of Node
 * @see \Rector\Core\Tests\PhpParser\Node\BetterNodeFinder\BetterNodeFinderTest
 */
final class BetterNodeFinder
{
    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var TypeChecker
     */
    private $typeChecker;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        NodeFinder $nodeFinder,
        NodeNameResolver $nodeNameResolver,
        TypeChecker $typeChecker
    ) {
        $this->nodeFinder = $nodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->typeChecker = $typeChecker;
    }

    /**
     * @param class-string<T> $type
     * @return T|null
     */
    public function findParentType(Node $node, string $type): ?Node
    {
        Assert::isAOf($type, Node::class);

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Node) {
            return null;
        }

        do {
            if (is_a($parent, $type, true)) {
                return $parent;
            }

            if (! $parent instanceof Node) {
                return null;
            }
        } while ($parent = $parent->getAttribute(AttributeKey::PARENT_NODE));

        return null;
    }

    /**
     * @param class-string<T>[] $types
     * @return T|null
     */
    public function findParentTypes(Node $node, array $types): ?Node
    {
        Assert::allIsAOf($types, Node::class);

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Node) {
            return null;
        }

        do {
            if (StaticInstanceOf::isOneOf($parent, $types)) {
                return $parent;
            }

            if ($parent === null) {
                return null;
            }
        } while ($parent = $parent->getAttribute(AttributeKey::PARENT_NODE));

        return null;
    }

    /**
     * @param class-string<T> $type
     * @return T|null
     */
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
     * @param array<class-string<T>> $types
     * @return T|null
     */
    public function findFirstAncestorInstancesOf(Node $node, array $types): ?Node
    {
        $currentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        while ($currentNode instanceof Node) {
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
     * @param class-string<T> $type
     * @param Node|Node[]|Stmt[] $nodes
     * @return T[]
     */
    public function findInstanceOf($nodes, string $type): array
    {
        Assert::isAOf($type, Node::class);

        return $this->nodeFinder->findInstanceOf($nodes, $type);
    }

    /**
     * @param class-string<T> $type
     * @param Node|Node[] $nodes
     * @return T|null
     */
    public function findFirstInstanceOf($nodes, string $type): ?Node
    {
        Assert::isAOf($type, Node::class);

        return $this->nodeFinder->findFirstInstanceOf($nodes, $type);
    }

    /**
     * @param class-string<T> $type
     * @param Node|Node[] $nodes
     */
    public function hasInstanceOfName($nodes, string $type, string $name): bool
    {
        Assert::isAOf($type, Node::class);

        return (bool) $this->findInstanceOfName($nodes, $type, $name);
    }

    /**
     * @param Node|Node[] $nodes
     */
    public function hasVariableOfName($nodes, string $name): bool
    {
        return (bool) $this->findVariableOfName($nodes, $name);
    }

    /**
     * @param Node|Node[] $nodes
     * @return Variable|null
     */
    public function findVariableOfName($nodes, string $name): ?Node
    {
        return $this->findInstanceOfName($nodes, Variable::class, $name);
    }

    /**
     * @param Node|Node[] $nodes
     * @param class-string<T>[] $types
     */
    public function hasInstancesOf($nodes, array $types): bool
    {
        Assert::allIsAOf($types, Node::class);

        foreach ($types as $type) {
            $nodeFinderFindFirstInstanceOf = $this->nodeFinder->findFirstInstanceOf($nodes, $type);

            if (! $nodeFinderFindFirstInstanceOf instanceof Node) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param class-string<T> $type
     * @param Node|Node[] $nodes
     * @return T|null
     */
    public function findLastInstanceOf($nodes, string $type): ?Node
    {
        Assert::isAOf($type, Node::class);

        $foundInstances = $this->nodeFinder->findInstanceOf($nodes, $type);
        if ($foundInstances === []) {
            return null;
        }

        $lastItemKey = array_key_last($foundInstances);
        return $foundInstances[$lastItemKey];
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
     * @param Node[]|Node $nodes
     * @return ClassLike[]
     */
    public function findClassLikes($nodes): array
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
     * @param Node[] $nodes
     * @return ClassLike|null
     */
    public function findFirstNonAnonymousClass(array $nodes): ?Node
    {
        return $this->findFirst($nodes, function (Node $node): bool {
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

    /**
     * @return Assign|null
     */
    public function findPreviousAssignToExpr(Expr $expr): ?Node
    {
        return $this->findFirstPrevious($expr, function (Node $node) use ($expr): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            return $this->betterStandardPrinter->areNodesEqual($node->var, $expr);
        });
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

        // move to previous expression
        $previousStatement = $node->getAttribute(AttributeKey::PREVIOUS_STATEMENT);
        if ($previousStatement !== null) {
            return $this->findFirstPrevious($previousStatement, $filter);
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent === null) {
            return null;
        }

        return $this->findFirstPrevious($parent, $filter);
    }

    /**
     * @param class-string<T>[] $types
     * @return T|null
     */
    public function findFirstPreviousOfTypes(Node $mainNode, array $types): ?Node
    {
        return $this->findFirstPrevious($mainNode, function (Node $node) use ($types): bool {
            return $this->typeChecker->isInstanceOf($node, $types);
        });
    }

    public function findFirstNext(Node $node, callable $filter): ?Node
    {
        $next = $node->getAttribute(AttributeKey::NEXT_NODE);
        if ($next instanceof Node) {
            $found = $this->findFirst($next, $filter);
            if ($found instanceof Node) {
                return $found;
            }

            return $this->findFirstNext($next, $filter);
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Node) {
            return $this->findFirstNext($parent, $filter);
        }

        return null;
    }

    /**
     * @param Node|Node[] $nodes
     * @param class-string<T> $type
     * @return T|null
     */
    private function findInstanceOfName($nodes, string $type, string $name): ?Node
    {
        Assert::isAOf($type, Node::class);

        $foundInstances = $this->nodeFinder->findInstanceOf($nodes, $type);
        foreach ($foundInstances as $foundInstance) {
            if (! $this->nodeNameResolver->isName($foundInstance, $name)) {
                continue;
            }

            return $foundInstance;
        }

        return null;
    }
}
