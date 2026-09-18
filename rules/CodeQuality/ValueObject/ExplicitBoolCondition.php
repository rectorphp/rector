<?php

declare (strict_types=1);
namespace Rector\CodeQuality\ValueObject;

use PhpParser\Node\Expr;
final class ExplicitBoolCondition
{
    /**
     * @readonly
     */
    private Expr $expr;
    /**
     * @readonly
     */
    private bool $isNegated;
    public function __construct(Expr $expr, bool $isNegated)
    {
        $this->expr = $expr;
        $this->isNegated = $isNegated;
    }
    public function getConditionNode(): Expr
    {
        return $this->expr;
    }
    public function isNegated(): bool
    {
        return $this->isNegated;
    }
}
