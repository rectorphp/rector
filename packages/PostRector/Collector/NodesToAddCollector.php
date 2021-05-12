<?php

declare(strict_types=1);

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

final class NodesToAddCollector implements NodeCollectorInterface
{
    /**
     * @var Stmt[][]
     */
    private array $nodesToAddAfter = [];

    /**
     * @var Stmt[][]
     */
    private array $nodesToAddBefore = [];

    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private RectorChangeCollector $rectorChangeCollector,
        private BetterStandardPrinter $betterStandardPrinter
    ) {
    }

    public function isActive(): bool
    {
        return $this->nodesToAddAfter !== [] || $this->nodesToAddBefore !== [];
    }

    public function addNodeBeforeNode(Node $addedNode, Node $positionNode): void
    {
        if ($positionNode->getAttributes() === []) {
            $message = sprintf('Switch arguments in "%s()" method', __METHOD__);
            throw new ShouldNotHappenException($message);
        }

        $position = $this->resolveNearestExpressionPosition($positionNode);
        $this->nodesToAddBefore[$position][] = $this->wrapToExpression($addedNode);

        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }

    /**
     * @param Node[] $addedNodes
     */
    public function addNodesAfterNode(array $addedNodes, Node $positionNode): void
    {
        $position = $this->resolveNearestExpressionPosition($positionNode);
        foreach ($addedNodes as $addedNode) {
            // prevent fluent method weird indent
            $addedNode->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $this->nodesToAddAfter[$position][] = $this->wrapToExpression($addedNode);
        }

        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }

    public function addNodeAfterNode(Node $addedNode, Node $positionNode): void
    {
        $position = $this->resolveNearestExpressionPosition($positionNode);
        $this->nodesToAddAfter[$position][] = $this->wrapToExpression($addedNode);

        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }

    /**
     * @return Stmt[]
     */
    public function getNodesToAddAfterNode(Node $node): array
    {
        $position = spl_object_hash($node);
        return $this->nodesToAddAfter[$position] ?? [];
    }

    /**
     * @return Stmt[]
     */
    public function getNodesToAddBeforeNode(Node $node): array
    {
        $position = spl_object_hash($node);
        return $this->nodesToAddBefore[$position] ?? [];
    }

    public function clearNodesToAddAfter(Node $node): void
    {
        $objectHash = spl_object_hash($node);
        unset($this->nodesToAddAfter[$objectHash]);
    }

    public function clearNodesToAddBefore(Node $node): void
    {
        $objectHash = spl_object_hash($node);
        unset($this->nodesToAddBefore[$objectHash]);
    }

    /**
     * @param Node[] $newNodes
     */
    public function addNodesBeforeNode(array $newNodes, Node $positionNode): void
    {
        foreach ($newNodes as $newNode) {
            $this->addNodeBeforeNode($newNode, $positionNode);
        }

        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }

    private function resolveNearestExpressionPosition(Node $node): string
    {
        if ($node instanceof Expression || $node instanceof Stmt) {
            return spl_object_hash($node);
        }

        $currentStmt = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if ($currentStmt instanceof Stmt) {
            return spl_object_hash($currentStmt);
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Return_) {
            return spl_object_hash($parent);
        }

        $foundNode = $this->betterNodeFinder->findParentTypes($node, [Expression::class, Stmt::class]);

        if (! $foundNode instanceof Stmt) {
            $printedNode = $this->betterStandardPrinter->print($node);
            $errorMessage = sprintf('Could not find parent Stmt of "%s" node', $printedNode);
            throw new ShouldNotHappenException($errorMessage);
        }

        return spl_object_hash($foundNode);
    }

    /**
     * @param Expr|Stmt $node
     */
    private function wrapToExpression(Node $node): Stmt
    {
        return $node instanceof Stmt ? $node : new Expression($node);
    }
}
