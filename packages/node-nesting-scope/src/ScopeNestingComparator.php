<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNestingScope\ValueObject\ControlStructure;

final class ScopeNestingComparator
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var Expr[]
     */
    private $doubleIfBranchExprs = [];

    public function __construct(BetterNodeFinder $betterNodeFinder, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function areScopeNestingEqual(Node $firstNode, Node $secondNode): bool
    {
        $firstNodeScopeNode = $this->findParentControlStructure($firstNode);
        $secondNodeScopeNode = $this->findParentControlStructure($secondNode);

        return $this->betterStandardPrinter->areNodesEqual($firstNodeScopeNode, $secondNodeScopeNode);
    }

    public function isNodeConditionallyScoped(Expr $expr): bool
    {
        $foundParent = $this->betterNodeFinder->findParentTypes(
            $expr,
            ControlStructure::CONDITIONAL_NODE_SCOPE_TYPES + [FunctionLike::class]
        );

        if (! $foundParent instanceof Node) {
            return false;
        }

        // is in both if/else branches
        if ($this->isInBothIfElseBranch($foundParent, $expr)) {
            return false;
        }

        if ($foundParent instanceof Else_ && $this->betterStandardPrinter->areNodesEqual(
            $expr,
            $this->doubleIfBranchExprs
        )) {
            return false;
        }

        return ! $foundParent instanceof FunctionLike;
    }

    public function isInBothIfElseBranch(Node $foundParentNode, Expr $seekedExpr): bool
    {
        if ($foundParentNode instanceof Else_) {
            return $this->betterStandardPrinter->isNodeEqual($seekedExpr, $this->doubleIfBranchExprs);
        }

        if (! $foundParentNode instanceof If_) {
            return false;
        }

        $foundIfNode = $this->betterNodeFinder->find($foundParentNode->stmts, function ($node) use ($seekedExpr): bool {
            return $this->betterStandardPrinter->areNodesEqual($node, $seekedExpr);
        });

        if ($foundParentNode->else === null) {
            return false;
        }

        $foundElseNode = $this->betterNodeFinder->find($foundParentNode->else, function ($node) use (
            $seekedExpr
        ): bool {
            return $this->betterStandardPrinter->areNodesEqual($node, $seekedExpr);
        });

        if ($foundIfNode && $foundElseNode) {
            $this->doubleIfBranchExprs[] = $seekedExpr;
            return true;
        }

        return false;
    }

    private function findParentControlStructure(Node $node): ?Node
    {
        return $this->betterNodeFinder->findParentTypes($node, ControlStructure::BREAKING_SCOPE_NODE_TYPES);
    }
}
