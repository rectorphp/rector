<?php

declare (strict_types=1);
namespace Rector\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix202407\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\PhpParser\Node\BetterNodeFinder\BetterNodeFinderTest
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
     * @var \Rector\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(NodeFinder $nodeFinder, NodeNameResolver $nodeNameResolver, ClassAnalyzer $classAnalyzer, SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->nodeFinder = $nodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classAnalyzer = $classAnalyzer;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
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
     *
     * @return T|null
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
     * @param Node|Node[] $nodes
     * @return Variable|null
     */
    public function findVariableOfName($nodes, string $name) : ?Node
    {
        return $this->findInstanceOfName($nodes, Variable::class, $name);
    }
    /**
     * @param Node|Node[] $nodes
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
     * @param Node|Node[] $nodes
     * @param callable(Node $node): bool $filter
     * @return Node[]
     */
    public function find($nodes, callable $filter) : array
    {
        return $this->nodeFinder->find($nodes, $filter);
    }
    /**
     * @api symfony
     * @param Node[] $nodes
     * @return Class_|null
     */
    public function findFirstNonAnonymousClass(array $nodes) : ?Node
    {
        // skip anonymous classes
        return $this->findFirst($nodes, function (Node $node) : bool {
            return $node instanceof Class_ && !$this->classAnalyzer->isAnonymousClass($node);
        });
    }
    /**
     * @param Node|Node[] $nodes
     * @param callable(Node $filter): bool $filter
     */
    public function findFirst($nodes, callable $filter) : ?Node
    {
        return $this->nodeFinder->findFirst($nodes, $filter);
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
        $isFoundNode = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->stmts, static function (Node $subNode) use($types, &$isFoundNode) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            foreach ($types as $type) {
                if ($subNode instanceof $type) {
                    $isFoundNode = \true;
                    return NodeTraverser::STOP_TRAVERSAL;
                }
            }
            return null;
        });
        return $isFoundNode;
    }
    /**
     * @return Return_[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function findReturnsScoped($functionLike) : array
    {
        $returns = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->stmts, function (Node $subNode) use(&$returns) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($subNode instanceof Yield_ || $subNode instanceof YieldFrom) {
                $returns = [];
                return NodeTraverser::STOP_TRAVERSAL;
            }
            if ($subNode instanceof Return_) {
                $returns[] = $subNode;
            }
            return null;
        });
        Assert::allIsInstanceOf($returns, Return_::class);
        return $returns;
    }
    /**
     * @template T of Node
     * @param array<class-string<T>>|class-string<T> $types
     * @return array<T>
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function findInstancesOfInFunctionLikeScoped($functionLike, $types) : array
    {
        if (\is_string($types)) {
            $types = [$types];
        }
        /** @var T[] $foundNodes */
        $foundNodes = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->stmts, static function (Node $subNode) use($types, &$foundNodes) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            foreach ($types as $type) {
                if ($subNode instanceof $type) {
                    $foundNodes[] = $subNode;
                    return null;
                }
            }
            return null;
        });
        return $foundNodes;
    }
    /**
     * @param callable(Node $node): bool $filter
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function findFirstInFunctionLikeScoped($functionLike, callable $filter) : ?Node
    {
        if ($functionLike->stmts === null) {
            return null;
        }
        $foundNode = $this->findFirst($functionLike->stmts, $filter);
        if (!$foundNode instanceof Node) {
            return null;
        }
        if (!$this->hasInstancesOf($functionLike->stmts, [Class_::class, Function_::class, Closure::class])) {
            return $foundNode;
        }
        $scopedNode = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($functionLike->stmts, function (Node $subNode) use(&$scopedNode, $foundNode, $filter) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                if ($foundNode instanceof $subNode && $subNode === $foundNode) {
                    $scopedNode = $subNode;
                    return NodeTraverser::STOP_TRAVERSAL;
                }
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$foundNode instanceof $subNode) {
                return null;
            }
            // handle after Closure
            // @see https://github.com/rectorphp/rector-src/pull/4931
            $scopedFoundNode = $this->findFirst($subNode, $filter);
            if ($scopedFoundNode === $subNode) {
                $scopedNode = $subNode;
                return NodeTraverser::STOP_TRAVERSAL;
            }
            return null;
        });
        return $scopedNode;
    }
    /**
     * @template T of Node
     * @param Node|Node[] $nodes
     * @param class-string<T> $type
     */
    private function findInstanceOfName($nodes, string $type, string $name) : ?Node
    {
        Assert::isAOf($type, Node::class);
        return $this->nodeFinder->findFirst($nodes, function (Node $node) use($type, $name) : bool {
            return $node instanceof $type && $this->nodeNameResolver->isName($node, $name);
        });
    }
}
