<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\NodeManipulator;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Coalesce;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\LogicalOr;
use RectorPrefix20220606\PhpParser\Node\Expr\BitwiseNot;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Clone_;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Empty_;
use RectorPrefix20220606\PhpParser\Node\Expr\Instanceof_;
use RectorPrefix20220606\PhpParser\Node\Expr\Isset_;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticPropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\UnaryMinus;
use RectorPrefix20220606\PhpParser\Node\Expr\UnaryPlus;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
final class LivingCodeManipulator
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return Expr[]|mixed[]
     * @param \PhpParser\Node|int|string|null $expr
     */
    public function keepLivingCodeFromExpr($expr) : array
    {
        if (!$expr instanceof Expr) {
            return [];
        }
        if ($expr instanceof Closure || $expr instanceof Scalar || $expr instanceof ConstFetch) {
            return [];
        }
        if ($this->isNestedExpr($expr)) {
            return $this->keepLivingCodeFromExpr($expr->expr);
        }
        if ($expr instanceof Variable) {
            return $this->keepLivingCodeFromExpr($expr->name);
        }
        if ($expr instanceof PropertyFetch) {
            return \array_merge($this->keepLivingCodeFromExpr($expr->var), $this->keepLivingCodeFromExpr($expr->name));
        }
        if ($expr instanceof ArrayDimFetch) {
            $type = $this->nodeTypeResolver->getType($expr->var);
            if ($type instanceof ObjectType) {
                $objectType = new ObjectType('ArrayAccess');
                if ($objectType->isSuperTypeOf($type)->yes()) {
                    return [$expr];
                }
            }
            return \array_merge($this->keepLivingCodeFromExpr($expr->var), $this->keepLivingCodeFromExpr($expr->dim));
        }
        if ($expr instanceof ClassConstFetch || $expr instanceof StaticPropertyFetch) {
            return \array_merge($this->keepLivingCodeFromExpr($expr->class), $this->keepLivingCodeFromExpr($expr->name));
        }
        if ($this->isBinaryOpWithoutChange($expr)) {
            /** @var BinaryOp $binaryOp */
            $binaryOp = $expr;
            return $this->processBinary($binaryOp);
        }
        if ($expr instanceof Instanceof_) {
            return \array_merge($this->keepLivingCodeFromExpr($expr->expr), $this->keepLivingCodeFromExpr($expr->class));
        }
        if ($expr instanceof Isset_) {
            return $this->processIsset($expr);
        }
        return [$expr];
    }
    private function isNestedExpr(Expr $expr) : bool
    {
        return $expr instanceof Cast || $expr instanceof Empty_ || $expr instanceof UnaryMinus || $expr instanceof UnaryPlus || $expr instanceof BitwiseNot || $expr instanceof BooleanNot || $expr instanceof Clone_;
    }
    private function isBinaryOpWithoutChange(Expr $expr) : bool
    {
        if (!$expr instanceof BinaryOp) {
            return \false;
        }
        return !($expr instanceof LogicalAnd || $expr instanceof BooleanAnd || $expr instanceof LogicalOr || $expr instanceof BooleanOr || $expr instanceof Coalesce);
    }
    /**
     * @return Expr[]
     */
    private function processBinary(BinaryOp $binaryOp) : array
    {
        return \array_merge($this->keepLivingCodeFromExpr($binaryOp->left), $this->keepLivingCodeFromExpr($binaryOp->right));
    }
    /**
     * @return mixed[]
     */
    private function processIsset(Isset_ $isset) : array
    {
        $livingExprs = [];
        foreach ($isset->vars as $expr) {
            $livingExprs = \array_merge($livingExprs, $this->keepLivingCodeFromExpr($expr));
        }
        return $livingExprs;
    }
}
