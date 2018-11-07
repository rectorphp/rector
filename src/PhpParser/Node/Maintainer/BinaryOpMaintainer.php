<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Maintainer;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;

final class BinaryOpMaintainer
{
    /**
     * Tries to match left or right parts (xor),
     * returns null or match on first condition and then second condition. No matter what the origin order is.
     *
     * @return Node[]|null
     */
    public function matchFirstAndSecondConditionNode(
        BinaryOp $binaryOp,
        callable $firstCondition,
        callable $secondCondition
    ): ?array {
        if ($firstCondition($binaryOp->left, $binaryOp->right) && $secondCondition($binaryOp->right, $binaryOp->left)) {
            return [$binaryOp->left, $binaryOp->right];
        }

        if ($firstCondition($binaryOp->right, $binaryOp->left) && $secondCondition($binaryOp->left, $binaryOp->right)) {
            return [$binaryOp->right, $binaryOp->left];
        }

        return null;
    }
}
