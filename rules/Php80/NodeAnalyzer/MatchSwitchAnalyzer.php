<?php

declare (strict_types=1);
namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\Enum\MatchKind;
use Rector\Php80\ValueObject\CondAndExpr;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Printer\BetterStandardPrinter;
final class MatchSwitchAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\SwitchAnalyzer
     */
    private $switchAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    public function __construct(\Rector\Php80\NodeAnalyzer\SwitchAnalyzer $switchAnalyzer, NodeNameResolver $nodeNameResolver, NodeComparator $nodeComparator, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->switchAnalyzer = $switchAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    public function isReturnCondsAndExprs(array $condAndExprs) : bool
    {
        foreach ($condAndExprs as $condAndExpr) {
            if ($condAndExpr->equalsMatchKind(MatchKind::RETURN)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    public function shouldSkipSwitch(Switch_ $switch, array $condAndExprs, ?Stmt $nextStmt) : bool
    {
        if ($condAndExprs === []) {
            return \true;
        }
        if (!$this->switchAnalyzer->hasEachCaseBreak($switch)) {
            return \true;
        }
        if ($this->switchAnalyzer->hasDifferentTypeCases($switch->cases, $switch->cond)) {
            return \true;
        }
        if (!$this->switchAnalyzer->hasEachCaseSingleStmt($switch)) {
            return \false;
        }
        if ($this->switchAnalyzer->hasDefaultSingleStmt($switch)) {
            return \false;
        }
        // is followed by return? is considered implicit default
        if ($this->isNextStmtReturnWithExpr($switch, $nextStmt)) {
            return \false;
        }
        return !$nextStmt instanceof Throw_;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    public function haveCondAndExprsMatchPotential(array $condAndExprs) : bool
    {
        $uniqueCondAndExprKinds = $this->resolveUniqueKindsWithoutThrows($condAndExprs);
        if (\count($uniqueCondAndExprKinds) > 1) {
            return \false;
        }
        $assignVariableNames = [];
        foreach ($condAndExprs as $condAndExpr) {
            $expr = $condAndExpr->getExpr();
            if (!$expr instanceof Assign) {
                continue;
            }
            if ($expr->var instanceof ArrayDimFetch) {
                $assignVariableNames[] = $this->betterStandardPrinter->print($expr->var);
            } else {
                $assignVariableNames[] = \get_class($expr->var) . $this->nodeNameResolver->getName($expr->var);
            }
        }
        $assignVariableNames = \array_unique($assignVariableNames);
        return \count($assignVariableNames) <= 1;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    public function hasCondsAndExprDefaultValue(array $condAndExprs) : bool
    {
        foreach ($condAndExprs as $condAndExpr) {
            if ($condAndExpr->getCondExprs() === null) {
                return \true;
            }
        }
        return \false;
    }
    public function hasDefaultValue(Match_ $match) : bool
    {
        foreach ($match->arms as $matchArm) {
            if ($matchArm->conds === null) {
                return \true;
            }
            if ($matchArm->conds === []) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     * @return array<MatchKind::*>
     */
    private function resolveUniqueKindsWithoutThrows(array $condAndExprs) : array
    {
        $condAndExprKinds = [];
        foreach ($condAndExprs as $condAndExpr) {
            if ($condAndExpr->equalsMatchKind(MatchKind::THROW)) {
                continue;
            }
            $condAndExprKinds[] = $condAndExpr->getMatchKind();
        }
        return \array_unique($condAndExprKinds);
    }
    private function isNextStmtReturnWithExpr(Switch_ $switch, ?Stmt $nextStmt) : bool
    {
        if (!$nextStmt instanceof Return_) {
            return \false;
        }
        if (!$nextStmt->expr instanceof Expr) {
            return \false;
        }
        foreach ($switch->cases as $case) {
            /** @var Expression[] $expressions */
            $expressions = \array_filter($case->stmts, static function (Node $node) : bool {
                return $node instanceof Expression;
            });
            foreach ($expressions as $expression) {
                if (!$expression->expr instanceof Assign) {
                    continue;
                }
                if (!$this->nodeComparator->areNodesEqual($expression->expr->var, $nextStmt->expr)) {
                    return \false;
                }
            }
        }
        return \true;
    }
}
