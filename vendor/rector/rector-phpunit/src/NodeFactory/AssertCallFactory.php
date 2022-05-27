<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
final class AssertCallFactory
{
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $node
     * @return \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall
     */
    public function createCallWithName($node, string $name)
    {
        if ($node instanceof MethodCall) {
            return new MethodCall($node->var, $name);
        }
        return new StaticCall($node->class, $name);
    }
}
