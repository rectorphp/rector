<?php

declare(strict_types=1);

namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\Enum\MatchKind;
use Rector\Php80\ValueObject\CondAndExpr;

final class MatchSwitchAnalyzer
{
    public function __construct(
        private SwitchAnalyzer $switchAnalyzer,
        private NodeNameResolver $nodeNameResolver,
    ) {
    }

    /**
     * @param CondAndExpr[] $condAndExprs
     */
    public function shouldSkipSwitch(Switch_ $switch, array $condAndExprs): bool
    {
        if ($condAndExprs === []) {
            return true;
        }

        if (! $this->switchAnalyzer->hasEachCaseBreak($switch)) {
            return true;
        }

        if (! $this->switchAnalyzer->hasEachCaseSingleStmt($switch)) {
            return false;
        }

        if ($this->switchAnalyzer->hasDefault($switch)) {
            return false;
        }

        // is followed by return? is considered implicit default
        if ($this->isNextStmtReturnWithExpr($switch)) {
            return false;
        }

        return ! $this->isNextStmtThrows($switch);
    }

    /**
     * @param CondAndExpr[] $condAndExprs
     */
    public function haveCondAndExprsMatchPotential(array $condAndExprs): bool
    {
        $uniqueCondAndExprKinds = $this->resolveUniqueKindsWithoutThrows($condAndExprs);
        if (count($uniqueCondAndExprKinds) > 1) {
            return false;
        }

        $assignVariableNames = [];
        foreach ($condAndExprs as $condAndExpr) {
            $expr = $condAndExpr->getExpr();
            if (! $expr instanceof Assign) {
                continue;
            }

            if ($expr->var instanceof ArrayDimFetch) {
                $arrayDimFethName = $this->nodeNameResolver->getName($expr->var->var);
                $assignVariableNames[] = $expr->var::class . $arrayDimFethName . '[]';
            } else {
                $assignVariableNames[] = $expr->var::class . $this->nodeNameResolver->getName($expr->var);
            }
        }

        $assignVariableNames = array_unique($assignVariableNames);
        return count($assignVariableNames) <= 1;
    }

    public function hasDefaultValue(Match_ $match): bool
    {
        foreach ($match->arms as $matchArm) {
            if ($matchArm->conds === null) {
                return true;
            }
            if ($matchArm->conds === []) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CondAndExpr[] $condAndExprs
     * @return MatchKind[]
     */
    private function resolveUniqueKindsWithoutThrows(array $condAndExprs): array
    {
        $condAndExprKinds = [];
        foreach ($condAndExprs as $condAndExpr) {
            if ($condAndExpr->equalsMatchKind(MatchKind::THROW())) {
                continue;
            }

            $condAndExprKinds[] = $condAndExpr->getMatchKind();
        }

        return array_unique($condAndExprKinds);
    }

    private function isNextStmtReturnWithExpr(Switch_ $switch): bool
    {
        $parent = $switch->getAttribute(AttributeKey::NEXT_NODE);
        if (! $parent instanceof Return_) {
            return false;
        }

        return $parent->expr !== null;
    }

    private function isNextStmtThrows(Switch_ $switch): bool
    {
        $parent = $switch->getAttribute(AttributeKey::NEXT_NODE);
        return $parent instanceof Throw_;
    }
}
