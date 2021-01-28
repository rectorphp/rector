<?php

declare(strict_types=1);

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
    public function createCallWithName(Node $node, string $name): Node
    {
        return $node instanceof MethodCall ? new MethodCall($node->var, $name) : new StaticCall($node->class, $name);
    }
}
