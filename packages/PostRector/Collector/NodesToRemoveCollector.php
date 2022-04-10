<?php

declare (strict_types=1);
namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\ChangesReporting\Collector\AffectedFilesCollector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\NodeCollector\NodeResolver\CurrentStmtResolver;
use Rector\NodeRemoval\BreakingRemovalGuard;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
final class NodesToRemoveCollector implements \Rector\PostRector\Contract\Collector\NodeCollectorInterface
{
    /**
     * @var Stmt[]|Node[]
     */
    private $nodesToRemove = [];
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Collector\AffectedFilesCollector
     */
    private $affectedFilesCollector;
    /**
     * @readonly
     * @var \Rector\NodeRemoval\BreakingRemovalGuard
     */
    private $breakingRemovalGuard;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \Rector\NodeCollector\NodeResolver\CurrentStmtResolver
     */
    private $currentStmtResolver;
    public function __construct(\Rector\ChangesReporting\Collector\AffectedFilesCollector $affectedFilesCollector, \Rector\NodeRemoval\BreakingRemovalGuard $breakingRemovalGuard, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\NodeCollector\NodeResolver\CurrentStmtResolver $currentStmtResolver)
    {
        $this->affectedFilesCollector = $affectedFilesCollector;
        $this->breakingRemovalGuard = $breakingRemovalGuard;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->currentFileProvider = $currentFileProvider;
        $this->currentStmtResolver = $currentStmtResolver;
    }
    public function addNodeToRemove(\PhpParser\Node $node) : void
    {
        /** Node|null $parentNode */
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode !== null && $this->isUsedInArg($node, $parentNode)) {
            return;
        }
        // chain call: "->method()->another()"
        $this->ensureIsNotPartOfChainMethodCall($node);
        if (!$node instanceof \PhpParser\Node\Stmt\Expression && $parentNode instanceof \PhpParser\Node\Stmt\Expression) {
            // only expressions can be removed
            $node = $parentNode;
        } else {
            $this->breakingRemovalGuard->ensureNodeCanBeRemove($node);
        }
        $file = $this->currentFileProvider->getFile();
        // /** @var SmartFileInfo|null $fileInfo */
        if ($file !== null) {
            $this->affectedFilesCollector->addFile($file);
        }
        /** @var Stmt $node */
        $this->nodesToRemove[] = $node;
    }
    public function isNodeRemoved(\PhpParser\Node $node) : bool
    {
        if (\in_array($node, $this->nodesToRemove, \true)) {
            return \true;
        }
        if ($node instanceof \PhpParser\Node\Stmt) {
            $currentStatement = $this->currentStmtResolver->resolve($node);
            return \in_array($currentStatement, $this->nodesToRemove, \true);
        }
        return \false;
    }
    public function isActive() : bool
    {
        return $this->getCount() > 0;
    }
    public function getCount() : int
    {
        return \count($this->nodesToRemove);
    }
    /**
     * @return Node[]
     */
    public function getNodesToRemove() : array
    {
        return $this->nodesToRemove;
    }
    public function unset(int $key) : void
    {
        unset($this->nodesToRemove[$key]);
    }
    private function isUsedInArg(\PhpParser\Node $node, \PhpParser\Node $parentNode) : bool
    {
        if (!$node instanceof \PhpParser\Node\Param) {
            return \false;
        }
        if (!$parentNode instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        $paramVariable = $node->var;
        if ($paramVariable instanceof \PhpParser\Node\Expr\Variable) {
            return (bool) $this->betterNodeFinder->findFirst((array) $parentNode->stmts, function (\PhpParser\Node $node) use($paramVariable) : bool {
                if (!$this->nodeComparator->areNodesEqual($node, $paramVariable)) {
                    return \false;
                }
                $hasArgParent = (bool) $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Arg::class);
                if (!$hasArgParent) {
                    return \false;
                }
                return !(bool) $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Expr\StaticCall::class);
            });
        }
        return \false;
    }
    private function ensureIsNotPartOfChainMethodCall(\PhpParser\Node $node) : void
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return;
        }
        throw new \Rector\Core\Exception\ShouldNotHappenException('Chain method calls cannot be removed this way. It would remove the whole tree of calls. Remove them manually by creating new parent node with no following method.');
    }
}
