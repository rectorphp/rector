<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNestingScope\ValueObject\ControlStructure;

final class ScopeNestingComparator
{
    /**
     * @var Expr[]
     */
    private array $doubleIfBranchExprs = [];

    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeComparator $nodeComparator
    ) {
    }

    public function areReturnScopeNested(Return_ $return, Node $secondNodeScopeNode): bool
    {
        $firstNodeScopeNode = $this->betterNodeFinder->findParentTypes(
            $return,
            ControlStructure::RETURN_ISOLATING_SCOPE_NODE_TYPES
        );

        return $this->nodeComparator->areNodesEqual($firstNodeScopeNode, $secondNodeScopeNode);
    }

    public function areScopeNestingEqual(Node $firstNode, Node $secondNode): bool
    {
        $firstNodeScopeNode = $this->findParentControlStructure($firstNode);
        $secondNodeScopeNode = $this->findParentControlStructure($secondNode);

        return $this->nodeComparator->areNodesEqual($firstNodeScopeNode, $secondNodeScopeNode);
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
        if (! $foundParent instanceof Else_) {
            return ! $foundParent instanceof FunctionLike;
        }
        if (! $this->nodeComparator->areNodesEqual($expr, $this->doubleIfBranchExprs)) {
            return ! $foundParent instanceof FunctionLike;
        }
        return false;
    }

    public function isInBothIfElseBranch(Node $foundParentNode, Expr $seekedExpr): bool
    {
        if ($foundParentNode instanceof Else_) {
            return $this->nodeComparator->isNodeEqual($seekedExpr, $this->doubleIfBranchExprs);
        }

        if (! $foundParentNode instanceof If_) {
            return false;
        }

        $foundIfNode = $this->betterNodeFinder->find($foundParentNode->stmts, function ($node) use ($seekedExpr): bool {
            return $this->nodeComparator->areNodesEqual($node, $seekedExpr);
        });

        if ($foundParentNode->else === null) {
            return false;
        }

        $foundElseNode = $this->betterNodeFinder->find($foundParentNode->else, function ($node) use (
            $seekedExpr
        ): bool {
            return $this->nodeComparator->areNodesEqual($node, $seekedExpr);
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
