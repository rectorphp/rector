<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Commander;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Exception\ShouldNotHappenException;
use Rector\Utils\BetterNodeFinder;

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
final class NodeAddingCommander
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var Stmt[][]
     */
    private $nodesToAdd = [];

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function addNodeAfterNode(Expr $node, Node $positionNode): void
    {
        $position = $this->resolveNearestExpressionPosition($positionNode);

        $this->nodesToAdd[$position][] = $this->wrapToExpression($node);
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverseNodes(array $nodes): array
    {
        if ($this->nodesToAdd === []) {
            return $nodes;
        }

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new class($this->nodesToAdd) extends NodeVisitorAbstract {
            /**
             * @var Stmt[][]
             */
            private $nodesToAdd = [];

            /**
             * @param Stmt[][] $nodesToAdd
             */
            public function __construct(array $nodesToAdd)
            {
                $this->nodesToAdd = $nodesToAdd;
            }

            /**
             * @return Node[]|Node|null
             */
            public function leaveNode(Node $node)
            {
                $position = spl_object_hash($node);

                if (! isset($this->nodesToAdd[$position])) {
                    return null;
                }

                $nodes = array_merge([$node], $this->nodesToAdd[$position]);

                unset($this->nodesToAdd[$position]);

                return $nodes;
            }
        });

        // new nodes to remove are always per traverse
        $this->nodesToAdd = [];

        return $nodeTraverser->traverse($nodes);
    }

    private function resolveNearestExpressionPosition(Node $node): string
    {
        if ($node instanceof Expression) {
            return spl_object_hash($node);
        }

        /** @var Expression|null $foundNode */
        $foundNode = $this->betterNodeFinder->findFirstAncestorInstanceOf($node, Expression::class);
        if ($foundNode === null) {
            throw new ShouldNotHappenException();
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
