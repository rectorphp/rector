<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\AssignAndBinaryMap;

final class BinaryOpManipulator
{
    /**
     * @var AssignAndBinaryMap
     */
    private $assignAndBinaryMap;

    public function __construct(AssignAndBinaryMap $assignAndBinaryMap)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
    }

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

    public function inverseBinaryOp(BinaryOp $binaryOp): ?BinaryOp
    {
        // no nesting
        if ($binaryOp->left instanceof BooleanOr) {
            return null;
        }

        if ($binaryOp->right instanceof BooleanOr) {
            return null;
        }

        $inversedNodeClass = $this->resolveInversedNodeClass($binaryOp);
        if ($inversedNodeClass === null) {
            return null;
        }

        $firstInversedNode = $this->inverseNode($binaryOp->left);
        $secondInversedNode = $this->inverseNode($binaryOp->right);

        return new $inversedNodeClass($firstInversedNode, $secondInversedNode);
    }

    public function inverseCondition(BinaryOp $binaryOp): ?BinaryOp
    {
        // no nesting
        if ($binaryOp->left instanceof BooleanOr) {
            return null;
        }

        if ($binaryOp->right instanceof BooleanOr) {
            return null;
        }

        $inversedNodeClass = $this->resolveInversedNodeClass($binaryOp);
        if ($inversedNodeClass === null) {
            return null;
        }

        return new $inversedNodeClass($binaryOp->left, $binaryOp->right);
    }

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

        return function (Node $node) use ($condition): bool {
            return is_a($node, $condition, true);
        };
    }

    private function inverseNode(Expr $expr): Node
    {
        if ($expr instanceof BinaryOp) {
            $inversedBinaryOp = $this->assignAndBinaryMap->getInversed($expr);
            if ($inversedBinaryOp) {
                return new $inversedBinaryOp($expr->left, $expr->right);
            }
        }

        if ($expr instanceof BooleanNot) {
            return $expr->expr;
        }

        return new BooleanNot($expr);
    }

    private function resolveInversedNodeClass(BinaryOp $binaryOp): ?string
    {
        $inversedNodeClass = $this->assignAndBinaryMap->getInversed($binaryOp);
        if ($inversedNodeClass !== null) {
            return $inversedNodeClass;
        }

        if ($binaryOp instanceof BooleanOr) {
            return BooleanAnd::class;
        }

        return null;
    }
}
