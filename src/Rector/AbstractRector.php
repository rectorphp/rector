<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Rector\RectorInterface;
use Rector\NodeTraverserQueue\BetterNodeFinder;
use SplObjectStorage;

abstract class AbstractRector extends NodeVisitorAbstract implements RectorInterface
{
    /**
     * @var SplObjectStorage|Expression[][]
     */
    private $expressionsToPrependAfter = [];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * Nasty magic, unable to do that in config autowire _instanceof calls.
     *
     * @required
     */
    public function setBetterNodeFinder(BetterNodeFinder $betterNodeFinder): void
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    final public function beforeTraverse(array $nodes): array
    {
        $this->expressionsToPrependAfter = new SplObjectStorage();

        return $nodes;
    }

    /**
     * @return null|int|Node
     */
    final public function enterNode(Node $node)
    {
        if ($this->isCandidate($node)) {
            $newNode = $this->refactor($node);
            if ($newNode !== null) {
                return $newNode;
            }

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        // Add new nodes after particular Expression nodes
        $expressionPrependerNodeVisitor = new class($this->expressionsToPrependAfter) extends NodeVisitorAbstract {
            private $expressionsToPrependAfter;
            public function __construct(SplObjectStorage $expressionsToPrependAfter)
            {
                $this->expressionsToPrependAfter = $expressionsToPrependAfter;
            }

            public function leaveNode(Node $node)
            {
                if (! isset($this->expressionsToPrependAfter[$node])) {
                    return $node;
                }

                return array_merge([$node], $this->expressionsToPrependAfter[$node]);
            }
        };

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($expressionPrependerNodeVisitor);

        return $nodeTraverser->traverse($nodes);
    }

    protected function prependNodeAfterNode(Expr $nodeToPrepend, Node $positionNode): void
    {
        $positionExpressionNode = $this->betterNodeFinder->findFirstAncestorInstanceOf(
            $positionNode,
            Expression::class
        );

        $expressionToPrepend = $this->wrapToExpression($nodeToPrepend);

        if (isset($this->expressionsToPrependAfter[$positionExpressionNode])) {
            $this->expressionsToPrependAfter[$positionExpressionNode] = array_merge(
                $this->expressionsToPrependAfter[$positionExpressionNode],
                [$expressionToPrepend]
            );
        } else {
            $this->expressionsToPrependAfter[$positionExpressionNode] = [$expressionToPrepend];
        }
    }

    private function wrapToExpression(Expr $exprNode): Expression
    {
        return new Expression($exprNode);
    }
}
