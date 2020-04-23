<?php

declare(strict_types=1);

namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Php80\ValueObject\StrposFuncCallToZero;

final class StrposMatchAndRefactor extends AbstractMatchAndRefactor
{
    /**
     * @param Identical|NotIdentical $binaryOp
     */
    public function match(BinaryOp $binaryOp): ?StrposFuncCallToZero
    {
        if ($this->isFuncCallName($binaryOp->left, 'strpos')) {
            if (! $this->valueResolver->isValue($binaryOp->right, 0)) {
                return null;
            }

            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->left;
            return new StrposFuncCallToZero($funcCall);
        }

        if ($this->isFuncCallName($binaryOp->right, 'strpos')) {
            if (! $this->valueResolver->isValue($binaryOp->left, 0)) {
                return null;
            }

            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->right;
            return new StrposFuncCallToZero($funcCall);
        }

        return null;
    }

    public function refactor(StrposFuncCallToZero $strposFuncCallToZero): FuncCall
    {
        $strposFuncCall = $strposFuncCallToZero->getStrposFuncCall();
        $strposFuncCall->name = new Name('str_starts_with');
        return $strposFuncCall;
    }
}
