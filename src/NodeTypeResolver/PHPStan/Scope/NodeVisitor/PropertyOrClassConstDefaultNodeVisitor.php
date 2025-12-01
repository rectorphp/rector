<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\NodeTraverser\SimpleTraverser;
final class PropertyOrClassConstDefaultNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Property) {
            foreach ($node->props as $propertyItem) {
                $default = $propertyItem->default;
                if (!$default instanceof Expr) {
                    continue;
                }
                SimpleTraverser::decorateWithTrueAttribute($default, AttributeKey::IS_DEFAULT_PROPERTY_VALUE);
            }
        }
        if ($node instanceof ClassConst) {
            foreach ($node->consts as $const) {
                SimpleTraverser::decorateWithTrueAttribute($const->value, AttributeKey::IS_CLASS_CONST_VALUE);
            }
        }
        return null;
    }
}
