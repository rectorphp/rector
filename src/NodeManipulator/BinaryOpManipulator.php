<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Php71\ValueObject\TwoNodeMatch;

final class BinaryOpManipulator
{
    public function __construct(
        private AssignAndBinaryMap $assignAndBinaryMap
    ) {
    }

    /**
     * Tries to match left or right parts (xor),
     * returns null or match on first condition and then second condition. No matter what the origin order is.
     *
     * @param callable|class-string<Node> $firstCondition
     * @param callable|class-string<Node> $secondCondition
     */
    public function matchFirstAndSecondConditionNode(
        BinaryOp $binaryOp,
        $firstCondition,
        $secondCondition
    ): ?TwoNodeMatch {
        $this->validateCondition($firstCondition);
        $this->validateCondition($secondCondition);

        $firstCondition = $this->normalizeCondition($firstCondition);
        $secondCondition = $this->normalizeCondition($secondCondition);

        if ($firstCondition($binaryOp->left, $binaryOp->right) && $secondCondition($binaryOp->right, $binaryOp->left)) {
            return new TwoNodeMatch($binaryOp->left, $binaryOp->right);
        }
        if (! $firstCondition($binaryOp->right, $binaryOp->left)) {
            return null;
        }
        if (! $secondCondition($binaryOp->left, $binaryOp->right)) {
            return null;
        }
        return new TwoNodeMatch($binaryOp->right, $binaryOp->left);
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

    public function invertCondition(BinaryOp $binaryOp): ?BinaryOp
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

    public function inverseNode(Expr $expr): Node
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

    /**
     * @param callable|class-string<Node> $firstCondition
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
     * @param callable|class-string<Node> $condition
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
