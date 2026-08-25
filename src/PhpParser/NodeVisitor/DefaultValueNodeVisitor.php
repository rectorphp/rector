<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\NodeTraverser\SimpleNodeTraverser;
/**
 * Marks default-value subtrees of params, properties and class constants, so later rules can tell
 * a value node stands in a default position (IS_PARAM_DEFAULT / IS_DEFAULT_PROPERTY_VALUE / IS_CLASS_CONST_VALUE).
 */
final class DefaultValueNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Param) {
            if ($node->default instanceof Expr) {
                SimpleNodeTraverser::decorateWithAttributeValue($node->default, AttributeKey::IS_PARAM_DEFAULT, \true);
            }
            return null;
        }
        if ($node instanceof Property) {
            foreach ($node->props as $propertyItem) {
                $default = $propertyItem->default;
                if (!$default instanceof Expr) {
                    continue;
                }
                SimpleNodeTraverser::decorateWithAttributeValue($default, AttributeKey::IS_DEFAULT_PROPERTY_VALUE, \true);
            }
            return null;
        }
        if ($node instanceof ClassConst) {
            foreach ($node->consts as $const) {
                SimpleNodeTraverser::decorateWithAttributeValue($const->value, AttributeKey::IS_CLASS_CONST_VALUE, \true);
            }
        }
        return null;
    }
}
