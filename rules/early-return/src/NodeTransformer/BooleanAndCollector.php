<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\NodeTransformer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;

final class BooleanAndCollector
{
    /**
     * @return Expr[]
     */
    public function getBooleanAndConditions(BooleanAnd $booleanAnd): array
    {
        $conditions = [];
        while ($booleanAnd instanceof BinaryOp) {
            $conditions[] = $booleanAnd->right;
            $booleanAnd = $booleanAnd->left;

            if (! $booleanAnd instanceof BooleanAnd) {
                $conditions[] = $booleanAnd;
                break;
            }
        }

        krsort($conditions);
        return $conditions;
    }
}
