<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;

final class FuncCallAndExpr
{
    /**
     * @var FuncCall
     */
    private $funcCall;

    /**
     * @var Expr
     */
    private $expr;

    public function __construct(FuncCall $funcCall, Expr $expr)
    {
        $this->funcCall = $funcCall;
        $this->expr = $expr;
    }

    public function getFuncCall(): FuncCall
    {
        return $this->funcCall;
    }

    public function getExpr(): Expr
    {
        return $this->expr;
    }
}
