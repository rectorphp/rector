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
    private $expressionsToPrependBefore = [];

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
        $this->expressionsToPrependBefore = new SplObjectStorage();
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
        return $this->prependExpressionNodes($nodes);
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

    protected function prependNodeBeforeNode(Expr $nodeToPrepend, Node $positionNode): void
    {
        $positionExpressionNode = $this->betterNodeFinder->findFirstAncestorInstanceOf(
            $positionNode,
            Expression::class
        );

        $expressionToPrepend = $this->wrapToExpression($nodeToPrepend);

        if (isset($this->expressionsToPrependBefore[$positionExpressionNode])) {
            $this->expressionsToPrependBefore[$positionExpressionNode] = array_merge(
                $this->expressionsToPrependBefore[$positionExpressionNode],
                [$expressionToPrepend]
            );
        } else {
            $this->expressionsToPrependBefore[$positionExpressionNode] = [$expressionToPrepend];
        }
    }

    /**
     * Adds new nodes before or after particular Expression nodes.
     *
     * @param Node[] $nodes
     * @return Node[] array
     */
    private function prependExpressionNodes(array $nodes): array
    {
        foreach ($nodes as $i => $node) {
            if ($node instanceof Expression) {
                $nodes = $this->prependNodesAfterAndBeforeExpression($nodes, $node, $i);
            } elseif (isset($node->stmts)) {
                $node->stmts = $this->prependExpressionNodes($node->stmts);
            }
        }

        return $nodes;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function prependNodesAfterAndBeforeExpression(array $nodes, Node $node, int $i): array
    {
        if (isset($this->expressionsToPrependBefore[$node])) {
            array_splice($nodes, $i, 0, $this->expressionsToPrependBefore[$node]);

            unset($this->expressionsToPrependBefore[$node]);
        }

        if (isset($this->expressionsToPrependAfter[$node])) {
            array_splice($nodes, $i + 1, 0, $this->expressionsToPrependAfter[$node]);

            unset($this->expressionsToPrependAfter[$node]);
        }

        return $nodes;
    }

    private function wrapToExpression(Expr $exprNode): Expression
    {
        return new Expression($exprNode);
    }
}
