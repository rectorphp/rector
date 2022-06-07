<?php

declare (strict_types=1);
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
     * @param \PhpParser\Node\Expr\Assign|\PhpParser\Node\Stmt\Foreach_ $node
     */
    public function matchCall($node) : ?Node
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
