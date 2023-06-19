<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
final class ObjectNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof MethodCall) {
            $node->var->setAttribute(AttributeKey::IS_OBJECT_CALLER, \true);
            return null;
        }
        if ($node instanceof StaticCall) {
            $node->class->setAttribute(AttributeKey::IS_OBJECT_CALLER, \true);
        }
        return null;
    }
}
