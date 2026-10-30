<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
final class AssertCallFactory
{
    /**
     * @template TCall of StaticCall|MethodCall
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     * @return ($node is MethodCall ? MethodCall : StaticCall)
     */
    public function createCallWithName($node, string $name)
    {
        if ($node instanceof MethodCall) {
            return new MethodCall($node->var, $name);
        }
        return new StaticCall($node->class, $name);
    }
}
