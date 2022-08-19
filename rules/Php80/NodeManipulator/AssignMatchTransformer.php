<?php

declare (strict_types=1);
namespace Rector\Php80\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Php80\ValueObject\MatchAssignResult;
final class AssignMatchTransformer
{
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
    public function unwrapMatchArmAssignsToAssign(Match_ $match, Expr $expr, bool $hasDefaultValue, ?Stmt $previousStmt, ?Stmt $nextStmt) : ?MatchAssignResult
    {
        // containts next this expr?
        if (!$hasDefaultValue && $this->isFollowedByReturnWithExprUsage($nextStmt, $expr)) {
            return null;
        }
        $prevInitializedAssign = $this->resolvePreviousInitializedAssign($previousStmt, $expr);
        $assign = new Assign($expr, $match);
        if (!$prevInitializedAssign instanceof Assign) {
            $currentAssign = $this->resolveCurrentAssign($hasDefaultValue, $assign);
            if ($currentAssign instanceof Assign) {
                return new MatchAssignResult($currentAssign, \false);
            }
            return null;
        }
        $matchArmCount = \count($match->arms);
        if ($hasDefaultValue) {
            $default = $match->arms[$matchArmCount - 1]->body;
            if ($this->nodeComparator->areNodesEqual($default, $prevInitializedAssign->var)) {
                return new MatchAssignResult($assign, \false);
            }
        } else {
            $match->arms[$matchArmCount] = new MatchArm(null, $prevInitializedAssign->expr);
        }
        return new MatchAssignResult($assign, \true);
    }
    private function resolveCurrentAssign(bool $hasDefaultValue, Assign $assign) : ?Assign
    {
        return $hasDefaultValue ? $assign : null;
    }
    private function isFollowedByReturnWithExprUsage(?\PhpParser\Node\Stmt $nextStmt, Expr $expr) : bool
    {
        if (!$nextStmt instanceof Return_) {
            return \false;
        }
        if (!$nextStmt->expr instanceof Expr) {
            return \false;
        }
        $returnExprs = $this->betterNodeFinder->findInstanceOf($nextStmt, Expr::class);
        foreach ($returnExprs as $returnExpr) {
            if ($this->nodeComparator->areNodesEqual($expr, $returnExpr)) {
                return \true;
            }
        }
        return \false;
    }
    private function resolvePreviousInitializedAssign(?Stmt $previousStmt, Expr $expr) : ?Assign
    {
        if (!$previousStmt instanceof Expression) {
            return null;
        }
        $previousExpr = $previousStmt->expr;
        if (!$previousExpr instanceof Assign) {
            return null;
        }
        // is that assign to our variable?
        if (!$this->nodeComparator->areNodesEqual($previousExpr->var, $expr)) {
            return null;
        }
        return $previousExpr;
    }
}
