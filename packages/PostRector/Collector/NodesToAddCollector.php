<?php

declare (strict_types=1);
namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Application\ChangedNodeScopeRefresher;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;
use RectorPrefix20220609\Symplify\SmartFileSystem\SmartFileInfo;
final class NodesToAddCollector implements NodeCollectorInterface
{
    /**
     * @var Stmt[][]
     */
    private $nodesToAddAfter = [];
    /**
     * @var Stmt[][]
     */
    private $nodesToAddBefore = [];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    /**
     * @readonly
     * @var \Rector\Core\Application\ChangedNodeScopeRefresher
     */
    private $changedNodeScopeRefresher;
    public function __construct(BetterNodeFinder $betterNodeFinder, RectorChangeCollector $rectorChangeCollector, NodePrinterInterface $nodePrinter, ChangedNodeScopeRefresher $changedNodeScopeRefresher)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->nodePrinter = $nodePrinter;
        $this->changedNodeScopeRefresher = $changedNodeScopeRefresher;
    }
    public function isActive() : bool
    {
        return $this->nodesToAddAfter !== [] || $this->nodesToAddBefore !== [];
    }
    /**
     * @deprecated Return created nodes right in refactor() method to keep context instead.
     */
    public function addNodeBeforeNode(Node $addedNode, Node $positionNode, ?SmartFileInfo $smartFileInfo = null) : void
    {
        if ($positionNode->getAttributes() === []) {
            $message = \sprintf('Switch arguments in "%s()" method', __METHOD__);
            throw new ShouldNotHappenException($message);
        }
        // @todo the node must be returned here, so traverser can refresh it
        // this is nasty hack to verify it works
        if ($smartFileInfo instanceof SmartFileInfo) {
            $currentScope = $positionNode->getAttribute(AttributeKey::SCOPE);
            $this->changedNodeScopeRefresher->refresh($addedNode, $smartFileInfo, $currentScope);
        }
        $position = $this->resolveNearestStmtPosition($positionNode);
        $this->nodesToAddBefore[$position][] = $this->wrapToExpression($addedNode);
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }
    /**
     * @param Node[] $addedNodes
     * @deprecated Return created nodes right in refactor() method to keep context instead.
     */
    public function addNodesAfterNode(array $addedNodes, Node $positionNode) : void
    {
        $position = $this->resolveNearestStmtPosition($positionNode);
        foreach ($addedNodes as $addedNode) {
            // prevent fluent method weird indent
            $addedNode->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $this->nodesToAddAfter[$position][] = $this->wrapToExpression($addedNode);
        }
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }
    /**
     * Better return created nodes right in refactor() method to keep context
     * @deprecated
     */
    public function addNodeAfterNode(Node $addedNode, Node $positionNode) : void
    {
        $position = $this->resolveNearestStmtPosition($positionNode);
        $this->nodesToAddAfter[$position][] = $this->wrapToExpression($addedNode);
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }
    /**
     * @return Stmt[]
     */
    public function getNodesToAddAfterNode(Node $node) : array
    {
        $position = \spl_object_hash($node);
        return $this->nodesToAddAfter[$position] ?? [];
    }
    /**
     * @return Stmt[]
     */
    public function getNodesToAddBeforeNode(Node $node) : array
    {
        $position = \spl_object_hash($node);
        return $this->nodesToAddBefore[$position] ?? [];
    }
    public function clearNodesToAddAfter(Node $node) : void
    {
        $objectHash = \spl_object_hash($node);
        unset($this->nodesToAddAfter[$objectHash]);
    }
    public function clearNodesToAddBefore(Node $node) : void
    {
        $objectHash = \spl_object_hash($node);
        unset($this->nodesToAddBefore[$objectHash]);
    }
    /**
     * @deprecated Return created nodes right in refactor() method to keep context instead.
     * @param Node[] $newNodes
     */
    public function addNodesBeforeNode(array $newNodes, Node $positionNode) : void
    {
        foreach ($newNodes as $newNode) {
            $this->addNodeBeforeNode($newNode, $positionNode, null);
        }
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }
    private function resolveNearestStmtPosition(Node $node) : string
    {
        if ($node instanceof Stmt) {
            return \spl_object_hash($node);
        }
        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($node);
        if ($currentStmt instanceof Stmt) {
            return \spl_object_hash($currentStmt);
        }
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Return_) {
            return \spl_object_hash($parent);
        }
        $foundNode = $this->betterNodeFinder->findParentType($node, Stmt::class);
        if (!$foundNode instanceof Stmt) {
            $printedNode = $this->nodePrinter->print($node);
            $errorMessage = \sprintf('Could not find parent Stmt of "%s" node', $printedNode);
            throw new ShouldNotHappenException($errorMessage);
        }
        return \spl_object_hash($foundNode);
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Stmt $node
     */
    private function wrapToExpression($node) : Stmt
    {
        return $node instanceof Stmt ? $node : new Expression($node);
    }
}
