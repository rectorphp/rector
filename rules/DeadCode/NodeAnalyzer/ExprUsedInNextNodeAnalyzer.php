<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class ExprUsedInNextNodeAnalyzer
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer
    ) {
    }

    public function isUsed(Expr $expr): bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext(
            $expr,
            fn (Node $node): bool => $this->exprUsedInNodeAnalyzer->isUsed($node, $expr)
        );
    }
}
