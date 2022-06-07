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
final class StrncmpMatchAndRefactor implements StrStartWithMatchAndRefactorInterface
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
    public function __construct(NodeNameResolver $nodeNameResolver, StrStartsWithFactory $strStartsWithFactory, NodeComparator $nodeComparator, StrStartsWithFuncCallFactory $strStartsWithFuncCallFactory, ArgsAnalyzer $argsAnalyzer)
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
    public function match($binaryOp) : ?StrStartsWith
    {
        $isPositive = $binaryOp instanceof Identical;
        if ($binaryOp->left instanceof FuncCall && $this->nodeNameResolver->isName($binaryOp->left, self::FUNCTION_NAME)) {
            return $this->strStartsWithFactory->createFromFuncCall($binaryOp->left, $isPositive);
        }
        if (!$binaryOp->right instanceof FuncCall) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($binaryOp->right, self::FUNCTION_NAME)) {
            return null;
        }
        return $this->strStartsWithFactory->createFromFuncCall($binaryOp->right, $isPositive);
    }
    public function refactorStrStartsWith(StrStartsWith $strStartsWith) : ?Node
    {
        if ($this->isNeedleExprWithStrlen($strStartsWith)) {
            return $this->strStartsWithFuncCallFactory->createStrStartsWith($strStartsWith);
        }
        if ($this->isHardcodedStringWithLNumberLength($strStartsWith)) {
            return $this->strStartsWithFuncCallFactory->createStrStartsWith($strStartsWith);
        }
        return null;
    }
    private function isNeedleExprWithStrlen(StrStartsWith $strStartsWith) : bool
    {
        $strncmpFuncCall = $strStartsWith->getFuncCall();
        $needleExpr = $strStartsWith->getNeedleExpr();
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($strncmpFuncCall->args, 2)) {
            return \false;
        }
        /** @var Arg $thirdArg */
        $thirdArg = $strncmpFuncCall->args[2];
        $secondArgumentValue = $thirdArg->value;
        if (!$secondArgumentValue instanceof FuncCall) {
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
    private function isHardcodedStringWithLNumberLength(StrStartsWith $strStartsWith) : bool
    {
        $strncmpFuncCall = $strStartsWith->getFuncCall();
        if (!$this->argsAnalyzer->isArgsInstanceInArgsPositions($strncmpFuncCall->args, [1, 2])) {
            return \false;
        }
        /** @var Arg $secondArg */
        $secondArg = $strncmpFuncCall->args[1];
        $hardcodedStringNeedle = $secondArg->value;
        if (!$hardcodedStringNeedle instanceof String_) {
            return \false;
        }
        /** @var Arg $thirdArg */
        $thirdArg = $strncmpFuncCall->args[2];
        $lNumberLength = $thirdArg->value;
        if (!$lNumberLength instanceof LNumber) {
            return \false;
        }
        return $lNumberLength->value === \strlen($hardcodedStringNeedle->value);
    }
}
