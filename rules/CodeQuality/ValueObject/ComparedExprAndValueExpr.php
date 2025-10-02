<?php

declare (strict_types=1);
namespace Rector\CodeQuality\ValueObject;

use PhpParser\Node\Expr;
final class ComparedExprAndValueExpr
{
    /**
     * @readonly
     */
    private Expr $comparedExpr;
    /**
     * @readonly
     */
    private Expr $valueExpr;
    public function __construct(Expr $comparedExpr, Expr $valueExpr)
    {
        $this->comparedExpr = $comparedExpr;
        $this->valueExpr = $valueExpr;
    }
    public function getComparedExpr(): Expr
    {
        return $this->comparedExpr;
    }
    public function getValueExpr(): Expr
    {
        return $this->valueExpr;
    }
}
