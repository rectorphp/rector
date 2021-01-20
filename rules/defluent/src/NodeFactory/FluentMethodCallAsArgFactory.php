<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class FluentMethodCallAsArgFactory
{
    public function createFluentAsArg(MethodCall $methodCall, Variable $variable): MethodCall
    {
        /** @var Arg $parent */
        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        /** @var MethodCall $parentParent */
        $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);

        $lastMethodCall = new MethodCall($parentParent->var, $parentParent->name);
        $lastMethodCall->args[] = new Arg($variable);

        return $lastMethodCall;
    }
}
