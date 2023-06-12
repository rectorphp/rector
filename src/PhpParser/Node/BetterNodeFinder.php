<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeFinder;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Exception\StopSearchException;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\Util\MultiInstanceofChecker;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix202306\Webmozart\Assert\Assert;
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
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Util\MultiInstanceofChecker
     */
    private $multiInstanceofChecker;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(NodeFinder $nodeFinder, NodeNameResolver $nodeNameResolver, NodeComparator $nodeComparator, ClassAnalyzer $classAnalyzer, MultiInstanceofChecker $multiInstanceofChecker, CurrentFileProvider $currentFileProvider)
    {
        $this->nodeFinder = $nodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
        $this->classAnalyzer = $classAnalyzer;
        $this->multiInstanceofChecker = $multiInstanceofChecker;
        $this->currentFileProvider = $currentFileProvider;
    }
    /**
     * @deprecated Make use of child nodes instead
     *
     * @template TNode of \PhpParser\Node
     * @param array<class-string<TNode>> $types
     * @return TNode|null
     */
    public function findParentByTypes(Node $node, array $types) : ?Node
    {
        Assert::allIsAOf($types, Node::class);
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        while ($parentNode instanceof Node) {
            foreach ($types as $type) {
                if ($parentNode instanceof $type) {
                    return $parentNode;
                }
            }
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }
        return null;
    }
    /**
     * @deprecated Make use of child nodes instead
     * @param class-string<T> $type
     * @template T of Node
     * @return T|null
     */
    public function findParentType(Node $node, string $type) : ?Node
    {
        Assert::isAOf($type, Node::class);
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        while ($parentNode instanceof Node) {
            if ($parentNode instanceof $type) {
                return $parentNode;
            }
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }
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
     * @param \PhpParser\Node|mixed[] $nodes
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
     * @api symfony
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
     * @deprecated Use nodes directly
     *
     * Search in previous Node/Stmt, when no Node found, lookup previous Stmt of Parent Node
     *
     * @param callable(Node $node): bool $filter
     */
    public function findFirstPrevious(Node $node, callable $filter) : ?Node
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        $newStmts = $this->resolveNewStmts($parentNode);
        $foundNode = $this->findFirstInlinedPrevious($node, $filter, $newStmts, $parentNode);
        // we found what we need
        if ($foundNode instanceof Node) {
            return $foundNode;
        }
        if ($parentNode instanceof FunctionLike) {
            return null;
        }
        if ($parentNode instanceof Node) {
            return $this->findFirstPrevious($parentNode, $filter);
        }
        return null;
    }
    /**
     * @api
     * @template T of Node
     *
     * @param array<class-string<T>> $types
     * @return T|null
     */
    public function findFirstPreviousOfTypes(Node $mainNode, array $types) : ?Node
    {
        return $this->findFirstPrevious($mainNode, function (Node $node) use($types) : bool {
            return $this->multiInstanceofChecker->isInstanceOf($node, $types);
        });
    }
    /**
     * @param callable(Node $node): bool $filter
     */
    public function findFirstNext(Node $node, callable $filter) : ?Node
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        $newStmts = $this->resolveNewStmts($parentNode);
        try {
            $foundNode = $this->findFirstInlinedNext($node, $filter, $newStmts, $parentNode);
        } catch (StopSearchException $exception) {
            return null;
        }
        // we found what we need
        if ($foundNode instanceof Node) {
            return $foundNode;
        }
        if ($parentNode instanceof Return_ || $parentNode instanceof FunctionLike) {
            return null;
        }
        if ($parentNode instanceof Node) {
            return $this->findFirstNext($parentNode, $filter);
        }
        return null;
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
        while (($currentStmt = $currentStmt->getAttribute(AttributeKey::PARENT_NODE)) instanceof Node) {
            if ($currentStmt instanceof Stmt) {
                return $currentStmt;
            }
            /** @var Node|null $currentStmt */
            if (!$currentStmt instanceof Node) {
                return null;
            }
        }
        return null;
    }
    /**
     * @api
     *
     * Resolve next node from any Node, eg: Expr, Identifier, Name, etc
     */
    public function resolveNextNode(Node $node) : ?Node
    {
        $currentStmt = $this->resolveCurrentStatement($node);
        if (!$currentStmt instanceof Stmt) {
            return null;
        }
        $endTokenPos = $node->getEndTokenPos();
        $nextNode = $endTokenPos < 0 || $currentStmt->getEndTokenPos() === $endTokenPos ? null : $this->findFirst($currentStmt, static function (Node $subNode) use($endTokenPos) : bool {
            return $subNode->getStartTokenPos() > $endTokenPos;
        });
        if (!$nextNode instanceof Node) {
            $parentNode = $currentStmt->getAttribute(AttributeKey::PARENT_NODE);
            if (!$this->isAllowedParentNode($parentNode)) {
                return null;
            }
            $currentStmtKey = $currentStmt->getAttribute(AttributeKey::STMT_KEY);
            /** @var StmtsAwareInterface|ClassLike|Declare_ $parentNode */
            return $parentNode->stmts[$currentStmtKey + 1] ?? null;
        }
        return $nextNode;
    }
    /**
     * @api
     *
     * Resolve previous node from any Node, eg: Expr, Identifier, Name, etc
     */
    private function resolvePreviousNode(Node $node) : ?Node
    {
        $currentStmt = $this->resolveCurrentStatement($node);
        if (!$currentStmt instanceof Stmt) {
            return null;
        }
        $startTokenPos = $node->getStartTokenPos();
        $nodes = $startTokenPos < 0 || $currentStmt->getStartTokenPos() === $startTokenPos ? [] : $this->find($currentStmt, static function (Node $subNode) use($startTokenPos) : bool {
            return $subNode->getEndTokenPos() < $startTokenPos;
        });
        if ($nodes === []) {
            $parentNode = $currentStmt->getAttribute(AttributeKey::PARENT_NODE);
            if (!$this->isAllowedParentNode($parentNode)) {
                return null;
            }
            $currentStmtKey = $currentStmt->getAttribute(AttributeKey::STMT_KEY);
            /** @var StmtsAwareInterface|ClassLike|Declare_ $parentNode */
            return $parentNode->stmts[$currentStmtKey - 1] ?? null;
        }
        return \end($nodes);
    }
    private function isAllowedParentNode(?Node $node) : bool
    {
        return $node instanceof StmtsAwareInterface || $node instanceof ClassLike || $node instanceof Declare_;
    }
    /**
     * Only search in next Node/Stmt
     *
     * @param Stmt[] $newStmts
     * @param callable(Node $node): bool $filter
     */
    private function findFirstInlinedNext(Node $node, callable $filter, array $newStmts, ?Node $parentNode) : ?Node
    {
        if (!$parentNode instanceof Node) {
            $nextNode = $this->resolveNextNodeFromFile($newStmts, $node);
        } elseif ($node instanceof Stmt) {
            if (!$this->isAllowedParentNode($parentNode)) {
                return null;
            }
            $currentStmtKey = $node->getAttribute(AttributeKey::STMT_KEY);
            /** @var StmtsAwareInterface|ClassLike|Declare_ $parentNode */
            $nextNode = $parentNode->stmts[$currentStmtKey + 1] ?? null;
        } else {
            $nextNode = $this->resolveNextNode($node);
        }
        if (!$nextNode instanceof Node) {
            return null;
        }
        if ($nextNode instanceof Return_ && !$nextNode->expr instanceof Expr && !$parentNode instanceof Case_) {
            throw new StopSearchException();
        }
        $found = $this->findFirst($nextNode, $filter);
        if ($found instanceof Node) {
            return $found;
        }
        return $this->findFirstInlinedNext($nextNode, $filter, $newStmts, $parentNode);
    }
    /**
     * @return Stmt[]
     */
    private function resolveNewStmts(?Node $parentNode) : array
    {
        if (!$parentNode instanceof Node) {
            // on __construct(), $file not yet a File object
            $file = $this->currentFileProvider->getFile();
            return $file instanceof File ? $file->getNewStmts() : [];
        }
        return [];
    }
    /**
     * @param callable(Node $node): bool $filter
     */
    private function findFirstInTopLevelStmtsAware(StmtsAwareInterface $stmtsAware, callable $filter) : ?Node
    {
        $nodes = [];
        if ($stmtsAware instanceof Foreach_) {
            $nodes = [$stmtsAware->valueVar, $stmtsAware->keyVar, $stmtsAware->expr];
        }
        if ($stmtsAware instanceof For_) {
            $nodes = [$stmtsAware->loop, $stmtsAware->cond, $stmtsAware->init];
        }
        if ($this->multiInstanceofChecker->isInstanceOf($stmtsAware, [If_::class, While_::class, Do_::class, Switch_::class, ElseIf_::class, Case_::class])) {
            /** @var If_|While_|Do_|Switch_|ElseIf_|Case_ $stmtsAware */
            $nodes = [$stmtsAware->cond];
        }
        foreach ($nodes as $node) {
            if (!$node instanceof Node) {
                continue;
            }
            $foundNode = $this->findFirst($node, $filter);
            if ($foundNode instanceof Node) {
                return $foundNode;
            }
        }
        return null;
    }
    /**
     * @param Stmt[] $newStmts
     */
    private function resolvePreviousNodeFromFile(array $newStmts, Node $node) : ?Node
    {
        if (!$node instanceof Namespace_ && !$node instanceof FileWithoutNamespace) {
            return null;
        }
        $currentStmtKey = $node->getAttribute(AttributeKey::STMT_KEY);
        $stmtKey = $currentStmtKey - 1;
        if ($node instanceof FileWithoutNamespace) {
            $stmtKey = $stmtKey === -1 ? 0 : $stmtKey;
        }
        return $newStmts[$stmtKey] ?? null;
    }
    /**
     * @param Stmt[] $newStmts
     */
    private function resolveNextNodeFromFile(array $newStmts, Node $node) : ?Node
    {
        if (!$node instanceof Namespace_ && !$node instanceof FileWithoutNamespace) {
            return null;
        }
        $currentStmtKey = $node->getAttribute(AttributeKey::STMT_KEY);
        return $newStmts[$currentStmtKey + 1] ?? null;
    }
    /**
     * Only search in previous Node/Stmt
     *
     * @param Stmt[] $newStmts
     * @param callable(Node $node): bool $filter
     */
    private function findFirstInlinedPrevious(Node $node, callable $filter, array $newStmts, ?Node $parentNode) : ?Node
    {
        if (!$parentNode instanceof Node) {
            $previousNode = $this->resolvePreviousNodeFromFile($newStmts, $node);
        } elseif ($node instanceof Stmt) {
            if (!$this->isAllowedParentNode($parentNode)) {
                return null;
            }
            $currentStmtKey = $node->getAttribute(AttributeKey::STMT_KEY);
            if ($parentNode instanceof StmtsAwareInterface && !isset($parentNode->stmts[$currentStmtKey - 1])) {
                return $this->findFirstInTopLevelStmtsAware($parentNode, $filter);
            }
            /** @var StmtsAwareInterface|ClassLike|Declare_ $parentNode */
            $previousNode = $parentNode->stmts[$currentStmtKey - 1] ?? null;
        } else {
            $previousNode = $this->resolvePreviousNode($node);
        }
        if (!$previousNode instanceof Node) {
            return null;
        }
        $foundNode = $this->findFirst($previousNode, $filter);
        // we found what we need
        if ($foundNode instanceof Node) {
            return $foundNode;
        }
        return $this->findFirstInlinedPrevious($previousNode, $filter, $newStmts, $parentNode);
    }
    /**
     * @template T of Node
     * @param \PhpParser\Node|mixed[] $nodes
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
