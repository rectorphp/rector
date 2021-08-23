<?php

declare (strict_types=1);
namespace Rector\Nette\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
final class FuncCallAndExpr
{
    /**
     * @var \PhpParser\Node\Expr\FuncCall
     */
    private $funcCall;
    /**
     * @var \PhpParser\Node\Expr
     */
    private $expr;
    public function __construct(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr $expr)
    {
        $this->funcCall = $funcCall;
        $this->expr = $expr;
    }
    public function getFuncCall() : \PhpParser\Node\Expr\FuncCall
    {
        return $this->funcCall;
    }
    public function getExpr() : \PhpParser\Node\Expr
    {
        return $this->expr;
    }
}
