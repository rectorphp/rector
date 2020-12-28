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
        foreach ($switch->cases as $case) {
            foreach ($case->stmts as $caseStmt) {
                if (! $caseStmt instanceof Break_) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    public function hasEachCaseSingleStmt(Switch_ $switch): bool
    {
        foreach ($switch->cases as $case) {
            $stmtsWithoutBreak = array_filter($case->stmts, function (Node $node): bool {
                return ! $node instanceof Break_;
            });

            if (count($stmtsWithoutBreak) !== 1) {
                return false;
            }
        }

        return true;
    }
}
