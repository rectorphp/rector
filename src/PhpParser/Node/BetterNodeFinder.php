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
use RectorPrefix20220418\Symplify\PackageBuilder\Php\TypeChecker;
use RectorPrefix20220418\Webmozart\Assert\Assert;
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
    public function __construct(\PhpParser\NodeFinder $nodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20220418\Symplify\PackageBuilder\Php\TypeChecker $typeChecker, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer)
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
    public function findParentByTypes(\PhpParser\Node $currentNode, array $types) : ?\PhpParser\Node
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::allIsAOf($types, \PhpParser\Node::class);
        while ($currentNode = $currentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE)) {
            if (!$currentNode instanceof \PhpParser\Node) {
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
    public function findParentType(\PhpParser\Node $node, string $type) : ?\PhpParser\Node
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::isAOf($type, \PhpParser\Node::class);
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            return null;
        }
        do {
            if (\is_a($parent, $type, \true)) {
                return $parent;
            }
            if (!$parent instanceof \PhpParser\Node) {
                return null;
            }
        } while ($parent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE));
        return null;
    }
    /**
     * @template T of Node
     * @param array<class-string<T>> $types
     * @param mixed[]|\PhpParser\Node $nodes
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
     * @param mixed[]|\PhpParser\Node $nodes
     * @return T[]
     */
    public function findInstanceOf($nodes, string $type) : array
    {
        return $this->nodeFinder->findInstanceOf($nodes, $type);
    }
    /**
     * @template T of Node
     * @param class-string<T> $type
     * @param mixed[]|\PhpParser\Node $nodes
     */
    public function findFirstInstanceOf($nodes, string $type) : ?\PhpParser\Node
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::isAOf($type, \PhpParser\Node::class);
        return $this->nodeFinder->findFirstInstanceOf($nodes, $type);
    }
    /**
     * @param class-string<Node> $type
     * @param mixed[]|\PhpParser\Node $nodes
     */
    public function hasInstanceOfName($nodes, string $type, string $name) : bool
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::isAOf($type, \PhpParser\Node::class);
        return (bool) $this->findInstanceOfName($nodes, $type, $name);
    }
    /**
     * @param mixed[]|\PhpParser\Node $nodes
     */
    public function hasVariableOfName($nodes, string $name) : bool
    {
        return $this->findVariableOfName($nodes, $name) instanceof \PhpParser\Node;
    }
    /**
     * @param mixed[]|\PhpParser\Node $nodes
     * @return Variable|null
     */
    public function findVariableOfName($nodes, string $name) : ?\PhpParser\Node
    {
        return $this->findInstanceOfName($nodes, \PhpParser\Node\Expr\Variable::class, $name);
    }
    /**
     * @param mixed[]|\PhpParser\Node $nodes
     * @param array<class-string<Node>> $types
     */
    public function hasInstancesOf($nodes, array $types) : bool
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::allIsAOf($types, \PhpParser\Node::class);
        foreach ($types as $type) {
            $foundNode = $this->nodeFinder->findFirstInstanceOf($nodes, $type);
            if (!$foundNode instanceof \PhpParser\Node) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @template T of Node
     * @param class-string<T> $type
     * @param mixed[]|\PhpParser\Node $nodes
     */
    public function findLastInstanceOf($nodes, string $type) : ?\PhpParser\Node
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::isAOf($type, \PhpParser\Node::class);
        $foundInstances = $this->nodeFinder->findInstanceOf($nodes, $type);
        if ($foundInstances === []) {
            return null;
        }
        \end($foundInstances);
        $lastItemKey = \key($foundInstances);
        return $foundInstances[$lastItemKey];
    }
    /**
     * @param mixed[]|\PhpParser\Node $nodes
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
    public function findFirstNonAnonymousClass(array $nodes) : ?\PhpParser\Node
    {
        return $this->findFirst($nodes, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Stmt\ClassLike) {
                return \false;
            }
            // skip anonymous classes
            return !($node instanceof \PhpParser\Node\Stmt\Class_ && $this->classAnalyzer->isAnonymousClass($node));
        });
    }
    /**
     * @param mixed[]|\PhpParser\Node $nodes
     * @param callable(Node $filter): bool $filter
     */
    public function findFirst($nodes, callable $filter) : ?\PhpParser\Node
    {
        return $this->nodeFinder->findFirst($nodes, $filter);
    }
    /**
     * @return Assign[]
     */
    public function findClassMethodAssignsToLocalProperty(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $propertyName) : array
    {
        return $this->find((array) $classMethod->stmts, function (\PhpParser\Node $node) use($classMethod, $propertyName) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return \false;
            }
            if (!$node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return \false;
            }
            $propertyFetch = $node->var;
            if (!$this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
                return \false;
            }
            $parentFunctionLike = $this->findParentType($node, \PhpParser\Node\Stmt\ClassMethod::class);
            if ($parentFunctionLike !== $classMethod) {
                return \false;
            }
            return $this->nodeNameResolver->isName($propertyFetch->name, $propertyName);
        });
    }
    /**
     * @return Assign|null
     */
    public function findPreviousAssignToExpr(\PhpParser\Node\Expr $expr) : ?\PhpParser\Node
    {
        return $this->findFirstPrevious($expr, function (\PhpParser\Node $node) use($expr) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node->var, $expr);
        });
    }
    /**
     * @param callable(Node $node): bool $filter
     */
    public function findFirstPreviousOfNode(\PhpParser\Node $node, callable $filter, bool $lookupParent = \true) : ?\PhpParser\Node
    {
        // move to previous expression
        $previousStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        if ($previousStatement !== null) {
            $foundNode = $this->findFirst([$previousStatement], $filter);
            // we found what we need
            if ($foundNode !== null) {
                return $foundNode;
            }
            return $this->findFirstPreviousOfNode($previousStatement, $filter);
        }
        if (!$lookupParent) {
            return null;
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\FunctionLike) {
            return null;
        }
        if ($parent instanceof \PhpParser\Node) {
            return $this->findFirstPreviousOfNode($parent, $filter);
        }
        return null;
    }
    /**
     * @param callable(Node $node): bool $filter
     */
    public function findFirstPrevious(\PhpParser\Node $node, callable $filter) : ?\PhpParser\Node
    {
        $node = $node instanceof \PhpParser\Node\Stmt\Expression ? $node : $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!$node instanceof \PhpParser\Node) {
            return null;
        }
        $foundNode = $this->findFirst([$node], $filter);
        // we found what we need
        if ($foundNode instanceof \PhpParser\Node) {
            return $foundNode;
        }
        // move to previous expression
        $previousStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT);
        if ($previousStatement instanceof \PhpParser\Node) {
            return $this->findFirstPrevious($previousStatement, $filter);
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            return null;
        }
        return $this->findFirstPrevious($parent, $filter);
    }
    /**
     * @template T of Node
     * @param array<class-string<T>> $types
     */
    public function findFirstPreviousOfTypes(\PhpParser\Node $mainNode, array $types) : ?\PhpParser\Node
    {
        return $this->findFirstPrevious($mainNode, function (\PhpParser\Node $node) use($types) : bool {
            return $this->typeChecker->isInstanceOf($node, $types);
        });
    }
    /**
     * @param callable(Node $node): bool $filter
     */
    public function findFirstNext(\PhpParser\Node $node, callable $filter) : ?\PhpParser\Node
    {
        $next = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if ($next instanceof \PhpParser\Node) {
            if ($next instanceof \PhpParser\Node\Stmt\Return_ && $next->expr === null) {
                return null;
            }
            $found = $this->findFirst($next, $filter);
            if ($found instanceof \PhpParser\Node) {
                return $found;
            }
            return $this->findFirstNext($next, $filter);
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Stmt\Return_ || $parent instanceof \PhpParser\Node\FunctionLike) {
            return null;
        }
        if ($parent instanceof \PhpParser\Node) {
            return $this->findFirstNext($parent, $filter);
        }
        return null;
    }
    /**
     * @return Expr[]
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch|\PhpParser\Node\Expr\Variable|\PhpParser\Node\Stmt\Property $expr
     */
    public function findSameNamedExprs($expr) : array
    {
        // assign of empty string to something
        $scopeNode = $this->findParentScope($expr);
        if (!$scopeNode instanceof \PhpParser\Node) {
            return [];
        }
        if ($expr instanceof \PhpParser\Node\Expr\Variable) {
            $exprName = $this->nodeNameResolver->getName($expr);
            if ($exprName === null) {
                return [];
            }
            $variables = $this->findInstancesOf($scopeNode, [\PhpParser\Node\Expr\Variable::class]);
            return \array_filter($variables, function (\PhpParser\Node\Expr\Variable $variable) use($exprName) : bool {
                return $this->nodeNameResolver->isName($variable, $exprName);
            });
        }
        if ($expr instanceof \PhpParser\Node\Stmt\Property) {
            $singleProperty = $expr->props[0];
            $exprName = $this->nodeNameResolver->getName($singleProperty->name);
        } elseif ($expr instanceof \PhpParser\Node\Expr\StaticPropertyFetch || $expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            $exprName = $this->nodeNameResolver->getName($expr->name);
        } else {
            return [];
        }
        if ($exprName === null) {
            return [];
        }
        $propertyFetches = $this->findInstancesOf($scopeNode, [\PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\StaticPropertyFetch::class]);
        return \array_filter($propertyFetches, function ($propertyFetch) use($exprName) : bool {
            return $this->nodeNameResolver->isName($propertyFetch->name, $exprName);
        });
    }
    /**
     * @template T of Node
     * @param array<class-string<T>>|class-string<T> $types
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function hasInstancesOfInFunctionLikeScoped($functionLike, $types) : bool
    {
        if (\is_string($types)) {
            $types = [$types];
        }
        foreach ($types as $type) {
            $foundNode = $this->findFirstInstanceOf((array) $functionLike->stmts, $type);
            if (!$foundNode instanceof \PhpParser\Node) {
                continue;
            }
            $parentFunctionLike = $this->findParentByTypes($foundNode, [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class]);
            if ($parentFunctionLike === $functionLike) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @template T of Node
     * @param array<class-string<T>>|class-string<T> $types
     * @return T[]
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
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
                $parentFunctionLike = $this->findParentByTypes($node, [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class]);
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
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function findFirstInFunctionLikeScoped($functionLike, callable $filter) : ?\PhpParser\Node
    {
        $foundNode = $this->findFirst((array) $functionLike->stmts, $filter);
        if (!$foundNode instanceof \PhpParser\Node) {
            return null;
        }
        $parentFunctionLike = $this->findParentByTypes($foundNode, [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class]);
        if ($parentFunctionLike !== $functionLike) {
            return null;
        }
        return $foundNode;
    }
    /**
     * @template T of Node
     * @param mixed[]|\PhpParser\Node $nodes
     * @param class-string<T> $type
     */
    private function findInstanceOfName($nodes, string $type, string $name) : ?\PhpParser\Node
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::isAOf($type, \PhpParser\Node::class);
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
     * @return \PhpParser\Node|null
     */
    private function findParentScope(\PhpParser\Node $node)
    {
        return $this->findParentByTypes($node, [\PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Namespace_::class]);
    }
}
