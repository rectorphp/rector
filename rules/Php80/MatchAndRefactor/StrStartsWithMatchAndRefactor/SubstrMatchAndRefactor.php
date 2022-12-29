<?php

declare (strict_types=1);
namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface;
use Rector\Php80\NodeFactory\StrStartsWithFuncCallFactory;
use Rector\Php80\ValueObject\StrStartsWith;
final class SubstrMatchAndRefactor implements StrStartWithMatchAndRefactorInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Php80\NodeFactory\StrStartsWithFuncCallFactory
     */
    private $strStartsWithFuncCallFactory;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, ValueResolver $valueResolver, NodeComparator $nodeComparator, StrStartsWithFuncCallFactory $strStartsWithFuncCallFactory, ArgsAnalyzer $argsAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->nodeComparator = $nodeComparator;
        $this->strStartsWithFuncCallFactory = $strStartsWithFuncCallFactory;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\Equal|\PhpParser\Node\Expr\BinaryOp\NotEqual $binaryOp
     */
    public function match($binaryOp) : ?StrStartsWith
    {
        $isPositive = $binaryOp instanceof Identical || $binaryOp instanceof Equal;
        if ($binaryOp->left instanceof FuncCall && $this->nodeNameResolver->isName($binaryOp->left, 'substr')) {
            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->left;
            if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($funcCall->args, 0)) {
                return null;
            }
            /** @var Arg $arg */
            $arg = $funcCall->args[0];
            $haystack = $arg->value;
            return new StrStartsWith($funcCall, $haystack, $binaryOp->right, $isPositive);
        }
        if ($binaryOp->right instanceof FuncCall && $this->nodeNameResolver->isName($binaryOp->right, 'substr')) {
            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->right;
            if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($funcCall->args, 0)) {
                return null;
            }
            /** @var Arg $arg */
            $arg = $funcCall->args[0];
            $haystack = $arg->value;
            return new StrStartsWith($funcCall, $haystack, $binaryOp->left, $isPositive);
        }
        return null;
    }
    public function refactorStrStartsWith(StrStartsWith $strStartsWith) : ?Node
    {
        if ($this->isStrlenWithNeedleExpr($strStartsWith)) {
            return $this->strStartsWithFuncCallFactory->createStrStartsWith($strStartsWith);
        }
        if ($this->isHardcodedStringWithLNumberLength($strStartsWith)) {
            return $this->strStartsWithFuncCallFactory->createStrStartsWith($strStartsWith);
        }
        return null;
    }
    private function isStrlenWithNeedleExpr(StrStartsWith $strStartsWith) : bool
    {
        $substrFuncCall = $strStartsWith->getFuncCall();
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($substrFuncCall->args, 1)) {
            return \false;
        }
        /** @var Arg $arg1 */
        $arg1 = $substrFuncCall->args[1];
        if (!$this->valueResolver->isValue($arg1->value, 0)) {
            return \false;
        }
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($substrFuncCall->args, 2)) {
            return \false;
        }
        /** @var Arg $arg2 */
        $arg2 = $substrFuncCall->args[2];
        $secondFuncCallArgValue = $arg2->value;
        if (!$secondFuncCallArgValue instanceof FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($secondFuncCallArgValue, 'strlen')) {
            return \false;
        }
        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $arg2->value;
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($strlenFuncCall->args, 0)) {
            return \false;
        }
        /** @var Arg $arg0 */
        $arg0 = $strlenFuncCall->args[0];
        $needleExpr = $arg0->value;
        $comparedNeedleExpr = $strStartsWith->getNeedleExpr();
        return $this->nodeComparator->areNodesEqual($needleExpr, $comparedNeedleExpr);
    }
    private function isHardcodedStringWithLNumberLength(StrStartsWith $strStartsWith) : bool
    {
        $substrFuncCall = $strStartsWith->getFuncCall();
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($substrFuncCall->args, 1)) {
            return \false;
        }
        /** @var Arg $arg1 */
        $arg1 = $substrFuncCall->args[1];
        if (!$this->valueResolver->isValue($arg1->value, 0)) {
            return \false;
        }
        $hardcodedStringNeedle = $strStartsWith->getNeedleExpr();
        if (!$hardcodedStringNeedle instanceof String_) {
            return \false;
        }
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($substrFuncCall->args, 2)) {
            return \false;
        }
        /** @var Arg $arg2 */
        $arg2 = $substrFuncCall->args[2];
        $lNumberLength = $arg2->value;
        if (!$lNumberLength instanceof LNumber) {
            return \false;
        }
        return $lNumberLength->value === \strlen($hardcodedStringNeedle->value);
    }
}
