<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @implements NodeNameResolverInterface<Variable>
 */
final class VariableNameResolver implements NodeNameResolverInterface
{
    public function getNode() : string
    {
        return Variable::class;
    }
    /**
     * @param Variable $node
     */
    public function resolve(Node $node) : ?string
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        // skip $some->$dynamicMethodName()
        if ($parentNode instanceof MethodCall && $node === $parentNode->name) {
            return null;
        }
        if ($node->name instanceof Expr) {
            return null;
        }
        return $node->name;
    }
}
