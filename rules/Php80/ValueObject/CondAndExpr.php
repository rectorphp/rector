<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr;

final class CondAndExpr
{
    /**
     * @param Expr[] $condExprs
     */
    public function __construct(
        private array $condExprs,
        private Expr $expr,
        private string $kind
    ) {
    }

    public function getExpr(): Expr
    {
        return $this->expr;
    }

    /**
     * @return Expr[]
     */
    public function getCondExprs(): array
    {
        return $this->condExprs;
    }

    public function getKind(): string
    {
        return $this->kind;
    }
}
