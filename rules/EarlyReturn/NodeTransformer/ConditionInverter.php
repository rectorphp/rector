<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\EarlyReturn\NodeTransformer;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\Rector\Core\NodeManipulator\BinaryOpManipulator;
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
