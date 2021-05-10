<?php

declare(strict_types=1);

namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
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

    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private StrStartsWithFactory $strStartsWithFactory,
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

        if ($binaryOp->left instanceof FuncCall && $this->nodeNameResolver->isName(
            $binaryOp->left,
            self::FUNCTION_NAME
        )) {
            return $this->strStartsWithFactory->createFromFuncCall($binaryOp->left, $isPositive);
        }
        if (! $binaryOp->right instanceof FuncCall) {
            return null;
        }
        if (! $this->nodeNameResolver->isName($binaryOp->right, self::FUNCTION_NAME)) {
            return null;
        }
        return $this->strStartsWithFactory->createFromFuncCall($binaryOp->right, $isPositive);
    }

    public function refactorStrStartsWith(StrStartsWith $strStartsWith): ?Node
    {
        $strncmpFuncCall = $strStartsWith->getFuncCall();
        $needleExpr = $strStartsWith->getNeedleExpr();

        $secondArgumentValue = $strncmpFuncCall->args[2]->value;
        if (! $secondArgumentValue instanceof FuncCall) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($secondArgumentValue, 'strlen')) {
            return null;
        }

        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $strncmpFuncCall->args[2]->value;
        $strlenArgumentValue = $strlenFuncCall->args[0]->value;

        if (! $this->nodeComparator->areNodesEqual($needleExpr, $strlenArgumentValue)) {
            return null;
        }

        return $this->strStartsWithFuncCallFactory->createStrStartsWith($strStartsWith);
    }
}
