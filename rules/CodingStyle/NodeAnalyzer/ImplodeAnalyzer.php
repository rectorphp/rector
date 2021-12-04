<?php

declare(strict_types=1);

namespace Rector\CodingStyle\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;

final class ImplodeAnalyzer
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly ArgsAnalyzer $argsAnalyzer
    ) {
    }

    /**
     * Matches: "implode('","', $items)"
     */
    public function isImplodeToJson(Expr $expr): bool
    {
        if (! $expr instanceof FuncCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($expr, 'implode')) {
            return false;
        }

        if (! $this->argsAnalyzer->isArgInstanceInArgsPosition($expr->args, 1)) {
            return false;
        }

        if (! $this->argsAnalyzer->isArgInstanceInArgsPosition($expr->args, 0)) {
            return false;
        }

        /** @var Arg $firstArg */
        $firstArg = $expr->args[0];
        $firstArgumentValue = $firstArg->value;
        if (! $firstArgumentValue instanceof String_) {
            return true;
        }

        return $firstArgumentValue->value === '","';
    }
}
