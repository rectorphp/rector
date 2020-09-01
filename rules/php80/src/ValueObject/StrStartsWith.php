<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;

final class StrStartsWith
{
    /**
     * @var bool
     */
    private $isPositive = false;

    /**
     * @var FuncCall
     */
    private $funcCall;

    /**
     * @var Expr
     */
    private $haystackExpr;

    /**
     * @var Expr
     */
    private $needleExpr;

    public function __construct(FuncCall $funcCall, Expr $haystackExpr, Expr $needleExpr, bool $isPositive)
    {
        $this->funcCall = $funcCall;
        $this->haystackExpr = $haystackExpr;
        $this->isPositive = $isPositive;
        $this->needleExpr = $needleExpr;
    }

    public function getFuncCall(): FuncCall
    {
        return $this->funcCall;
    }

    public function getHaystackExpr(): Expr
    {
        return $this->haystackExpr;
    }

    public function isPositive(): bool
    {
        return $this->isPositive;
    }

    public function getNeedleExpr(): Expr
    {
        return $this->needleExpr;
    }
}
