<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @implements NodeNameResolverInterface<Variable>
 */
final class VariableNameResolver implements \Rector\NodeNameResolver\Contract\NodeNameResolverInterface
{
    public function getNode() : string
    {
        return \PhpParser\Node\Expr\Variable::class;
    }
    /**
     * @param Variable $node
     */
    public function resolve(\PhpParser\Node $node) : ?string
    {
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // skip $some->$dynamicMethodName()
        if ($parentNode instanceof \PhpParser\Node\Expr\MethodCall && $node === $parentNode->name) {
            return null;
        }
        if ($node->name instanceof \PhpParser\Node\Expr) {
            return null;
        }
        return $node->name;
    }
}
