<?php

declare (strict_types=1);
namespace Rector\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
final class FuncCallAndExpr
{
    /**
     * @readonly
     */
    private FuncCall $funcCall;
    /**
     * @readonly
     */
    private Expr $expr;
    public function __construct(FuncCall $funcCall, Expr $expr)
    {
        $this->funcCall = $funcCall;
        $this->expr = $expr;
    }
    public function getFuncCall() : FuncCall
    {
        return $this->funcCall;
    }
    public function getExpr() : Expr
    {
        return $this->expr;
    }
}
