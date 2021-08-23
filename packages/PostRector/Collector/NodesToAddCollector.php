<?php

declare (strict_types=1);
namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;
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
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    /**
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector, \Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    public function isActive() : bool
    {
        return $this->nodesToAddAfter !== [] || $this->nodesToAddBefore !== [];
    }
    /**
     * @param \PhpParser\Node $addedNode
     * @param \PhpParser\Node $positionNode
     */
    public function addNodeBeforeNode($addedNode, $positionNode) : void
    {
        if ($positionNode->getAttributes() === []) {
            $message = \sprintf('Switch arguments in "%s()" method', __METHOD__);
            throw new \Rector\Core\Exception\ShouldNotHappenException($message);
        }
        $position = $this->resolveNearestStmtPosition($positionNode);
        $this->nodesToAddBefore[$position][] = $this->wrapToExpression($addedNode);
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }
    /**
     * @param Node[] $addedNodes
     * @param \PhpParser\Node $positionNode
     */
    public function addNodesAfterNode($addedNodes, $positionNode) : void
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
     * @param \PhpParser\Node $addedNode
     * @param \PhpParser\Node $positionNode
     */
    public function addNodeAfterNode($addedNode, $positionNode) : void
    {
        $position = $this->resolveNearestStmtPosition($positionNode);
        $this->nodesToAddAfter[$position][] = $this->wrapToExpression($addedNode);
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }
    /**
     * @return Stmt[]
     * @param \PhpParser\Node $node
     */
    public function getNodesToAddAfterNode($node) : array
    {
        $position = \spl_object_hash($node);
        return $this->nodesToAddAfter[$position] ?? [];
    }
    /**
     * @return Stmt[]
     * @param \PhpParser\Node $node
     */
    public function getNodesToAddBeforeNode($node) : array
    {
        $position = \spl_object_hash($node);
        return $this->nodesToAddBefore[$position] ?? [];
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function clearNodesToAddAfter($node) : void
    {
        $objectHash = \spl_object_hash($node);
        unset($this->nodesToAddAfter[$objectHash]);
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function clearNodesToAddBefore($node) : void
    {
        $objectHash = \spl_object_hash($node);
        unset($this->nodesToAddBefore[$objectHash]);
    }
    /**
     * @param Node[] $newNodes
     * @param \PhpParser\Node $positionNode
     */
    public function addNodesBeforeNode($newNodes, $positionNode) : void
    {
        foreach ($newNodes as $newNode) {
            $this->addNodeBeforeNode($newNode, $positionNode);
        }
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }
    private function resolveNearestStmtPosition(\PhpParser\Node $node) : string
    {
        if ($node instanceof \PhpParser\Node\Stmt) {
            return \spl_object_hash($node);
        }
        $currentStmt = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if ($currentStmt instanceof \PhpParser\Node\Stmt) {
            return \spl_object_hash($currentStmt);
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Stmt\Return_) {
            return \spl_object_hash($parent);
        }
        $foundNode = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt::class);
        if (!$foundNode instanceof \PhpParser\Node\Stmt) {
            $printedNode = $this->betterStandardPrinter->print($node);
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
