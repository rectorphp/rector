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
use Symplify\SmartFileSystem\SmartFileInfo;
final class NodesToAddCollector implements \Rector\PostRector\Contract\Collector\NodeCollectorInterface
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
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector, \Rector\Core\Contract\PhpParser\NodePrinterInterface $nodePrinter, \Rector\Core\Application\ChangedNodeScopeRefresher $changedNodeScopeRefresher)
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
    public function addNodeBeforeNode(\PhpParser\Node $addedNode, \PhpParser\Node $positionNode, ?\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo = null) : void
    {
        if ($positionNode->getAttributes() === []) {
            $message = \sprintf('Switch arguments in "%s()" method', __METHOD__);
            throw new \Rector\Core\Exception\ShouldNotHappenException($message);
        }
        // @todo the node must be returned here, so traverser can refresh it
        // this is nasty hack to verify it works
        if ($smartFileInfo instanceof \Symplify\SmartFileSystem\SmartFileInfo) {
            $currentScope = $positionNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
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
    public function addNodesAfterNode(array $addedNodes, \PhpParser\Node $positionNode) : void
    {
        $position = $this->resolveNearestStmtPosition($positionNode);
        foreach ($addedNodes as $addedNode) {
            // prevent fluent method weird indent
            $addedNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
            $this->nodesToAddAfter[$position][] = $this->wrapToExpression($addedNode);
        }
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }
    /**
     * Better return created nodes right in refactor() method to keep context
     * @deprecated
     */
    public function addNodeAfterNode(\PhpParser\Node $addedNode, \PhpParser\Node $positionNode) : void
    {
        $position = $this->resolveNearestStmtPosition($positionNode);
        $this->nodesToAddAfter[$position][] = $this->wrapToExpression($addedNode);
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }
    /**
     * @return Stmt[]
     */
    public function getNodesToAddAfterNode(\PhpParser\Node $node) : array
    {
        $position = \spl_object_hash($node);
        return $this->nodesToAddAfter[$position] ?? [];
    }
    /**
     * @return Stmt[]
     */
    public function getNodesToAddBeforeNode(\PhpParser\Node $node) : array
    {
        $position = \spl_object_hash($node);
        return $this->nodesToAddBefore[$position] ?? [];
    }
    public function clearNodesToAddAfter(\PhpParser\Node $node) : void
    {
        $objectHash = \spl_object_hash($node);
        unset($this->nodesToAddAfter[$objectHash]);
    }
    public function clearNodesToAddBefore(\PhpParser\Node $node) : void
    {
        $objectHash = \spl_object_hash($node);
        unset($this->nodesToAddBefore[$objectHash]);
    }
    /**
     * @deprecated Return created nodes right in refactor() method to keep context instead.
     * @param Node[] $newNodes
     */
    public function addNodesBeforeNode(array $newNodes, \PhpParser\Node $positionNode) : void
    {
        foreach ($newNodes as $newNode) {
            $this->addNodeBeforeNode($newNode, $positionNode, null);
        }
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }
    private function resolveNearestStmtPosition(\PhpParser\Node $node) : string
    {
        if ($node instanceof \PhpParser\Node\Stmt) {
            return \spl_object_hash($node);
        }
        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($node);
        if ($currentStmt instanceof \PhpParser\Node\Stmt) {
            return \spl_object_hash($currentStmt);
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Stmt\Return_) {
            return \spl_object_hash($parent);
        }
        $foundNode = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt::class);
        if (!$foundNode instanceof \PhpParser\Node\Stmt) {
            $printedNode = $this->nodePrinter->print($node);
            $errorMessage = \sprintf('Could not find parent Stmt of "%s" node', $printedNode);
            throw new \Rector\Core\Exception\ShouldNotHappenException($errorMessage);
        }
        return \spl_object_hash($foundNode);
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Stmt $node
     */
    private function wrapToExpression($node) : \PhpParser\Node\Stmt
    {
        return $node instanceof \PhpParser\Node\Stmt ? $node : new \PhpParser\Node\Stmt\Expression($node);
    }
}
