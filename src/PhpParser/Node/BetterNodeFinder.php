<?php

declare (strict_types=1);
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
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix202208\Symplify\PackageBuilder\Php\TypeChecker;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Core\Tests\PhpParser\Node\BetterNodeFinder\BetterNodeFinderTest
 */
final class BetterNodeFinder
{
    /**
     * @readonly
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Php\TypeChecker
     */
    private $typeChecker;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(NodeFinder $nodeFinder, NodeNameResolver $nodeNameResolver, TypeChecker $typeChecker, NodeComparator $nodeComparator, ClassAnalyzer $classAnalyzer)
    {
        $this->nodeFinder = $nodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->typeChecker = $typeChecker;
        $this->nodeComparator = $nodeComparator;
        $this->classAnalyzer = $classAnalyzer;
    }
    /**
     * @template T of \PhpParser\Node
     * @param array<class-string<T>> $types
     * @return T|null
     */
    public function findParentByTypes(Node $currentNode, array $types) : ?Node
    {
        Assert::allIsAOf($types, Node::class);
        while ($currentNode = $currentNode->getAttribute(AttributeKey::PARENT_NODE)) {
            if (!$currentNode instanceof Node) {
                return null;
            }
            foreach ($types as $type) {
                if (\is_a($currentNode, $type, \true)) {
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
    public function findParentType(Node $node, string $type) : ?Node
    {
        Assert::isAOf($type, Node::class);
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parent instanceof Node) {
            return null;
        }
        do {
            if (\is_a($parent, $type, \true)) {
                return $parent;
            }
            $parent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        } while ($parent instanceof Node);
        return null;
    }
    /**
     * @template T of Node
     * @param array<class-string<T>> $types
     * @param \PhpParser\Node|mixed[] $nodes
     * @return T[]
     */
    public function findInstancesOf($nodes, array $types) : array
    {
        $foundInstances = [];
        foreach ($types as $type) {
            $currentFoundInstances = $this->findInstanceOf($nodes, $type);
            $foundInstances = \array_merge($foundInstances, $currentFoundInstances);
        }
        return $foundInstances;
    }
    /**
     * @template T of Node
     * @param class-string<T> $type
     * @param \PhpParser\Node|mixed[] $nodes
     * @return T[]
     */
    public function findInstanceOf($nodes, string $type) : array
    {
        return $this->nodeFinder->findInstanceOf($nodes, $type);
    }
    /**
     * @template T of Node
     * @param class-string<T> $type
     * @return T|null
     *
     * @param \PhpParser\Node|mixed[] $nodes
     */
    public function findFirstInstanceOf($nodes, string $type) : ?Node
    {
        Assert::isAOf($type, Node::class);
        return $this->nodeFinder->findFirstInstanceOf($nodes, $type);
    }
    /**
     * @param class-string<Node> $type
     * @param Node[] $nodes
     */
    public function hasInstanceOfName(array $nodes, string $type, string $name) : bool
    {
        Assert::isAOf($type, Node::class);
        return (bool) $this->findInstanceOfName($nodes, $type, $name);
    }
    /**
     * @param Node[] $nodes
     */
    public function hasVariableOfName(array $nodes, string $name) : bool
    {
        return $this->findVariableOfName($nodes, $name) instanceof Node;
    }
    /**
     * @api
     * @param \PhpParser\Node|mixed[] $nodes
     * @return Variable|null
     */
    public function findVariableOfName($nodes, string $name) : ?Node
    {
        return $this->findInstanceOfName($nodes, Variable::class, $name);
    }
    /**
     * @param \PhpParser\Node|mixed[] $nodes
     * @param array<class-string<Node>> $types
     */
    public function hasInstancesOf($nodes, array $types) : bool
    {
        Assert::allIsAOf($types, Node::class);
        foreach ($types as $type) {
            $foundNode = $this->nodeFinder->findFirstInstanceOf($nodes, $type);
            if (!$foundNode instanceof Node) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @template T of Node
     *
     * @param Stmt[] $nodes
     * @param class-string<T> $type
     */
    public function findLastInstanceOf(array $nodes, string $type) : ?Node
    {
        Assert::allIsAOf($nodes, Stmt::class);
        Assert::isAOf($type, Node::class);
        $foundInstances = $this->nodeFinder->findInstanceOf($nodes, $type);
        if ($foundInstances === []) {
            return null;
        }
        \end($foundInstances);
        $lastItemKey = \key($foundInstances);
        return $foundInstances[$lastItemKey];
    }
    /**
     * @param \PhpParser\Node|mixed[] $nodes
     * @param callable(Node $node): bool $filter
     * @return Node[]
     */
    public function find($nodes, callable $filter) : array
    {
        return $this->nodeFinder->find($nodes, $filter);
    }
    /**
     * @param Node[] $nodes
     * @return ClassLike|null
     */
    public function findFirstNonAnonymousClass(array $nodes) : ?Node
    {
        // skip anonymous classes
        return $this->findFirst($nodes, function (Node $node) : bool {
            return $node instanceof Class_ && !$this->classAnalyzer->isAnonymousClass($node);
        });
    }
    /**
     * @param \PhpParser\Node|mixed[] $nodes
     * @param callable(Node $filter): bool $filter
     */
    public function findFirst($nodes, callable $filter) : ?Node
    {
        return $this->nodeFinder->findFirst($nodes, $filter);
    }
    /**
     * @return Assign[]
     */
    public function findClassMethodAssignsToLocalProperty(ClassMethod $classMethod, string $propertyName) : array
    {
        return $this->find((array) $classMethod->stmts, function (Node $node) use($classMethod, $propertyName) : bool {
            if (!$node instanceof Assign) {
                return \false;
            }
            if (!$node->var instanceof PropertyFetch) {
                return \false;
            }
            $propertyFetch = $node->var;
            if (!$this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
                return \false;
            }
            $parentFunctionLike = $this->findParentType($node, ClassMethod::class);
            if ($parentFunctionLike !== $classMethod) {
                return \false;
            }
            return $this->nodeNameResolver->isName($propertyFetch->name, $propertyName);
        });
    }
    /**
     * @return Assign|null
     */
    public function findPreviousAssignToExpr(Expr $expr) : ?Node
    {
        return $this->findFirstPrevious($expr, function (Node $node) use($expr) : bool {
            if (!$node instanceof Assign) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node->var, $expr);
        });
    }
    /**
     * Only search in previous Node/Stmt
     * @api
     *
     * @param callable(Node $node): bool $filter
     */
    public function findFirstInlinedPrevious(Node $node, callable $filter) : ?Node
    {
        $previousNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (!$previousNode instanceof Node) {
            return null;
        }
        $foundNode = $this->findFirst($previousNode, $filter);
        // we found what we need
        if ($foundNode instanceof Node) {
            return $foundNode;
        }
        return $this->findFirstInlinedPrevious($previousNode, $filter);
    }
    /**
     * Search in previous Node/Stmt, when no Node found, lookup previous Stmt of Parent Node
     *
     * @param callable(Node $node): bool $filter
     */
    public function findFirstPrevious(Node $node, callable $filter) : ?Node
    {
        $foundNode = $this->findFirstInlinedPrevious($node, $filter);
        // we found what we need
        if ($foundNode instanceof Node) {
            return $foundNode;
        }
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof FunctionLike) {
            return null;
        }
        if ($parent instanceof Node) {
            return $this->findFirstPrevious($parent, $filter);
        }
        return null;
    }
    /**
     * @api
     * @template T of Node
     * @param array<class-string<T>> $types
     */
    public function findFirstPreviousOfTypes(Node $mainNode, array $types) : ?Node
    {
        return $this->findFirstPrevious($mainNode, function (Node $node) use($types) : bool {
            return $this->typeChecker->isInstanceOf($node, $types);
        });
    }
    /**
     * @param callable(Node $node): bool $filter
     */
    public function findFirstNext(Node $node, callable $filter) : ?Node
    {
        $next = $node->getAttribute(AttributeKey::NEXT_NODE);
        if ($next instanceof Node) {
            if ($next instanceof Return_ && $next->expr === null) {
                $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
                if (!$parent instanceof Case_) {
                    return null;
                }
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
     * @api
     * @return Expr[]
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $expr
     */
    public function findSameNamedExprs($expr) : array
    {
        // assign of empty string to something
        $scopeNode = $this->findParentScope($expr);
        if (!$scopeNode instanceof Node) {
            return [];
        }
        if ($expr instanceof Variable) {
            $exprName = $this->nodeNameResolver->getName($expr);
            if ($exprName === null) {
                return [];
            }
            $variables = $this->findInstancesOf($scopeNode, [Variable::class]);
            return \array_filter($variables, function (Variable $variable) use($exprName) : bool {
                return $this->nodeNameResolver->isName($variable, $exprName);
            });
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
        return \array_filter($propertyFetches, function ($propertyFetch) use($exprName) : bool {
            return $this->nodeNameResolver->isName($propertyFetch->name, $exprName);
        });
    }
    /**
     * @template T of Node
     * @param array<class-string<T>>|class-string<T> $types
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function hasInstancesOfInFunctionLikeScoped($functionLike, $types) : bool
    {
        if (\is_string($types)) {
            $types = [$types];
        }
        foreach ($types as $type) {
            $foundNodes = $this->findInstanceOf((array) $functionLike->stmts, $type);
            foreach ($foundNodes as $foundNode) {
                $parentFunctionLike = $this->findParentByTypes($foundNode, [ClassMethod::class, Function_::class, Closure::class]);
                if ($parentFunctionLike === $functionLike) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * @template T of Node
     * @param array<class-string<T>>|class-string<T> $types
     * @return T[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function findInstancesOfInFunctionLikeScoped($functionLike, $types) : array
    {
        if (\is_string($types)) {
            $types = [$types];
        }
        /** @var T[] $foundNodes */
        $foundNodes = [];
        foreach ($types as $type) {
            /** @var T[] $nodes */
            $nodes = $this->findInstanceOf((array) $functionLike->stmts, $type);
            if ($nodes === []) {
                continue;
            }
            foreach ($nodes as $key => $node) {
                $parentFunctionLike = $this->findParentByTypes($node, [ClassMethod::class, Function_::class, Closure::class]);
                if ($parentFunctionLike !== $functionLike) {
                    unset($nodes[$key]);
                }
            }
            if ($nodes === []) {
                continue;
            }
            $foundNodes = \array_merge($foundNodes, $nodes);
        }
        return $foundNodes;
    }
    /**
     * @param callable(Node $node): bool $filter
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function findFirstInFunctionLikeScoped($functionLike, callable $filter) : ?Node
    {
        $foundNode = $this->findFirst((array) $functionLike->stmts, $filter);
        if (!$foundNode instanceof Node) {
            return null;
        }
        $parentFunctionLike = $this->findParentByTypes($foundNode, [ClassMethod::class, Function_::class, Closure::class, Class_::class]);
        if ($parentFunctionLike !== $functionLike) {
            return null;
        }
        return $foundNode;
    }
    public function resolveCurrentStatement(Node $node) : ?Stmt
    {
        if ($node instanceof Stmt) {
            return $node;
        }
        $currentStmt = $node;
        while ($currentStmt = $currentStmt->getAttribute(AttributeKey::PARENT_NODE)) {
            if ($currentStmt instanceof Stmt) {
                return $currentStmt;
            }
            if (!$currentStmt instanceof Node) {
                return null;
            }
        }
        return null;
    }
    /**
     * @template T of Node
     * @param \PhpParser\Node|mixed[] $nodes
     * @param class-string<T> $type
     */
    private function findInstanceOfName($nodes, string $type, string $name) : ?Node
    {
        Assert::isAOf($type, Node::class);
        $foundInstances = $this->nodeFinder->findInstanceOf($nodes, $type);
        foreach ($foundInstances as $foundInstance) {
            if (!$this->nodeNameResolver->isName($foundInstance, $name)) {
                continue;
            }
            return $foundInstance;
        }
        return null;
    }
    /**
     * @return Closure|Function_|ClassMethod|Class_|Namespace_|null
     */
    private function findParentScope(Node $node)
    {
        return $this->findParentByTypes($node, [Closure::class, Function_::class, ClassMethod::class, Class_::class, Namespace_::class]);
    }
}
