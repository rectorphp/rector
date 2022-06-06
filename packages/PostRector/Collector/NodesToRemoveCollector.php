<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PostRector\Collector;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\Rector\ChangesReporting\Collector\AffectedFilesCollector;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\PhpParser\Comparing\NodeComparator;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\Core\Provider\CurrentFileProvider;
use RectorPrefix20220606\Rector\NodeRemoval\BreakingRemovalGuard;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\PostRector\Contract\Collector\NodeCollectorInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
final class NodesToRemoveCollector implements NodeCollectorInterface
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
    public function __construct(AffectedFilesCollector $affectedFilesCollector, BreakingRemovalGuard $breakingRemovalGuard, BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, CurrentFileProvider $currentFileProvider)
    {
        $this->affectedFilesCollector = $affectedFilesCollector;
        $this->breakingRemovalGuard = $breakingRemovalGuard;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->currentFileProvider = $currentFileProvider;
    }
    public function addNodeToRemove(Node $node) : void
    {
        /** Node|null $parentNode */
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode !== null && $this->isUsedInArg($node, $parentNode)) {
            return;
        }
        // chain call: "->method()->another()"
        $this->ensureIsNotPartOfChainMethodCall($node);
        if (!$node instanceof Expression && $parentNode instanceof Expression) {
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
    public function isNodeRemoved(Node $node) : bool
    {
        return \in_array($node, $this->nodesToRemove, \true);
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
    private function isUsedInArg(Node $node, Node $parentNode) : bool
    {
        if (!$node instanceof Param) {
            return \false;
        }
        if (!$parentNode instanceof ClassMethod) {
            return \false;
        }
        $paramVariable = $node->var;
        if ($paramVariable instanceof Variable) {
            return (bool) $this->betterNodeFinder->findFirst((array) $parentNode->stmts, function (Node $node) use($paramVariable) : bool {
                if (!$this->nodeComparator->areNodesEqual($node, $paramVariable)) {
                    return \false;
                }
                $hasArgParent = (bool) $this->betterNodeFinder->findParentType($node, Arg::class);
                if (!$hasArgParent) {
                    return \false;
                }
                return !(bool) $this->betterNodeFinder->findParentType($node, StaticCall::class);
            });
        }
        return \false;
    }
    private function ensureIsNotPartOfChainMethodCall(Node $node) : void
    {
        if (!$node instanceof MethodCall) {
            return;
        }
        if (!$node->var instanceof MethodCall) {
            return;
        }
        throw new ShouldNotHappenException('Chain method calls cannot be removed this way. It would remove the whole tree of calls. Remove them manually by creating new parent node with no following method.');
    }
}
