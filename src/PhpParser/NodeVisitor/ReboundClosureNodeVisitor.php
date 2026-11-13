<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * Marks closure/arrow function with rebound $this, e.g. (fn () => ...)->call($object)
 * @see https://github.com/rectorphp/rector/issues/9964
 */
final class ReboundClosureNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof MethodCall) {
            return null;
        }
        if (!$node->var instanceof Closure && !$node->var instanceof ArrowFunction) {
            return null;
        }
        if (!$node->name instanceof Identifier) {
            return null;
        }
        if (!in_array($node->name->toLowerString(), ['call', 'bindto'], \true)) {
            return null;
        }
        $node->var->setAttribute(AttributeKey::IS_REBOUND_CLOSURE, \true);
        return null;
    }
}
