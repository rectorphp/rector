<?php

declare (strict_types=1);
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
    public function normalizeStmts(array $stmts) : array
    {
        $normalizedStmts = [];
        foreach ($stmts as $stmt) {
            if ($stmt instanceof \PhpParser\Node\Stmt\Expression) {
                $normalizedStmts[] = $stmt;
                continue;
            }
            $normalizedStmts[] = new \PhpParser\Node\Stmt\Expression($stmt);
        }
        return $normalizedStmts;
    }
}
