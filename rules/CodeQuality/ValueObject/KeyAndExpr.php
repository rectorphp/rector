<?php

declare(strict_types=1);

namespace Rector\CodeQuality\ValueObject;

use PhpParser\Node\Expr;

final class KeyAndExpr
{
    public function __construct(
        private readonly Expr $keyExpr,
        private readonly Expr $expr
    ) {
    }

    public function getKeyExpr(): Expr
    {
        return $this->keyExpr;
    }

    public function getExpr(): Expr
    {
        return $this->expr;
    }
}
