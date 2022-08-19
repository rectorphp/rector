<?php

declare (strict_types=1);
namespace Rector\Php80\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_ as ThrowsStmt;
use Rector\Php80\Enum\MatchKind;
use Rector\Php80\NodeAnalyzer\MatchSwitchAnalyzer;
use Rector\Php80\ValueObject\CondAndExpr;
use Rector\Php80\ValueObject\MatchResult;
final class MatchFactory
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeFactory\MatchArmsFactory
     */
    private $matchArmsFactory;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\MatchSwitchAnalyzer
     */
    private $matchSwitchAnalyzer;
    public function __construct(\Rector\Php80\NodeFactory\MatchArmsFactory $matchArmsFactory, MatchSwitchAnalyzer $matchSwitchAnalyzer)
    {
        $this->matchArmsFactory = $matchArmsFactory;
        $this->matchSwitchAnalyzer = $matchSwitchAnalyzer;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    public function createFromCondAndExprs(Expr $condExpr, array $condAndExprs, ?Stmt $nextStmt) : ?MatchResult
    {
        $matchArms = $this->matchArmsFactory->createFromCondAndExprs($condAndExprs);
        $match = new Match_($condExpr, $matchArms);
        // implicit return default after switch
        if ($nextStmt instanceof Return_ && $nextStmt->expr instanceof Expr) {
            return $this->processImplicitReturnAfterSwitch($match, $condAndExprs, $nextStmt->expr);
        }
        if ($nextStmt instanceof ThrowsStmt) {
            return $this->processImplicitThrowsAfterSwitch($match, $condAndExprs, $nextStmt->expr);
        }
        return new MatchResult($match, \false);
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function processImplicitReturnAfterSwitch(Match_ $match, array $condAndExprs, Expr $returnExpr) : ?MatchResult
    {
        if ($this->matchSwitchAnalyzer->hasDefaultValue($match)) {
            return new MatchResult($match, \false);
        }
        $expr = $this->resolveAssignVar($condAndExprs);
        if ($expr instanceof ArrayDimFetch) {
            return null;
        }
        $shouldRemoveNextStmt = \false;
        if (!$expr instanceof Expr) {
            $shouldRemoveNextStmt = \true;
        }
        $condAndExprs[] = new CondAndExpr([], $returnExpr, MatchKind::RETURN);
        $matchArms = $this->matchArmsFactory->createFromCondAndExprs($condAndExprs);
        $wrappedMatch = new Match_($match->cond, $matchArms);
        return new MatchResult($wrappedMatch, $shouldRemoveNextStmt);
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function resolveAssignVar(array $condAndExprs) : ?Expr
    {
        foreach ($condAndExprs as $condAndExpr) {
            $expr = $condAndExpr->getExpr();
            if (!$expr instanceof Assign) {
                continue;
            }
            return $expr->var;
        }
        return null;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function processImplicitThrowsAfterSwitch(Match_ $match, array $condAndExprs, Expr $throwExpr) : MatchResult
    {
        if ($this->matchSwitchAnalyzer->hasDefaultValue($match)) {
            return new MatchResult($match, \false);
        }
        $throw = new Throw_($throwExpr);
        $condAndExprs[] = new CondAndExpr([], $throw, MatchKind::RETURN);
        $matchArms = $this->matchArmsFactory->createFromCondAndExprs($condAndExprs);
        $wrappedMatch = new Match_($match->cond, $matchArms);
        return new MatchResult($wrappedMatch, \true);
    }
}
