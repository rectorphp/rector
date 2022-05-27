<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\NodeTransformer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
final class ConditionInverter
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function createInvertedCondition(Expr $expr) : Expr
    {
        // inverse condition
        if ($expr instanceof BinaryOp) {
            $inversedCondition = $this->binaryOpManipulator->invertCondition($expr);
            if (!$inversedCondition instanceof BinaryOp) {
                return new BooleanNot($expr);
            }
            if ($inversedCondition instanceof BooleanAnd) {
                return new BooleanNot($expr);
            }
            return $inversedCondition;
        }
        if ($expr instanceof BooleanNot) {
            return $expr->expr;
        }
        return new BooleanNot($expr);
    }
}
