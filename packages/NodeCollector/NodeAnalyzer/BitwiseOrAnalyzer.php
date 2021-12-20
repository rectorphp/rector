<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;

final class BitwiseOrAnalyzer
{
    /**
     * @return Expr[]
     */
    public function findBitwiseOrConditions(BitwiseOr $bitwiseOr): array
    {
        $conditions = [];
        /** @var BinaryOp|Expr $bitwiseOr */
        while ($bitwiseOr instanceof BinaryOp) {
            $conditions[] = $bitwiseOr->right;
            $bitwiseOr = $bitwiseOr->left;

            if (! $bitwiseOr instanceof BitwiseOr) {
                $conditions[] = $bitwiseOr;
                break;
            }
        }

        krsort($conditions);
        return $conditions;
    }
}
