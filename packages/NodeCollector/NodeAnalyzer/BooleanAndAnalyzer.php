<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;

final class BooleanAndAnalyzer
{
    /**
     * @return Expr[]
     */
    public function findBooleanAndConditions(BooleanAnd $booleanAnd): array
    {
        $conditions = [];
        /** @var BinaryOp|Expr $booleanAnd */
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
