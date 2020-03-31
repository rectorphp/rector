<?php

declare(strict_types=1);

namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
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
        return count($this->nodesToAddAfter) > 0 || count($this->nodesToAddBefore) > 0;
    }

    public function addNodeBeforeNode(Node $addedNode, Node $positionNode): void
    {
        $position = $this->resolveNearestExpressionPosition($positionNode);
        $this->nodesToAddBefore[$position][] = $this->wrapToExpression($addedNode);
    }

    public function addNodeAfterNode(Node $addedNode, Node $positionNode): void
    {
        $position = $this->resolveNearestExpressionPosition($positionNode);
        $this->nodesToAddAfter[$position][] = $this->wrapToExpression($addedNode);
    }

    public function getNodesToAddAfterNode(Node $node): array
    {
        $position = spl_object_hash($node);
        return $this->nodesToAddAfter[$position] ?? [];
    }

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
        if ($node instanceof Expression) {
            return spl_object_hash($node);
        }

        // special case for "If_"
        if ($node instanceof If_) {
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
