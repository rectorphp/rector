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
use Rector\Php80\ValueObject\StrncmpFuncCallToHaystack;

final class StrncmpMatchAndRefactor extends AbstractMatchAndRefactor
{
    /**
     * @var string
     */
    private const FUNCTION_NAME = 'strncmp';

    /**
     * @param Identical|NotIdentical $binaryOp
     */
    public function match(BinaryOp $binaryOp): ?StrncmpFuncCallToHaystack
    {
        $isPositive = $binaryOp instanceof Identical;

        if ($this->isFuncCallName($binaryOp->left, self::FUNCTION_NAME)) {
            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->left;
            return new StrncmpFuncCallToHaystack($funcCall, $isPositive);
        }

        if ($this->isFuncCallName($binaryOp->right, self::FUNCTION_NAME)) {
            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->right;
            return new StrncmpFuncCallToHaystack($funcCall, $isPositive);
        }

        return null;
    }

    public function refactor(StrncmpFuncCallToHaystack $strncmpFuncCallToHaystack): ?Node
    {
        $strncmpFuncCall = $strncmpFuncCallToHaystack->getStrncmpFuncCall();

        $needleExpr = $strncmpFuncCall->args[1]->value;

        if (! $this->isFuncCallName($strncmpFuncCall->args[2]->value, 'strlen')) {
            return null;
        }

        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $strncmpFuncCall->args[2]->value;
        $strlenArgumentValue = $strlenFuncCall->args[0]->value;

        if (! $this->betterStandardPrinter->areNodesEqual($needleExpr, $strlenArgumentValue)) {
            return null;
        }

        $strStartsWith = $this->createStrStartsWith($strncmpFuncCall, $needleExpr);
        if ($strncmpFuncCallToHaystack->isPositive()) {
            return $strStartsWith;
        }

        return new BooleanNot($strStartsWith);
    }

    private function createStrStartsWith(FuncCall $strncmpFuncCall, Expr $needleExpr): FuncCall
    {
        $haystackExpr = $strncmpFuncCall->args[0]->value;
        $args = [new Arg($haystackExpr), new Arg($needleExpr)];

        return new FuncCall(new Name('str_starts_with'), $args);
    }
}
