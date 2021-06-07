<?php

declare(strict_types=1);

namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Switch_;

final class SwitchAnalyzer
{
    public function hasEachCaseBreak(Switch_ $switch): bool
    {
        $totalCases = count($switch->cases);
        if ($totalCases === 1) {
            return false;
        }

        foreach ($switch->cases as $key => $case) {
            if ($key === $totalCases - 1) {
                return true;
            }

            foreach ($case->stmts as $caseStmt) {
                if ($caseStmt instanceof Break_) {
                    continue 2;
                }
            }

            return false;
        }

        return true;
    }

    public function hasEachCaseSingleStmt(Switch_ $switch): bool
    {
        foreach ($switch->cases as $case) {
            $stmtsWithoutBreak = array_filter($case->stmts, fn (Node $node): bool => ! $node instanceof Break_);

            if (count($stmtsWithoutBreak) !== 1) {
                return false;
            }
        }

        return true;
    }

    public function hasDefault(Switch_ $switch): bool
    {
        foreach ($switch->cases as $case) {
            if ($case->cond === null) {
                return true;
            }
        }

        return false;
    }
}
