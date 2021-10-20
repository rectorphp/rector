<?php

declare (strict_types=1);
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
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20211020\Symplify\PackageBuilder\Php\TypeChecker;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Core\Tests\PhpParser\Node\BetterNodeFinder\BetterNodeFinderTest
 */
final class BetterNodeFinder
{
    /**
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Symplify\PackageBuilder\Php\TypeChecker
     */
    private $typeChecker;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @var \Rector\NodeNestingScope\ParentScopeFinder
     */
    private $parentScopeFinder;
    public function __construct(\PhpParser\NodeFinder $nodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20211020\Symplify\PackageBuilder\Php\TypeChecker $typeChecker, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer, \Rector\NodeNestingScope\ParentScopeFinder $parentScopeFinder)
    {
        $this->nodeFinder = $nodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->typeChecker = $typeChecker;
        $this->nodeComparator = $nodeComparator;
        $this->classAnalyzer = $classAnalyzer;
        $this->parentScopeFinder = $parentScopeFinder;
    }
    /**
     * @template T of Node
     * @param class-string<T> $type
     * @return T|null
     */
    public function findParentType(\PhpParser\Node $node, string $type) : ?\PhpParser\Node
    {
        \RectorPrefix20211020\Webmozart\Assert\Assert::isAOf($type, \PhpParser\Node::class);
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
     * @param Node|Node[]|Stmt[] $nodes
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
     * @param Node|Node[]|Stmt[] $nodes
     * @return T[]
     */
    public function findInstanceOf($nodes, string $type) : array
    {
        return $this->nodeFinder->findInstanceOf($nodes, $type);
    }
    /**
     * @template T of Node
     * @param class-string<T> $type
     * @param Node|Node[] $nodes
     */
    public function findFirstInstanceOf($nodes, string $type) : ?\PhpParser\Node
    {
        \RectorPrefix20211020\Webmozart\Assert\Assert::isAOf($type, \PhpParser\Node::class);
        return $this->nodeFinder->findFirstInstanceOf($nodes, $type);
    }
    /**
     * @param class-string<Node> $type
     * @param Node|Node[] $nodes
     */
    public function hasInstanceOfName($nodes, string $type, string $name) : bool
    {
        \RectorPrefix20211020\Webmozart\Assert\Assert::isAOf($type, \PhpParser\Node::class);
        return (bool) $this->findInstanceOfName($nodes, $type, $name);
    }
    /**
     * @param Node|Node[] $nodes
     */
    public function hasVariableOfName($nodes, string $name) : bool
    {
        return (bool) $this->findVariableOfName($nodes, $name);
    }
    /**
     * @param Node|Node[] $nodes
     * @return Variable|null
     */
    public function findVariableOfName($nodes, string $name) : ?\PhpParser\Node
    {
        return $this->findInstanceOfName($nodes, \PhpParser\Node\Expr\Variable::class, $name);
    }
    /**
     * @param Node|Node[] $nodes
     * @param array<class-string<Node>> $types
     */
    public function hasInstancesOf($nodes, array $types) : bool
    {
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsAOf($types, \PhpParser\Node::class);
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
     * @param Node|Node[] $nodes
     */
    public function findLastInstanceOf($nodes, string $type) : ?\PhpParser\Node
    {
        \RectorPrefix20211020\Webmozart\Assert\Assert::isAOf($type, \PhpParser\Node::class);
        $foundInstances = $this->nodeFinder->findInstanceOf($nodes, $type);
        if ($foundInstances === []) {
            return null;
        }
        \end($foundInstances);
        $lastItemKey = \key($foundInstances);
        return $foundInstances[$lastItemKey];
    }
    /**
     * @param Node|Node[] $nodes
     * @return Node[]
     */
    public function find($nodes, callable $filter) : array
    {
        return $this->nodeFinder->find($nodes, $filter);
    }
    /**
     * Excludes anonymous classes!
     *
     * @param Node[]|Node $nodes
     * @return ClassLike[]
     */
    public function findClassLikes($nodes) : array
    {
        return $this->find($nodes, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Stmt\ClassLike) {
                return \false;
            }
            // skip anonymous classes
            return !($node instanceof \PhpParser\Node\Stmt\Class_ && $this->classAnalyzer->isAnonymousClass($node));
        });
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
     * @param Node|Node[] $nodes
     */
    public function findFirst($nodes, callable $filter) : ?\PhpParser\Node
    {
        return $this->nodeFinder->findFirst($nodes, $filter);
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
    public function findFirstPreviousOfNode(\PhpParser\Node $node, callable $filter) : ?\PhpParser\Node
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
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\FunctionLike) {
            return null;
        }
        if ($parent instanceof \PhpParser\Node) {
            return $this->findFirstPreviousOfNode($parent, $filter);
        }
        return null;
    }
    public function findFirstPrevious(\PhpParser\Node $node, callable $filter) : ?\PhpParser\Node
    {
        $node = $node instanceof \PhpParser\Node\Stmt\Expression ? $node : $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if ($node === null) {
            return null;
        }
        $foundNode = $this->findFirst([$node], $filter);
        // we found what we need
        if ($foundNode !== null) {
            return $foundNode;
        }
        // move to previous expression
        $previousStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT);
        if ($previousStatement !== null) {
            return $this->findFirstPrevious($previousStatement, $filter);
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent === null) {
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
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $expr
     */
    public function findSameNamedExprs($expr) : array
    {
        // assign of empty string to something
        $scopeNode = $this->parentScopeFinder->find($expr);
        if ($scopeNode === null) {
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
     * @param Node|Node[] $nodes
     * @param class-string<T> $type
     */
    private function findInstanceOfName($nodes, string $type, string $name) : ?\PhpParser\Node
    {
        \RectorPrefix20211020\Webmozart\Assert\Assert::isAOf($type, \PhpParser\Node::class);
        $foundInstances = $this->nodeFinder->findInstanceOf($nodes, $type);
        foreach ($foundInstances as $foundInstance) {
            if (!$this->nodeNameResolver->isName($foundInstance, $name)) {
                continue;
            }
            return $foundInstance;
        }
        return null;
    }
}
