<?php declare(strict_types=1);

namespace Rector\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTraverserQueue\BetterNodeFinder;
use SplObjectStorage;

final class ExpressionPrependingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var SplObjectStorage
     */
    private $expressionsToPrepend;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function clear(): void
    {
        $this->expressionsToPrepend = new SplObjectStorage();
    }

    public function setExpressionsToPrepend(SplObjectStorage $expressionsToPrepend)
    {
        $this->expressionsToPrepend = $expressionsToPrepend;
    }

    /**
     * @return Node[]|Node
     */
    public function leaveNode(Node $node)
    {
        if (! isset($this->expressionsToPrepend[$node])) {
            return $node;
        }

        return array_merge([$node], $this->expressionsToPrepend[$node]);
    }

    public function prependNodeAfterNode(Expr $nodeToPrepend, Node $positionNode): void
    {
        $expressionToPrepend = $this->wrapToExpression($nodeToPrepend);
        $positionExpressionNode = $this->betterNodeFinder->findFirstAncestorInstanceOf($positionNode, Expression::class);

        if (isset($this->expressionsToPrepend[$positionExpressionNode])) {
            $this->expressionsToPrepend[$positionExpressionNode] = array_merge(
                $this->expressionsToPrepend[$positionExpressionNode],
                [$expressionToPrepend]
            );
        } else {
            $this->expressionsToPrepend[$positionExpressionNode] = [$expressionToPrepend];
        }
    }

    private function wrapToExpression(Expr $exprNode): Expression
    {
        return new Expression($exprNode);
    }
}
