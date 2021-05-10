<?php

declare (strict_types=1);
namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Switch_;
final class SwitchAnalyzer
{
    public function hasEachCaseBreak(\PhpParser\Node\Stmt\Switch_ $switch) : bool
    {
        $totalCases = \count($switch->cases);
        if ($totalCases === 1) {
            return \false;
        }
        foreach ($switch->cases as $key => $case) {
            if ($key === $totalCases - 1) {
                return \true;
            }
            foreach ($case->stmts as $caseStmt) {
                if ($caseStmt instanceof \PhpParser\Node\Stmt\Break_) {
                    continue 2;
                }
            }
            return \false;
        }
        return \true;
    }
    public function hasEachCaseSingleStmt(\PhpParser\Node\Stmt\Switch_ $switch) : bool
    {
        foreach ($switch->cases as $case) {
            $stmtsWithoutBreak = \array_filter($case->stmts, function (\PhpParser\Node $node) : bool {
                return !$node instanceof \PhpParser\Node\Stmt\Break_;
            });
            if (\count($stmtsWithoutBreak) !== 1) {
                return \false;
            }
        }
        return \true;
    }
}
