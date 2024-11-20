<?php

declare (strict_types=1);
namespace Rector\Php71\ValueObject;

use PhpParser\Node\Expr;
final class TwoNodeMatch
{
    /**
     * @readonly
     */
    private Expr $firstExpr;
    /**
     * @readonly
     */
    private Expr $secondExpr;
    public function __construct(Expr $firstExpr, Expr $secondExpr)
    {
        $this->firstExpr = $firstExpr;
        $this->secondExpr = $secondExpr;
    }
    public function getFirstExpr() : Expr
    {
        return $this->firstExpr;
    }
    public function getSecondExpr() : Expr
    {
        return $this->secondExpr;
    }
}
