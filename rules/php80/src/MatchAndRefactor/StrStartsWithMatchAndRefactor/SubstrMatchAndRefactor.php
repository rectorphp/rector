<?php

declare(strict_types=1);

namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Php80\ValueObject\SubstrFuncCallToHaystack;

final class SubstrMatchAndRefactor extends AbstractMatchAndRefactor
{
    /**
     * @param Identical|NotIdentical $binaryOp
     */
    public function match(BinaryOp $binaryOp): ?SubstrFuncCallToHaystack
    {
        $isPositive = $binaryOp instanceof Identical;

        if ($this->isFuncCallName($binaryOp->left, 'substr')) {
            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->left;
            return new SubstrFuncCallToHaystack($funcCall, $binaryOp->right, $isPositive);
        }

        if ($this->isFuncCallName($binaryOp->right, 'substr')) {
            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->right;
            return new SubstrFuncCallToHaystack($funcCall, $binaryOp->left, $isPositive);
        }

        return null;
    }

    public function refactor(SubstrFuncCallToHaystack $substrFuncCallToHaystack): ?Node
    {
        $substrFuncCall = $substrFuncCallToHaystack->getSubstrFuncCall();
        if (! $this->valueResolver->isValue($substrFuncCall->args[1]->value, 0)) {
            return null;
        }

        if (! $this->isFuncCallName($substrFuncCall->args[2]->value, 'strlen')) {
            return null;
        }

        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $substrFuncCall->args[2]->value;
        $needleExpr = $strlenFuncCall->args[0]->value;

        $comparedExpr = $substrFuncCallToHaystack->getHaystackExpr();
        if (! $this->betterStandardPrinter->areNodesEqual($needleExpr, $comparedExpr)) {
            return null;
        }

        $strStartsWith = $this->createStrStartsWith($substrFuncCall, $needleExpr);
        if ($substrFuncCallToHaystack->isPositive()) {
            return $strStartsWith;
        }

        return new BooleanNot($strStartsWith);
    }

    private function createStrStartsWith(FuncCall $substrFuncCall, Expr $needleExpr): FuncCall
    {
        $haystackExpr = $substrFuncCall->args[0]->value;

        $args = [new Arg($haystackExpr), new Arg($needleExpr)];

        return new FuncCall(new Name('str_starts_with'), $args);
    }
}
