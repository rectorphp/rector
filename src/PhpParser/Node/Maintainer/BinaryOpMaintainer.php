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
        ?callable $secondCondition = null
    ): ?array {
        if ($secondCondition === null) {
            $secondCondition = function (Node $node) {
                return $node;
            };
        }

        if ($firstCondition($binaryOp->left) && $secondCondition($binaryOp->right)) {
            return [$binaryOp->left, $binaryOp->right];
        }

        if ($firstCondition($binaryOp->right) && $secondCondition($binaryOp->left)) {
            return [$binaryOp->right, $binaryOp->left];
        }

        return null;
    }
}
