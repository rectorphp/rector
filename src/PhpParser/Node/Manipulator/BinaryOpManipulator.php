<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use Rector\Exception\ShouldNotHappenException;

final class BinaryOpManipulator
{
    /**
     * Tries to match left or right parts (xor),
     * returns null or match on first condition and then second condition. No matter what the origin order is.
     *
     * @param callable|string $firstCondition callable or Node to instanceof
     * @param callable|string $secondCondition callable or Node to instanceof
     * @return Node[]|null
     */
    public function matchFirstAndSecondConditionNode(
        BinaryOp $binaryOp,
        $firstCondition,
        $secondCondition
    ): ?array {
        $this->validateCondition($firstCondition);
        $this->validateCondition($secondCondition);

        $firstCondition = $this->normalizeCondition($firstCondition);
        $secondCondition = $this->normalizeCondition($secondCondition);

        if ($firstCondition($binaryOp->left, $binaryOp->right) && $secondCondition($binaryOp->right, $binaryOp->left)) {
            return [$binaryOp->left, $binaryOp->right];
        }

        if ($firstCondition($binaryOp->right, $binaryOp->left) && $secondCondition($binaryOp->left, $binaryOp->right)) {
            return [$binaryOp->right, $binaryOp->left];
        }

        return null;
    }

    /**
     * @param mixed $firstCondition
     */
    private function validateCondition($firstCondition): void
    {
        if (is_callable($firstCondition)) {
            return;
        }

        if (is_a($firstCondition, Node::class, true)) {
            return;
        }

        throw new ShouldNotHappenException();
    }

    /**
     * @param callable|string $condition
     */
    private function normalizeCondition($condition): callable
    {
        if (is_callable($condition)) {
            return $condition;
        }

        return function (Node $node) use ($condition) {
            return is_a($node, $condition, true);
        };
    }
}
