<?php

declare (strict_types=1);
namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
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
    public function __construct(NodeNameResolver $nodeNameResolver, ValueResolver $valueResolver, NodeComparator $nodeComparator, StrStartsWithFuncCallFactory $strStartsWithFuncCallFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->nodeComparator = $nodeComparator;
        $this->strStartsWithFuncCallFactory = $strStartsWithFuncCallFactory;
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
            $haystack = $funcCall->getArgs()[0]->value;
            return new StrStartsWith($funcCall, $haystack, $binaryOp->right, $isPositive);
        }
        if ($binaryOp->right instanceof FuncCall && $this->nodeNameResolver->isName($binaryOp->right, 'substr')) {
            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->right;
            $haystack = $funcCall->getArgs()[0]->value;
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
        if ($substrFuncCall->isFirstClassCallable()) {
            return \false;
        }
        $firstArg = $substrFuncCall->getArgs()[1];
        if (!$this->valueResolver->isValue($firstArg->value, 0)) {
            return \false;
        }
        $secondFuncCallArgValue = $substrFuncCall->getArgs()[2]->value;
        if (!$secondFuncCallArgValue instanceof FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($secondFuncCallArgValue, 'strlen')) {
            return \false;
        }
        $strlenFuncCall = $secondFuncCallArgValue;
        $needleExpr = $strlenFuncCall->getArgs()[0]->value;
        $comparedNeedleExpr = $strStartsWith->getNeedleExpr();
        return $this->nodeComparator->areNodesEqual($needleExpr, $comparedNeedleExpr);
    }
    private function isHardcodedStringWithLNumberLength(StrStartsWith $strStartsWith) : bool
    {
        $substrFuncCall = $strStartsWith->getFuncCall();
        if ($substrFuncCall->isFirstClassCallable()) {
            return \false;
        }
        $secondArg = $substrFuncCall->getArgs()[1];
        if (!$this->valueResolver->isValue($secondArg->value, 0)) {
            return \false;
        }
        $hardcodedStringNeedle = $strStartsWith->getNeedleExpr();
        if (!$hardcodedStringNeedle instanceof String_) {
            return \false;
        }
        if (\count($substrFuncCall->getArgs()) < 3) {
            return \false;
        }
        $lNumberLength = $substrFuncCall->getArgs()[2]->value;
        if (!$lNumberLength instanceof LNumber) {
            return \false;
        }
        return $lNumberLength->value === \strlen($hardcodedStringNeedle->value);
    }
}
