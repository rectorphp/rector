<?php

declare (strict_types=1);
namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface;
use Rector\Php80\NodeFactory\StrStartsWithFuncCallFactory;
use Rector\Php80\ValueObject\StrStartsWith;
use Rector\Php80\ValueObjectFactory\StrStartsWithFactory;
final class StrncmpMatchAndRefactor implements \Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface
{
    /**
     * @var string
     */
    private const FUNCTION_NAME = 'strncmp';
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Php80\ValueObjectFactory\StrStartsWithFactory
     */
    private $strStartsWithFactory;
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
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Php80\ValueObjectFactory\StrStartsWithFactory $strStartsWithFactory, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Php80\NodeFactory\StrStartsWithFuncCallFactory $strStartsWithFuncCallFactory, \Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->strStartsWithFactory = $strStartsWithFactory;
        $this->nodeComparator = $nodeComparator;
        $this->strStartsWithFuncCallFactory = $strStartsWithFuncCallFactory;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $binaryOp
     */
    public function match($binaryOp) : ?\Rector\Php80\ValueObject\StrStartsWith
    {
        $isPositive = $binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\Identical;
        if ($binaryOp->left instanceof \PhpParser\Node\Expr\FuncCall && $this->nodeNameResolver->isName($binaryOp->left, self::FUNCTION_NAME)) {
            return $this->strStartsWithFactory->createFromFuncCall($binaryOp->left, $isPositive);
        }
        if (!$binaryOp->right instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($binaryOp->right, self::FUNCTION_NAME)) {
            return null;
        }
        return $this->strStartsWithFactory->createFromFuncCall($binaryOp->right, $isPositive);
    }
    public function refactorStrStartsWith(\Rector\Php80\ValueObject\StrStartsWith $strStartsWith) : ?\PhpParser\Node
    {
        if ($this->isNeedleExprWithStrlen($strStartsWith)) {
            return $this->strStartsWithFuncCallFactory->createStrStartsWith($strStartsWith);
        }
        if ($this->isHardcodedStringWithLNumberLength($strStartsWith)) {
            return $this->strStartsWithFuncCallFactory->createStrStartsWith($strStartsWith);
        }
        return null;
    }
    private function isNeedleExprWithStrlen(\Rector\Php80\ValueObject\StrStartsWith $strStartsWith) : bool
    {
        $strncmpFuncCall = $strStartsWith->getFuncCall();
        $needleExpr = $strStartsWith->getNeedleExpr();
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($strncmpFuncCall->args, 2)) {
            return \false;
        }
        /** @var Arg $thirdArg */
        $thirdArg = $strncmpFuncCall->args[2];
        $secondArgumentValue = $thirdArg->value;
        if (!$secondArgumentValue instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($secondArgumentValue, 'strlen')) {
            return \false;
        }
        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $thirdArg->value;
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($strlenFuncCall->args, 0)) {
            return \false;
        }
        /** @var Arg $firstArg */
        $firstArg = $strlenFuncCall->args[0];
        $strlenArgumentValue = $firstArg->value;
        return $this->nodeComparator->areNodesEqual($needleExpr, $strlenArgumentValue);
    }
    private function isHardcodedStringWithLNumberLength(\Rector\Php80\ValueObject\StrStartsWith $strStartsWith) : bool
    {
        $strncmpFuncCall = $strStartsWith->getFuncCall();
        if (!$this->argsAnalyzer->isArgsInstanceInArgsPositions($strncmpFuncCall->args, [1, 2])) {
            return \false;
        }
        /** @var Arg $secondArg */
        $secondArg = $strncmpFuncCall->args[1];
        $hardcodedStringNeedle = $secondArg->value;
        if (!$hardcodedStringNeedle instanceof \PhpParser\Node\Scalar\String_) {
            return \false;
        }
        /** @var Arg $thirdArg */
        $thirdArg = $strncmpFuncCall->args[2];
        $lNumberLength = $thirdArg->value;
        if (!$lNumberLength instanceof \PhpParser\Node\Scalar\LNumber) {
            return \false;
        }
        return $lNumberLength->value === \strlen($hardcodedStringNeedle->value);
    }
}
