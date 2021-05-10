<?php

declare (strict_types=1);
namespace Rector\Defluent\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class FluentMethodCallAsArgFactory
{
    public function createFluentAsArg(\PhpParser\Node\Expr\MethodCall $methodCall, \PhpParser\Node\Expr\Variable $variable) : \PhpParser\Node\Expr\MethodCall
    {
        /** @var Arg $parent */
        $parent = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        /** @var MethodCall $parentParent */
        $parentParent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $lastMethodCall = new \PhpParser\Node\Expr\MethodCall($parentParent->var, $parentParent->name);
        $lastMethodCall->args[] = new \PhpParser\Node\Arg($variable);
        return $lastMethodCall;
    }
}
