<?php

declare (strict_types=1);
namespace Rector\Naming\Matcher;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
final class CallMatcher
{
    /**
     * @return FuncCall|StaticCall|MethodCall|null
     */
    public function matchCall(Assign $assign): ?Node
    {
        if ($assign->expr instanceof MethodCall) {
            return $assign->expr;
        }
        if ($assign->expr instanceof StaticCall) {
            return $assign->expr;
        }
        if ($assign->expr instanceof FuncCall) {
            return $assign->expr;
        }
        return null;
    }
}
