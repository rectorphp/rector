<?php

declare (strict_types=1);
namespace Rector\Nette\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
final class FuncCallAndExpr
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\FuncCall
     */
    private $funcCall;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $expr;
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
