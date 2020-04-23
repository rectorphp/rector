<?php

declare(strict_types=1);

namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface;
use Rector\Php80\ValueObject\StrStartsWithValueObject;

final class StrncmpMatchAndRefactor extends AbstractMatchAndRefactor implements StrStartWithMatchAndRefactorInterface
{
    /**
     * @var string
     */
    private const FUNCTION_NAME = 'strncmp';

    /**
     * @param Identical|NotIdentical $binaryOp
     */
    public function match(BinaryOp $binaryOp): ?StrStartsWithValueObject
    {
        $isPositive = $binaryOp instanceof Identical;

        if ($this->isFuncCallName($binaryOp->left, self::FUNCTION_NAME)) {
            return $this->createStrStartsWithValueObjectFromFuncCall($binaryOp->left, $isPositive);
        }

        if ($this->isFuncCallName($binaryOp->right, self::FUNCTION_NAME)) {
            return $this->createStrStartsWithValueObjectFromFuncCall($binaryOp->right, $isPositive);
        }

        return null;
    }

    public function refactor(StrStartsWithValueObject $strStartsWithValueObject): ?Node
    {
        $strncmpFuncCall = $strStartsWithValueObject->getFuncCall();
        $needleExpr = $strStartsWithValueObject->getNeedleExpr();

        if (! $this->isFuncCallName($strncmpFuncCall->args[2]->value, 'strlen')) {
            return null;
        }

        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $strncmpFuncCall->args[2]->value;
        $strlenArgumentValue = $strlenFuncCall->args[0]->value;

        if (! $this->betterStandardPrinter->areNodesEqual($needleExpr, $strlenArgumentValue)) {
            return null;
        }

        return $this->createStrStartsWith($strStartsWithValueObject);
    }
}
