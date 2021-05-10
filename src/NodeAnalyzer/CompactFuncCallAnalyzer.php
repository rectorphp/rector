<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\NodeNameResolver\NodeNameResolver;

final class CompactFuncCallAnalyzer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function isInCompact(FuncCall $funcCall, Variable $variable): bool
    {
        if (! $this->nodeNameResolver->isName($funcCall, 'compact')) {
            return false;
        }

        $variableName = $variable->name;
        if (! is_string($variableName)) {
            return false;
        }

        $args = $funcCall->args;
        foreach ($args as $arg) {
            if (! $arg->value instanceof String_) {
                continue;
            }

            if ($arg->value->value === $variableName) {
                return true;
            }
        }

        return false;
    }
}
