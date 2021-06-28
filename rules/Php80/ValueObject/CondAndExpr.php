<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr;
use Rector\Php80\Enum\MatchKind;

final class CondAndExpr
{
    /**
     * @param Expr[] $condExprs
     */
    public function __construct(
        private array $condExprs,
        private Expr $expr,
        private MatchKind $matchKind
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

    public function getMatchKind(): MatchKind
    {
        return $this->matchKind;
    }

    public function equalsMatchKind(MatchKind $matchKind): bool
    {
        return $this->matchKind->equals($matchKind);
    }
}
