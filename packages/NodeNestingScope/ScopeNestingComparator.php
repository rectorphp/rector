<?php

declare (strict_types=1);
namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNestingScope\ValueObject\ControlStructure;
final class ScopeNestingComparator
{
    /**
     * @var Expr[]
     */
    private $doubleIfBranchExprs = [];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    public function isNodeConditionallyScoped(Expr $expr) : bool
    {
        $foundParent = $this->betterNodeFinder->findParentByTypes($expr, ControlStructure::CONDITIONAL_NODE_SCOPE_TYPES + [FunctionLike::class]);
        if (!$foundParent instanceof Node) {
            return \false;
        }
        // is in both if/else branches
        if ($this->isInBothIfElseBranch($foundParent, $expr)) {
            return \false;
        }
        if (!$foundParent instanceof Else_) {
            return !$foundParent instanceof FunctionLike;
        }
        if (!$this->nodeComparator->areNodesEqual($expr, $this->doubleIfBranchExprs)) {
            return !$foundParent instanceof FunctionLike;
        }
        return \false;
    }
    private function isInBothIfElseBranch(Node $foundParentNode, Expr $seekedExpr) : bool
    {
        if ($foundParentNode instanceof Else_) {
            return $this->nodeComparator->isNodeEqual($seekedExpr, $this->doubleIfBranchExprs);
        }
        if (!$foundParentNode instanceof If_) {
            return \false;
        }
        $foundIfNode = (bool) $this->betterNodeFinder->findFirst($foundParentNode->stmts, function (Node $node) use($seekedExpr) : bool {
            return $this->nodeComparator->areNodesEqual($node, $seekedExpr);
        });
        if (!$foundParentNode->else instanceof Else_) {
            return \false;
        }
        $foundElseNode = (bool) $this->betterNodeFinder->findFirst($foundParentNode->else, function (Node $node) use($seekedExpr) : bool {
            return $this->nodeComparator->areNodesEqual($node, $seekedExpr);
        });
        if ($foundIfNode && $foundElseNode) {
            $this->doubleIfBranchExprs[] = $seekedExpr;
            return \true;
        }
        return \false;
    }
}
