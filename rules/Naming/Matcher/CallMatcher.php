<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Matcher;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Stmt\Foreach_;
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
