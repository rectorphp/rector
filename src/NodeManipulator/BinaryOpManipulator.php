<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\NodeManipulator;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use RectorPrefix20220606\Rector\Php71\ValueObject\TwoNodeMatch;
final class BinaryOpManipulator
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\AssignAndBinaryMap
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
     * @param callable(Node $firstNode, Node $secondNode=): bool|class-string<Node> $firstCondition
     * @param callable(Node $firstNode, Node $secondNode=): bool|class-string<Node> $secondCondition
     */
    public function matchFirstAndSecondConditionNode(BinaryOp $binaryOp, $firstCondition, $secondCondition) : ?TwoNodeMatch
    {
        $this->validateCondition($firstCondition);
        $this->validateCondition($secondCondition);
        $firstCondition = $this->normalizeCondition($firstCondition);
        $secondCondition = $this->normalizeCondition($secondCondition);
        if ($firstCondition($binaryOp->left, $binaryOp->right) && $secondCondition($binaryOp->right, $binaryOp->left)) {
            return new TwoNodeMatch($binaryOp->left, $binaryOp->right);
        }
        if (!$firstCondition($binaryOp->right, $binaryOp->left)) {
            return null;
        }
        if (!$secondCondition($binaryOp->left, $binaryOp->right)) {
            return null;
        }
        return new TwoNodeMatch($binaryOp->right, $binaryOp->left);
    }
    public function inverseBinaryOp(BinaryOp $binaryOp) : ?BinaryOp
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
    public function invertCondition(BinaryOp $binaryOp) : ?BinaryOp
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
    /**
     * @return \PhpParser\Node\Expr\BinaryOp|\PhpParser\Node\Expr|\PhpParser\Node\Expr\BooleanNot
     */
    public function inverseNode(Expr $expr)
    {
        if ($expr instanceof BinaryOp) {
            $inversedBinaryOp = $this->assignAndBinaryMap->getInversed($expr);
            if ($inversedBinaryOp !== null) {
                return new $inversedBinaryOp($expr->left, $expr->right);
            }
        }
        if ($expr instanceof BooleanNot) {
            return $expr->expr;
        }
        return new BooleanNot($expr);
    }
    /**
     * @param callable(Node $firstNode, Node $secondNode=): bool|class-string<Node> $firstCondition
     */
    private function validateCondition($firstCondition) : void
    {
        if (\is_callable($firstCondition)) {
            return;
        }
        if (\is_a($firstCondition, Node::class, \true)) {
            return;
        }
        throw new ShouldNotHappenException();
    }
    /**
     * @param callable(Node $firstNode, Node $secondNode=): bool|class-string<Node> $condition
     * @return callable(Node $firstNode, Node $secondNode=): bool
     */
    private function normalizeCondition($condition) : callable
    {
        if (\is_callable($condition)) {
            return $condition;
        }
        return function (Node $node) use($condition) : bool {
            return \is_a($node, $condition, \true);
        };
    }
    /**
     * @return class-string<BinaryOp>|null
     */
    private function resolveInversedNodeClass(BinaryOp $binaryOp) : ?string
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
