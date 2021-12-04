<?php

declare (strict_types=1);
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
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\AssignAndBinaryMap
     */
    private $assignAndBinaryMap;
    public function __construct(\Rector\Core\PhpParser\Node\AssignAndBinaryMap $assignAndBinaryMap)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
    }
    /**
     * Tries to match left or right parts (xor),
     * returns null or match on first condition and then second condition. No matter what the origin order is.
     *
     * @param callable|class-string<Node> $firstCondition
     * @param callable|class-string<Node> $secondCondition
     */
    public function matchFirstAndSecondConditionNode(\PhpParser\Node\Expr\BinaryOp $binaryOp, $firstCondition, $secondCondition) : ?\Rector\Php71\ValueObject\TwoNodeMatch
    {
        $this->validateCondition($firstCondition);
        $this->validateCondition($secondCondition);
        $firstCondition = $this->normalizeCondition($firstCondition);
        $secondCondition = $this->normalizeCondition($secondCondition);
        if ($firstCondition($binaryOp->left, $binaryOp->right) && $secondCondition($binaryOp->right, $binaryOp->left)) {
            return new \Rector\Php71\ValueObject\TwoNodeMatch($binaryOp->left, $binaryOp->right);
        }
        if (!$firstCondition($binaryOp->right, $binaryOp->left)) {
            return null;
        }
        if (!$secondCondition($binaryOp->left, $binaryOp->right)) {
            return null;
        }
        return new \Rector\Php71\ValueObject\TwoNodeMatch($binaryOp->right, $binaryOp->left);
    }
    public function inverseBinaryOp(\PhpParser\Node\Expr\BinaryOp $binaryOp) : ?\PhpParser\Node\Expr\BinaryOp
    {
        // no nesting
        if ($binaryOp->left instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return null;
        }
        if ($binaryOp->right instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
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
    public function invertCondition(\PhpParser\Node\Expr\BinaryOp $binaryOp) : ?\PhpParser\Node\Expr\BinaryOp
    {
        // no nesting
        if ($binaryOp->left instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return null;
        }
        if ($binaryOp->right instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return null;
        }
        $inversedNodeClass = $this->resolveInversedNodeClass($binaryOp);
        if ($inversedNodeClass === null) {
            return null;
        }
        return new $inversedNodeClass($binaryOp->left, $binaryOp->right);
    }
    /**
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Expr\BinaryOp|\PhpParser\Node\Expr\BooleanNot
     */
    public function inverseNode(\PhpParser\Node\Expr $expr)
    {
        if ($expr instanceof \PhpParser\Node\Expr\BinaryOp) {
            $inversedBinaryOp = $this->assignAndBinaryMap->getInversed($expr);
            if ($inversedBinaryOp !== null) {
                return new $inversedBinaryOp($expr->left, $expr->right);
            }
        }
        if ($expr instanceof \PhpParser\Node\Expr\BooleanNot) {
            return $expr->expr;
        }
        return new \PhpParser\Node\Expr\BooleanNot($expr);
    }
    /**
     * @param callable|class-string<Node> $firstCondition
     */
    private function validateCondition($firstCondition) : void
    {
        if (\is_callable($firstCondition)) {
            return;
        }
        if (\is_a($firstCondition, \PhpParser\Node::class, \true)) {
            return;
        }
        throw new \Rector\Core\Exception\ShouldNotHappenException();
    }
    /**
     * @param callable|class-string<Node> $condition
     */
    private function normalizeCondition($condition) : callable
    {
        if (\is_callable($condition)) {
            return $condition;
        }
        return function (\PhpParser\Node $node) use($condition) : bool {
            return \is_a($node, $condition, \true);
        };
    }
    /**
     * @return class-string<BinaryOp>|null
     */
    private function resolveInversedNodeClass(\PhpParser\Node\Expr\BinaryOp $binaryOp) : ?string
    {
        $inversedNodeClass = $this->assignAndBinaryMap->getInversed($binaryOp);
        if ($inversedNodeClass !== null) {
            return $inversedNodeClass;
        }
        if ($binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return \PhpParser\Node\Expr\BinaryOp\BooleanAnd::class;
        }
        return null;
    }
}
