<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
final class StrStartsWith
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
    private $haystackExpr;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $needleExpr;
    /**
     * @readonly
     * @var bool
     */
    private $isPositive;
    public function __construct(FuncCall $funcCall, Expr $haystackExpr, Expr $needleExpr, bool $isPositive)
    {
        $this->funcCall = $funcCall;
        $this->haystackExpr = $haystackExpr;
        $this->needleExpr = $needleExpr;
        $this->isPositive = $isPositive;
    }
    public function getFuncCall() : FuncCall
    {
        return $this->funcCall;
    }
    public function getHaystackExpr() : Expr
    {
        return $this->haystackExpr;
    }
    public function isPositive() : bool
    {
        return $this->isPositive;
    }
    public function getNeedleExpr() : Expr
    {
        return $this->needleExpr;
    }
}
