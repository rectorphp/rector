<?php

declare (strict_types=1);
namespace Rector\Php71;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use Rector\NodeManipulator\BinaryOpManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Rector\PhpParser\Comparing\NodeComparator;
final class IsArrayAndDualCheckToAble
{
    /**
     * @readonly
     */
    private BinaryOpManipulator $binaryOpManipulator;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private NodeComparator $nodeComparator;
    public function __construct(BinaryOpManipulator $binaryOpManipulator, NodeNameResolver $nodeNameResolver, NodeComparator $nodeComparator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
    }
    public function processBooleanOr(BooleanOr $booleanOr, string $type, string $newMethodName): ?FuncCall
    {
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($booleanOr, Instanceof_::class, FuncCall::class);
        if (!$twoNodeMatch instanceof TwoNodeMatch) {
            return null;
        }
        /** @var Instanceof_ $instanceofExpr */
        $instanceofExpr = $twoNodeMatch->getFirstExpr();
        /** @var FuncCall $funcCallExpr */
        $funcCallExpr = $twoNodeMatch->getSecondExpr();
        $instanceOfClass = $instanceofExpr->class;
        if ($instanceOfClass instanceof Expr) {
            return null;
        }
        if ((string) $instanceOfClass !== $type) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($funcCallExpr, 'is_array')) {
            return null;
        }
        if ($funcCallExpr->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $funcCallExpr->getArg('value', 0);
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $firstExprNode = $firstArg->value;
        if (!$this->nodeComparator->areNodesEqual($instanceofExpr->expr, $firstExprNode)) {
            return null;
        }
        // the expr is evaluated twice in the original, e.g. a call can return different values
        if (!$this->isSideEffectFreeExpr($firstExprNode)) {
            return null;
        }
        // both use same Expr
        return new FuncCall(new Name($newMethodName), [new Arg($firstExprNode)]);
    }
    private function isSideEffectFreeExpr(Expr $expr): bool
    {
        if ($expr instanceof Variable) {
            return \true;
        }
        if ($expr instanceof PropertyFetch) {
            return !$expr->name instanceof Expr && $this->isSideEffectFreeExpr($expr->var);
        }
        if ($expr instanceof StaticPropertyFetch) {
            return !$expr->class instanceof Expr;
        }
        if ($expr instanceof ArrayDimFetch) {
            if ($expr->dim instanceof Expr && !$expr->dim instanceof Scalar && !$this->isSideEffectFreeExpr($expr->dim)) {
                return \false;
            }
            return $this->isSideEffectFreeExpr($expr->var);
        }
        return \false;
    }
}
