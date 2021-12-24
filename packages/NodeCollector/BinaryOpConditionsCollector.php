<?php

declare(strict_types=1);

namespace Rector\NodeCollector;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;

final class BinaryOpConditionsCollector
{
    /**
     * @param class-string<BinaryOp> $binaryOpClass
     * @return Expr[]
     */
    public function findConditions(BinaryOp $binaryOp, string $binaryOpClass): array
    {
        $conditions = [];
        /** @var BinaryOp|Expr $binaryOp */
        while ($binaryOp instanceof BinaryOp) {
            $conditions[] = $binaryOp->right;
            $binaryOp = $binaryOp->left;

            if ($binaryOp::class !== $binaryOpClass) {
                $conditions[] = $binaryOp;
                break;
            }
        }

        krsort($conditions);
        return $conditions;
    }
}
