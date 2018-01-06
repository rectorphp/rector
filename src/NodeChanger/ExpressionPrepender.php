<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use Rector\NodeTraverserQueue\BetterNodeFinder;
use Rector\NodeVisitor\ExpressionPrependingNodeVisitor;
use SplObjectStorage;

/**
 * This class collects all to-be-added expresssions (expression ~= 1 line in code)
 * and then prepends new expressions to list of $nodes
 *
 * From:
 * - $this->someCall();
 *
 * To:
 * - $this->someCall();
 * - $value = this->someNewCall(); // added expression
 */
final class ExpressionPrepender
{
    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    /**
     * @var ExpressionPrependingNodeVisitor
     */
    private $expressionPrependingNodeVisitor;

    /**
     * @var SplObjectStorage
     */
    private $expressionsToPrepend;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        ExpressionPrependingNodeVisitor $expressionPrependingNodeVisitor,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->nodeTraverser = new NodeTraverser();
        $this->nodeTraverser->addVisitor($expressionPrependingNodeVisitor);

        $this->expressionPrependingNodeVisitor = $expressionPrependingNodeVisitor;
        $this->expressionsToPrepend = new SplObjectStorage();
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function prependNodeAfterNode(Expr $nodeToPrepend, Node $positionNode): void
    {
        $expressionToPrepend = new Expression($nodeToPrepend);

        $positionExpressionNode = $this->betterNodeFinder->findFirstAncestorInstanceOf(
            $positionNode,
            Expression::class
        );

        if (isset($this->expressionsToPrepend[$positionExpressionNode])) {
            $this->expressionsToPrepend[$positionExpressionNode] = array_merge(
                $this->expressionsToPrepend[$positionExpressionNode],
                [$expressionToPrepend]
            );
        } else {
            $this->expressionsToPrepend[$positionExpressionNode] = [$expressionToPrepend];
        }
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function prependExpressionsToNodes(array $nodes): array
    {
        $this->expressionPrependingNodeVisitor->setExpressionsToPrepend($this->expressionsToPrepend);

        return $this->nodeTraverser->traverse($nodes);
    }
}
