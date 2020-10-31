<?php

declare(strict_types=1);

namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;

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
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
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
    }

    public function addNodeAfterNode(Node $addedNode, Node $positionNode): void
    {
        $position = $this->resolveNearestExpressionPosition($positionNode);
        $this->nodesToAddAfter[$position][] = $this->wrapToExpression($addedNode);
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

    private function resolveNearestExpressionPosition(Node $node): string
    {
        if ($node instanceof Expression || $node instanceof Stmt) {
            return spl_object_hash($node);
        }

        /** @var Expression|null $foundNode */
        $foundNode = $this->betterNodeFinder->findFirstAncestorInstanceOf($node, Expression::class);
        if ($foundNode === null) {
            $foundNode = $node;
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
