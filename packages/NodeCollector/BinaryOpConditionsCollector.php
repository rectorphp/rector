<?php

declare(strict_types=1);

namespace Rector\NodeCollector;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;

final class BinaryOpConditionsCollector
{
    /**
     * Collects operands of a sequence of applications of a given left-associative binary operation.
     *
     * For example, for `a + b + c`, which is parsed as `(Plus (Plus a b) c)`, it will return `[a, b, c]`.
     * Note that parenthesization not matching the associativity (e.g. `a + (b + c)`) will return the parenthesized
     * nodes as standalone operands (`[a, b + c]`) even for associative operations.
     * Similarly, for right-associative operations (e.g. `a ?? b ?? c`), the result produced by
     * the implicit parenthesization (`[a, b ?? c]`) might not match the expectations.
     *
     * @param class-string<BinaryOp> $binaryOpClass
     * @return Expr[]
     */
    public function findConditions(BinaryOp $binaryOp, string $binaryOpClass): array
    {
        if ($binaryOp::class !== $binaryOpClass) {
            return [$binaryOp];
        }

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
