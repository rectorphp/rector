<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class ExprAnalyzer
{
    public function __construct(
        private readonly NodeComparator $nodeComparator,
        private readonly BetterNodeFinder $betterNodeFinder,
    ) {
    }

    public function isNonTypedFromParam(Expr $expr): bool
    {
        if (! $expr instanceof Variable) {
            return false;
        }

        $functionLike = $this->betterNodeFinder->findParentType($expr, FunctionLike::class);
        if (! $functionLike instanceof FunctionLike) {
            return false;
        }

        $params = $functionLike->getParams();
        foreach ($params as $param) {
            if (! $this->nodeComparator->areNodesEqual($param->var, $expr)) {
                continue;
            }

            return $param->type === null;
        }

        return false;
    }
}
