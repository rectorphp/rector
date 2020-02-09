<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;

final class LivingCodeManipulator
{
    /**
     * @param Node|int|string|null $expr
     * @return Expr[]
     */
    public function keepLivingCodeFromExpr($expr): array
    {
        if (! $expr instanceof Node ||
            $expr instanceof Closure ||
            $expr instanceof Name ||
            $expr instanceof Identifier ||
            $expr instanceof Scalar ||
            $expr instanceof ConstFetch
        ) {
            return [];
        }

        if ($this->isNestedExpr($expr)) {
            return $this->keepLivingCodeFromExpr($expr->expr);
        }

        if ($expr instanceof Variable) {
            return $this->keepLivingCodeFromExpr($expr->name);
        }

        if ($expr instanceof PropertyFetch) {
            return array_merge(
                $this->keepLivingCodeFromExpr($expr->var),
                $this->keepLivingCodeFromExpr($expr->name)
            );
        }

        if ($expr instanceof ArrayDimFetch) {
            return array_merge(
                $this->keepLivingCodeFromExpr($expr->var),
                $this->keepLivingCodeFromExpr($expr->dim)
            );
        }

        if ($expr instanceof ClassConstFetch ||
            $expr instanceof StaticPropertyFetch) {
            return array_merge(
                $this->keepLivingCodeFromExpr($expr->class),
                $this->keepLivingCodeFromExpr($expr->name)
            );
        }

        if ($this->isBinaryOpWithoutChange($expr)) {
            return array_merge(
                $this->keepLivingCodeFromExpr($expr->left),
                $this->keepLivingCodeFromExpr($expr->right)
            );
        }

        if ($expr instanceof Instanceof_) {
            return array_merge(
                $this->keepLivingCodeFromExpr($expr->expr),
                $this->keepLivingCodeFromExpr($expr->class)
            );
        }

        if ($expr instanceof Isset_) {
            return array_merge(...array_map(function (Expr $expr): array {
                return $this->keepLivingCodeFromExpr($expr);
            }, $expr->vars));
        }

        return [$expr];
    }

    private function isBinaryOpWithoutChange($expr): bool
    {
        return $expr instanceof BinaryOp
            && ! (
                $expr instanceof LogicalAnd ||
                $expr instanceof BooleanAnd ||
                $expr instanceof LogicalOr ||
                $expr instanceof BooleanOr ||
                $expr instanceof Coalesce
            );
    }

    private function isNestedExpr($expr): bool
    {
        return $expr instanceof Cast ||
            $expr instanceof Empty_ ||
            $expr instanceof UnaryMinus ||
            $expr instanceof UnaryPlus ||
            $expr instanceof BitwiseNot ||
            $expr instanceof BooleanNot ||
            $expr instanceof Clone_;
    }
}
