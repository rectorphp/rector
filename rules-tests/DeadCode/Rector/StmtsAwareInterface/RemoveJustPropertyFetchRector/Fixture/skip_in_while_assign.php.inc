<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;

final class SkipInWhileAssign
{
    public function run(Expr $expr, string $binaryOpClass)
    {
        $conditions = [];

        while ($expr instanceof BinaryOp) {
            $conditions[] = $expr->right;
            $expr = $expr->left;

            if ($binaryOpClass !== $expr::class) {
                $conditions[] = $expr;
                break;
            }
        }
    }
}
