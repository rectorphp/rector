<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Commander;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\Contract\PhpParser\Node\CommanderInterface;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

/**
 * This class collects all to-be-added expresssions (= 1 line in code)
 * and then adds new expressions to list of $nodes
 *
 * From:
 * - $this->someCall();
 *
 * To:
 * - $this->someCall();
 * - $value = this->someNewCall(); // added expression
 */
final class NodeAddingCommander implements CommanderInterface
{
    /**
     * @var Stmt[][]
     */
    private $nodesToAdd = [];

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

    public function addNodeBeforeNode(Node $addedNode, Node $positionNode): void
    {
        $position = $this->resolveNearestExpressionPosition($positionNode);

        $this->nodesToAddBefore[$position][] = $this->wrapToExpression($addedNode);
    }

    public function addNodeAfterNode(Node $addedNode, Node $positionNode): void
    {
        $position = $this->resolveNearestExpressionPosition($positionNode);

        $this->nodesToAdd[$position][] = $this->wrapToExpression($addedNode);
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverseNodes(array $nodes): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->createNodeVisitor());

        // new nodes to remove are always per traverse
        $this->reset();

        return $nodeTraverser->traverse($nodes);
    }

    public function isActive(): bool
    {
        return count($this->nodesToAdd) > 0 || count($this->nodesToAddBefore) > 0;
    }

    public function getPriority(): int
    {
        return 1000;
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

    private function createNodeVisitor(): NodeVisitor
    {
        return new class($this->nodesToAdd, $this->nodesToAddBefore) extends NodeVisitorAbstract {
            /**
             * @var Stmt[][]
             */
            private $nodesToAdd = [];

            /**
             * @var Stmt[][]
             */
            private $nodesToAddBefore = [];

            /**
             * @param Stmt[][] $nodesToAdd
             * @param Stmt[][] $nodesToAddBefore
             */
            public function __construct(array $nodesToAdd, array $nodesToAddBefore)
            {
                $this->nodesToAdd = $nodesToAdd;
                $this->nodesToAddBefore = $nodesToAddBefore;
            }

            /**
             * @return Node[]|Node|null
             */
            public function leaveNode(Node $node)
            {
                $position = spl_object_hash($node);

                // add node after
                if (isset($this->nodesToAdd[$position])) {
                    $nodes = array_merge([$node], $this->nodesToAdd[$position]);
                    unset($this->nodesToAdd[$position]);
                    return $nodes;
                }

                // add node before
                if (isset($this->nodesToAddBefore[$position])) {
                    $nodes = array_merge($this->nodesToAddBefore[$position], [$node]);

                    unset($this->nodesToAddBefore[$position]);

                    return $nodes;
                }

                return null;
            }
        };
    }

    private function reset(): void
    {
        $this->nodesToAdd = [];
        $this->nodesToAddBefore = [];
    }
}
