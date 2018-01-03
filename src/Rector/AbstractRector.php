<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTraverserQueue\BetterNodeFinder;
use Rector\Printer\BetterStandardPrinter;
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
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

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
     * Nasty magic, unable to do that in config autowire _instanceof calls.
     *
     * @required
     */
    public function setPrettyPrinter(BetterStandardPrinter $betterStandardPrinter): void
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
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
        $nodesWithPrependedExpressions = $this->prependExpressionNodes($nodes);

        $this->ensureAllExpressionsWerePrepended();

        return $nodesWithPrependedExpressions;
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

    /**
     * @todo maybe use leave node instead where is used array_splice() method?
     *
     * Adds new nodes after particular Expression nodes.
     *
     * @param Node[] $nodes
     * @return Node[] array
     */
    private function prependExpressionNodes(array $nodes): array
    {
        foreach ($nodes as $i => $node) {
            if (isset($node->stmts)) {
                $node->stmts = $this->prependExpressionNodes($node->stmts);
                if ($node instanceof Node\Stmt\If_ && isset($node->else->stmts)) {
                    $node->else->stmts = $this->prependExpressionNodes($node->else->stmts);
                }

            } elseif ($node instanceof Expression) {
                $nodes = $this->prependNodesAfterExpression($nodes, $node, $i);
            }
        }

        return $nodes;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function prependNodesAfterExpression(array $nodes, Node $node, int $i): array
    {
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

    private function ensureAllExpressionsWerePrepended(): void
    {
        foreach ($this->expressionsToPrependAfter as $value) {
            $targetExpression = $this->expressionsToPrependAfter->current();
            $targetExpressionInString = $this->betterStandardPrinter->prettyPrint([$targetExpression]);

            foreach ($this->expressionsToPrependAfter->getInfo() as $expressionToBeAdded) {
                $expressionToBeAddedInString = $this->betterStandardPrinter->prettyPrint([$expressionToBeAdded]);

                throw new ShouldNotHappenException(sprintf(
                    '"%s" expression was not added after %s"%s" in "%s" class',
                    $expressionToBeAddedInString,
                    PHP_EOL,
                    $targetExpressionInString,
                    self::class
                ));
            }
        }
    }
}
