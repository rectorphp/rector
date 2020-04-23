<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;

final class SubstrFuncCallToHaystack
{
    /**
     * @var FuncCall
     */
    private $substrFuncCall;

    /**
     * @var Expr
     */
    private $haystackExpr;

    /**
     * @var bool
     */
    private $isPositive = false;

    public function __construct(FuncCall $substrFuncCall, Expr $haystackExpr, bool $isPositive)
    {
        $this->substrFuncCall = $substrFuncCall;
        $this->haystackExpr = $haystackExpr;
        $this->isPositive = $isPositive;
    }

    public function getSubstrFuncCall(): FuncCall
    {
        return $this->substrFuncCall;
    }

    public function getHaystackExpr(): Expr
    {
        return $this->haystackExpr;
    }

    public function isPositive(): bool
    {
        return $this->isPositive;
    }
}
