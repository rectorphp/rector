<?php

declare(strict_types=1);

namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Switch_;
use Rector\NodeNameResolver\NodeNameResolver;
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

        return ! $this->switchAnalyzer->hasDefault($switch);
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
                $assignVariableNames[] = get_class($expr->var) . $arrayDimFethName . '[]';
            } else {
                $assignVariableNames[] = get_class($expr->var) . $this->nodeNameResolver->getName($expr->var);
            }
        }

        $assignVariableNames = array_unique($assignVariableNames);
        return count($assignVariableNames) <= 1;
    }

    /**
     * @param CondAndExpr[] $condAndExprs
     * @return string[]
     */
    private function resolveUniqueKindsWithoutThrows(array $condAndExprs): array
    {
        $condAndExprKinds = [];
        foreach ($condAndExprs as $condAndExpr) {
            if ($condAndExpr->getKind() === CondAndExpr::TYPE_THROW) {
                continue;
            }

            $condAndExprKinds[] = $condAndExpr->getKind();
        }

        return array_unique($condAndExprKinds);
    }
}
