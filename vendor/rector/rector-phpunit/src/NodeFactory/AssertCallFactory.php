<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
final class AssertCallFactory
{
    /**
     * @param StaticCall|MethodCall $node
     * @return StaticCall|MethodCall
     */
    public function createCallWithName(\PhpParser\Node $node, string $name) : \PhpParser\Node
    {
        return $node instanceof \PhpParser\Node\Expr\MethodCall ? new \PhpParser\Node\Expr\MethodCall($node->var, $name) : new \PhpParser\Node\Expr\StaticCall($node->class, $name);
    }
}
