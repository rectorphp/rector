<?php

declare(strict_types=1);

namespace Rector\PHPUnit\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;

final class StmtManipulator
{
    /**
     * @param Expr[]|Stmt[] $stmts
     * @return Stmt[]
     */
    public function normalizeStmts(array $stmts): array
    {
        foreach ($stmts as $key => $stmt) {
            if ($stmt instanceof Expression) {
                continue;
            }

            $stmts[$key] = new Expression($stmt);
        }

        return $stmts;
    }
}
