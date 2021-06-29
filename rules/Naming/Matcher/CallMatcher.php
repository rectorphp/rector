<?php

declare(strict_types=1);

namespace Rector\Naming\Matcher;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Foreach_;

final class CallMatcher
{
    /**
     * @return FuncCall|StaticCall|MethodCall|null
     */
    public function matchCall(Assign | Foreach_ $node): ?Node
    {
        if ($node->expr instanceof MethodCall) {
            return $node->expr;
        }

        if ($node->expr instanceof StaticCall) {
            return $node->expr;
        }

        if ($node->expr instanceof FuncCall) {
            return $node->expr;
        }

        return null;
    }
}
