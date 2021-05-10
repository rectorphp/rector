<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;

final class StrStartsWith
{
    public function __construct(
        private FuncCall $funcCall,
        private Expr $haystackExpr,
        private Expr $needleExpr,
        private bool $isPositive
    ) {
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
