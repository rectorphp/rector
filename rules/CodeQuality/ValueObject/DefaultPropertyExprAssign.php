<?php

declare(strict_types=1);

namespace Rector\CodeQuality\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;

final class DefaultPropertyExprAssign
{
    public function __construct(
        private readonly Expression $assignExpression,
        private readonly string $propertyName,
        private readonly Expr $defaultExpr
    ) {
    }

    public function getAssignExpression(): Expression
    {
        return $this->assignExpression;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getDefaultExpr(): Expr
    {
        return $this->defaultExpr;
    }
}
