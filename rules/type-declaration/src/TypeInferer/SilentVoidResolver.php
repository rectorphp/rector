<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\TryCatch;

final class SilentVoidResolver
{
    /**
     * @param ClassMethod|Closure|Function_ $functionLike
     */
    public function hasSilentVoid(FunctionLike $functionLike): bool
    {
        if ($this->hasStmtsAlwaysReturn((array) $functionLike->stmts)) {
            return false;
        }

        foreach ((array) $functionLike->stmts as $stmt) {
            // has switch with always return
            if ($stmt instanceof Switch_ && $this->isSwitchWithAlwaysReturn($stmt)) {
                return false;
            }

            // is part of try/catch
            if ($stmt instanceof TryCatch && $this->isTryCatchAlwaysReturn($stmt)) {
                return false;
            }

            if ($stmt instanceof Throw_) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Stmt[]|Expression[] $stmts
     */
    private function hasStmtsAlwaysReturn(array $stmts): bool
    {
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Expression) {
                $stmt = $stmt->expr;
            }

            // is 1st level return
            if ($stmt instanceof Return_) {
                return true;
            }
        }

        return false;
    }

    private function isSwitchWithAlwaysReturn(Switch_ $switch): bool
    {
        $casesWithReturn = 0;
        foreach ($switch->cases as $case) {
            foreach ($case->stmts as $caseStmt) {
                if ($caseStmt instanceof Return_) {
                    ++$casesWithReturn;
                    break;
                }
            }
        }

        // has same amount of returns as switches
        return count($switch->cases) === $casesWithReturn;
    }

    private function isTryCatchAlwaysReturn(TryCatch $tryCatch): bool
    {
        if (! $this->hasStmtsAlwaysReturn($tryCatch->stmts)) {
            return false;
        }

        foreach ($tryCatch->catches as $catch) {
            return $this->hasStmtsAlwaysReturn($catch->stmts);
        }

        return true;
    }
}
