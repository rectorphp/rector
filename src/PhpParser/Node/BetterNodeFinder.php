<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Php\TypeChecker;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Core\Tests\PhpParser\Node\BetterNodeFinder\BetterNodeFinderTest
 */
final class BetterNodeFinder
{
    public function __construct(
        private NodeFinder $nodeFinder,
        private NodeNameResolver $nodeNameResolver,
        private TypeChecker $typeChecker,
        private NodeComparator $nodeComparator,
        private ClassAnalyzer $classAnalyzer,
        private ParentScopeFinder $parentScopeFinder
    ) {
    }

    /**
     * @template T of Node
     * @param class-string<T> $type
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
        return (bool) $this->findVariableOfName($nodes, $name);
    }

//    /**
//     * @param Node|Stmt[] $nodes
//     * @return Variable[]
//     */
//    public function findVariablesOfName(Node | array $nodes, string $variableName): array
//    {
//        /** @var Variable[] $variables */
//        $variables = $this->findInstanceOf($nodes, Variable::class);
//
//        $variablesOfName = [];
//
//        foreach ($variables as $variable) {
//            if (! $this->nodeNameResolver->isName($variable, $variableName)) {
//                continue;
//            }
//
//            $variablesOfName[] = $variable;
//        }
//
//        return $variablesOfName;
//    }

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
     * Excludes anonymous classes!
     *
     * @param Node[]|Node $nodes
     * @return ClassLike[]
     */
    public function findClassLikes(array | Node $nodes): array
    {
        return $this->find($nodes, function (Node $node): bool {
            if (! $node instanceof ClassLike) {
                return false;
            }
            // skip anonymous classes
            return ! ($node instanceof Class_ && $this->classAnalyzer->isAnonymousClass($node));
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
        $scopeNode = $this->parentScopeFinder->find($expr);
        if ($scopeNode === null) {
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
                fn (Variable $variable) => $this->nodeNameResolver->isName($variable, $exprName)
            );
        }

        if ($expr instanceof Property) {
            $singleProperty = $expr->props[0];
            $exprName = $this->nodeNameResolver->getName($singleProperty->name);
        } elseif ($expr instanceof StaticPropertyFetch || $expr instanceof PropertyFetch) {
            $exprName = $this->nodeNameResolver->getName($expr->name);
        } else {
            throw new NotImplementedYetException();
        }

        if ($exprName === null) {
            return [];
        }

        $propertyFetches = $this->findInstancesOf($scopeNode, [PropertyFetch::class, StaticPropertyFetch::class]);

        return array_filter(
            $propertyFetches,
            fn (PropertyFetch | StaticPropertyFetch $propertyFetch) =>
                $this->nodeNameResolver->isName($propertyFetch->name, $exprName)
        );
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
}
