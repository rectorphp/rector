<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Php\TypeChecker;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Core\Tests\PhpParser\Node\BetterNodeFinder\BetterNodeFinderTest
 */
final class BetterNodeFinder
{
    public function __construct(
        private readonly NodeFinder $nodeFinder,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly TypeChecker $typeChecker,
        private readonly NodeComparator $nodeComparator,
        private readonly ClassAnalyzer $classAnalyzer,
    ) {
    }

    /**
     * @template T of \PhpParser\Node
     * @param array<class-string<T>> $types
     * @return T|null
     */
    public function findParentByTypes(Node $currentNode, array $types): ?Node
    {
        Assert::allIsAOf($types, Node::class);

        while ($currentNode = $currentNode->getAttribute(AttributeKey::PARENT_NODE)) {
            if (! $currentNode instanceof Node) {
                return null;
            }

            foreach ($types as $type) {
                if (is_a($currentNode, $type, true)) {
                    return $currentNode;
                }
            }
        }

        return null;
    }

    /**
     * @template T of Node
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
     * @template T of Node
     * @param array<class-string<T>> $types
     * @param Node|Node[]|Stmt[] $nodes
     * @return T[]
     */
    public function findInstancesOf(Node | array $nodes, array $types): array
    {
        $foundInstances = [];
        foreach ($types as $type) {
            $currentFoundInstances = $this->findInstanceOf($nodes, $type);
            $foundInstances = array_merge($foundInstances, $currentFoundInstances);
        }

        return $foundInstances;
    }

    /**
     * @template T of Node
     * @param class-string<T> $type
     * @param Node|Node[]|Stmt[] $nodes
     * @return T[]
     */
    public function findInstanceOf(Node | array $nodes, string $type): array
    {
        return $this->nodeFinder->findInstanceOf($nodes, $type);
    }

    /**
     * @template T of Node
     * @param class-string<T> $type
     * @param Node|Node[] $nodes
     */
    public function findFirstInstanceOf(Node | array $nodes, string $type): ?Node
    {
        Assert::isAOf($type, Node::class);
        return $this->nodeFinder->findFirstInstanceOf($nodes, $type);
    }

    /**
     * @param class-string<Node> $type
     * @param Node|Node[] $nodes
     */
    public function hasInstanceOfName(Node | array $nodes, string $type, string $name): bool
    {
        Assert::isAOf($type, Node::class);
        return (bool) $this->findInstanceOfName($nodes, $type, $name);
    }

    /**
     * @param Node|Node[] $nodes
     */
    public function hasVariableOfName(Node | array $nodes, string $name): bool
    {
        return $this->findVariableOfName($nodes, $name) instanceof Node;
    }

    /**
     * @param Node|Node[] $nodes
     * @return Variable|null
     */
    public function findVariableOfName(Node | array $nodes, string $name): ?Node
    {
        return $this->findInstanceOfName($nodes, Variable::class, $name);
    }

    /**
     * @param Node|Node[] $nodes
     * @param array<class-string<Node>> $types
     */
    public function hasInstancesOf(Node | array $nodes, array $types): bool
    {
        Assert::allIsAOf($types, Node::class);

        foreach ($types as $type) {
            $foundNode = $this->nodeFinder->findFirstInstanceOf($nodes, $type);
            if (! $foundNode instanceof Node) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @template T of Node
     * @param class-string<T> $type
     * @param Node|Node[] $nodes
     */
    public function findLastInstanceOf(Node | array $nodes, string $type): ?Node
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
    public function find(Node | array $nodes, callable $filter): array
    {
        return $this->nodeFinder->find($nodes, $filter);
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
            return ! ($node instanceof Class_ && $this->classAnalyzer->isAnonymousClass($node));
        });
    }

    /**
     * @param Node|Node[] $nodes
     */
    public function findFirst(Node | array $nodes, callable $filter): ?Node
    {
        return $this->nodeFinder->findFirst($nodes, $filter);
    }

    /**
     * @return Assign[]
     */
    public function findClassMethodAssignsToLocalProperty(ClassMethod $classMethod, string $propertyName): array
    {
        return $this->find((array) $classMethod->stmts, function (Node $node) use ($propertyName): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            if (! $node->var instanceof PropertyFetch) {
                return false;
            }

            $propertyFetch = $node->var;
            if (! $this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
                return false;
            }

            return $this->nodeNameResolver->isName($propertyFetch->name, $propertyName);
        });
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

            return $this->nodeComparator->areNodesEqual($node->var, $expr);
        });
    }

    public function findFirstPreviousOfNode(Node $node, callable $filter): ?Node
    {
        // move to previous expression
        $previousStatement = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if ($previousStatement !== null) {
            $foundNode = $this->findFirst([$previousStatement], $filter);
            // we found what we need
            if ($foundNode !== null) {
                return $foundNode;
            }

            return $this->findFirstPreviousOfNode($previousStatement, $filter);
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof FunctionLike) {
            return null;
        }

        if ($parent instanceof Node) {
            return $this->findFirstPreviousOfNode($parent, $filter);
        }

        return null;
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
     * @template T of Node
     * @param array<class-string<T>> $types
     */
    public function findFirstPreviousOfTypes(Node $mainNode, array $types): ?Node
    {
        return $this->findFirstPrevious(
            $mainNode,
            fn (Node $node): bool => $this->typeChecker->isInstanceOf($node, $types)
        );
    }

    public function findFirstNext(Node $node, callable $filter): ?Node
    {
        $next = $node->getAttribute(AttributeKey::NEXT_NODE);
        if ($next instanceof Node) {
            if ($next instanceof Return_ && $next->expr === null) {
                return null;
            }

            $found = $this->findFirst($next, $filter);
            if ($found instanceof Node) {
                return $found;
            }

            return $this->findFirstNext($next, $filter);
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Return_ || $parent instanceof FunctionLike) {
            return null;
        }

        if ($parent instanceof Node) {
            return $this->findFirstNext($parent, $filter);
        }

        return null;
    }

    /**
     * @return Expr[]
     */
    public function findSameNamedExprs(Expr | Variable | Property | PropertyFetch | StaticPropertyFetch $expr): array
    {
        // assign of empty string to something
        $scopeNode = $this->findParentScope($expr);
        if (! $scopeNode instanceof Node) {
            return [];
        }

        if ($expr instanceof Variable) {
            $exprName = $this->nodeNameResolver->getName($expr);
            if ($exprName === null) {
                return [];
            }

            $variables = $this->findInstancesOf($scopeNode, [Variable::class]);

            return array_filter(
                $variables,
                fn (Variable $variable): bool => $this->nodeNameResolver->isName($variable, $exprName)
            );
        }

        if ($expr instanceof Property) {
            $singleProperty = $expr->props[0];
            $exprName = $this->nodeNameResolver->getName($singleProperty->name);
        } elseif ($expr instanceof StaticPropertyFetch || $expr instanceof PropertyFetch) {
            $exprName = $this->nodeNameResolver->getName($expr->name);
        } else {
            return [];
        }

        if ($exprName === null) {
            return [];
        }

        $propertyFetches = $this->findInstancesOf($scopeNode, [PropertyFetch::class, StaticPropertyFetch::class]);

        return array_filter(
            $propertyFetches,
            fn (PropertyFetch | StaticPropertyFetch $propertyFetch): bool =>
                $this->nodeNameResolver->isName($propertyFetch->name, $exprName)
        );
    }

    /**
     * @template T of Node
     * @param array<class-string<T>>|class-string<T> $types
     */
    public function hasInstancesOfInFunctionLikeScoped(ClassMethod | Function_ $functionLike, string|array $types): bool
    {
        if (is_string($types)) {
            $types = [$types];
        }

        foreach ($types as $type) {
            $foundNode = $this->findFirstInstanceOf((array) $functionLike->stmts, $type);
            if (! $foundNode instanceof Node) {
                continue;
            }

            $parentFunctionLike = $this->findParentType($foundNode, $functionLike::class);
            if ($parentFunctionLike === $functionLike) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template T of Node
     * @param Node|Node[] $nodes
     * @param class-string<T> $type
     */
    private function findInstanceOfName(Node | array $nodes, string $type, string $name): ?Node
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

    private function findParentScope(Node $node): Node|null
    {
        return $this->findParentByTypes($node, [
            Closure::class,
            Function_::class,
            ClassMethod::class,
            Class_::class,
            Namespace_::class,
        ]);
    }
}
