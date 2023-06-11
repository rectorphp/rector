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
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface;
use Rector\Php80\NodeFactory\StrStartsWithFuncCallFactory;
use Rector\Php80\ValueObject\StrStartsWith;
use Rector\Php80\ValueObjectFactory\StrStartsWithFactory;
final class StrncmpMatchAndRefactor implements StrStartWithMatchAndRefactorInterface
{
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
     * @var string
     */
    private const FUNCTION_NAME = 'strncmp';
    public function __construct(NodeNameResolver $nodeNameResolver, StrStartsWithFactory $strStartsWithFactory, NodeComparator $nodeComparator, StrStartsWithFuncCallFactory $strStartsWithFuncCallFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->strStartsWithFactory = $strStartsWithFactory;
        $this->nodeComparator = $nodeComparator;
        $this->strStartsWithFuncCallFactory = $strStartsWithFuncCallFactory;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\Equal|\PhpParser\Node\Expr\BinaryOp\NotEqual $binaryOp
     */
    public function match($binaryOp) : ?StrStartsWith
    {
        $isPositive = $binaryOp instanceof Identical || $binaryOp instanceof Equal;
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
        if ($strncmpFuncCall->isFirstClassCallable()) {
            return \false;
        }
        if (\count($strncmpFuncCall->getArgs()) < 2) {
            return \false;
        }
        $thirdArg = $strncmpFuncCall->getArgs()[2];
        $thirdArgExpr = $thirdArg->value;
        if (!$thirdArgExpr instanceof FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($thirdArgExpr, 'strlen')) {
            return \false;
        }
        $strlenFuncCall = $thirdArgExpr;
        $strlenExpr = $strlenFuncCall->getArgs()[0]->value;
        return $this->nodeComparator->areNodesEqual($needleExpr, $strlenExpr);
    }
    private function isHardcodedStringWithLNumberLength(StrStartsWith $strStartsWith) : bool
    {
        $strncmpFuncCall = $strStartsWith->getFuncCall();
        if (\count($strncmpFuncCall->getArgs()) < 2) {
            return \false;
        }
        $hardcodedStringNeedle = $strncmpFuncCall->getArgs()[1]->value;
        if (!$hardcodedStringNeedle instanceof String_) {
            return \false;
        }
        $lNumberLength = $strncmpFuncCall->getArgs()[2]->value;
        if (!$lNumberLength instanceof LNumber) {
            return \false;
        }
        return $lNumberLength->value === \strlen($hardcodedStringNeedle->value);
    }
}
