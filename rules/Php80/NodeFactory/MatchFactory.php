<?php

declare(strict_types=1);

namespace Rector\Php80\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Match_;
use Rector\Php80\ValueObject\CondAndExpr;

final class MatchFactory
{
    public function __construct(
        private MatchArmsFactory $matchArmsFactory
    ) {
    }

    /**
     * @param CondAndExpr[] $condAndExprs
     */
    public function createFromCondAndExprs(Expr $condExpr, array $condAndExprs): Match_
    {
        $matchArms = $this->matchArmsFactory->createFromCondAndExprs($condAndExprs);
        return new Match_($condExpr, $matchArms);
    }
}
