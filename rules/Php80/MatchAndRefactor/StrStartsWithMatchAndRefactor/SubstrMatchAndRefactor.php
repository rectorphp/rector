<?php

declare(strict_types=1);

namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface;
use Rector\Php80\NodeFactory\StrStartsWithFuncCallFactory;
use Rector\Php80\ValueObject\StrStartsWith;

final class SubstrMatchAndRefactor implements StrStartWithMatchAndRefactorInterface
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private ValueResolver $valueResolver,
        private NodeComparator $nodeComparator,
        private StrStartsWithFuncCallFactory $strStartsWithFuncCallFactory
    ) {
    }

    /**
     * @param Identical|NotIdentical $binaryOp
     */
    public function match(BinaryOp $binaryOp): ?StrStartsWith
    {
        $isPositive = $binaryOp instanceof Identical;

        if ($binaryOp->left instanceof FuncCall && $this->nodeNameResolver->isName($binaryOp->left, 'substr')) {
            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->left;
            $haystack = $funcCall->args[0]->value;

            return new StrStartsWith($funcCall, $haystack, $binaryOp->right, $isPositive);
        }

        if ($binaryOp->right instanceof FuncCall && $this->nodeNameResolver->isName($binaryOp->right, 'substr')) {
            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->right;
            $haystack = $funcCall->args[0]->value;

            return new StrStartsWith($funcCall, $haystack, $binaryOp->left, $isPositive);
        }

        return null;
    }

    public function refactorStrStartsWith(StrStartsWith $strStartsWith): ?Node
    {
        if ($this->isStrlenWithNeedleExpr($strStartsWith)) {
            return $this->strStartsWithFuncCallFactory->createStrStartsWith($strStartsWith);
        }

        if ($this->isHardcodedStringWithLNumberLength($strStartsWith)) {
            return $this->strStartsWithFuncCallFactory->createStrStartsWith($strStartsWith);
        }

        return null;
    }

    private function isStrlenWithNeedleExpr(StrStartsWith $strStartsWith): bool
    {
        $substrFuncCall = $strStartsWith->getFuncCall();
        if (! $this->valueResolver->isValue($substrFuncCall->args[1]->value, 0)) {
            return false;
        }

        $secondFuncCallArgValue = $substrFuncCall->args[2]->value;
        if (! $secondFuncCallArgValue instanceof FuncCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($secondFuncCallArgValue, 'strlen')) {
            return false;
        }

        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $substrFuncCall->args[2]->value;
        $needleExpr = $strlenFuncCall->args[0]->value;

        $comparedNeedleExpr = $strStartsWith->getNeedleExpr();
        return $this->nodeComparator->areNodesEqual($needleExpr, $comparedNeedleExpr);
    }

    private function isHardcodedStringWithLNumberLength(StrStartsWith $strStartsWith): bool
    {
        $substrFuncCall = $strStartsWith->getFuncCall();
        if (! $this->valueResolver->isValue($substrFuncCall->args[1]->value, 0)) {
            return false;
        }

        $hardcodedStringNeedle = $strStartsWith->getNeedleExpr();
        if (! $hardcodedStringNeedle instanceof String_) {
            return false;
        }

        $lNumberLength = $substrFuncCall->args[2]->value;
        if (! $lNumberLength instanceof LNumber) {
            return false;
        }

        return $lNumberLength->value === strlen($hardcodedStringNeedle->value);
    }
}
