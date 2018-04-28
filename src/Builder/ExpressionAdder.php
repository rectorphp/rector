<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use Rector\NodeTraverserQueue\BetterNodeFinder;
use Rector\PhpParser\NodeVisitor\ExpressionAddingNodeVisitor;
use SplObjectStorage;

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
final class ExpressionAdder
{
    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    /**
     * @var ExpressionAddingNodeVisitor
     */
    private $expressionAddingNodeVisitor;

    /**
     * @var SplObjectStorage
     */
    private $expressionsToAdd;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        ExpressionAddingNodeVisitor $expressionAddingNodeVisitor,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->nodeTraverser = new NodeTraverser();
        $this->nodeTraverser->addVisitor($expressionAddingNodeVisitor);

        $this->expressionAddingNodeVisitor = $expressionAddingNodeVisitor;
        $this->expressionsToAdd = new SplObjectStorage();
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function addNodeAfterNode(Expr $newNode, Node $positionNode): void
    {
        $expressionToAdd = new Expression($newNode);

        $positionExpressionNode = $this->betterNodeFinder->findFirstAncestorInstanceOf(
            $positionNode,
            Expression::class
        );

        if ($positionExpressionNode === null) {
            $positionExpressionNode = $positionNode;
        }

        $expressionsToAdd = [$expressionToAdd];

        if ($this->expressionsToAdd->contains($positionExpressionNode)) {
            $expressionsToAdd = array_merge($this->expressionsToAdd[$positionExpressionNode], $expressionsToAdd);
        }

        $this->expressionsToAdd->attach($positionExpressionNode, $expressionsToAdd);
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function addExpressionsToNodes(array $nodes): array
    {
        if (! count($this->expressionsToAdd)) {
            return $nodes;
        }

        $this->expressionAddingNodeVisitor->setExpressionsToAdd($this->expressionsToAdd);

        return $this->nodeTraverser->traverse($nodes);
    }
}
