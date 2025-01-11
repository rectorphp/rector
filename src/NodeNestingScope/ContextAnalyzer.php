<?php

declare (strict_types=1);
namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ContextAnalyzer
{
    /**
     * @api
     */
    public function isInLoop(Node $node) : bool
    {
        return $node->getAttribute(AttributeKey::IS_IN_LOOP) === \true;
    }
    /**
     * @api
     */
    public function isInIf(Node $node) : bool
    {
        return $node->getAttribute(AttributeKey::IS_IN_IF) === \true;
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch|\PhpParser\Node\Expr\NullsafePropertyFetch $propertyFetch
     */
    public function isChangeableContext($propertyFetch) : bool
    {
        if ($propertyFetch->getAttribute(AttributeKey::IS_UNSET_VAR, \false)) {
            return \true;
        }
        if ($propertyFetch->getAttribute(AttributeKey::INSIDE_ARRAY_DIM_FETCH, \false)) {
            return \true;
        }
        if ($propertyFetch->getAttribute(AttributeKey::IS_USED_AS_ARG_BY_REF_VALUE, \false) === \true) {
            return \true;
        }
        return $propertyFetch->getAttribute(AttributeKey::IS_INCREMENT_OR_DECREMENT, \false) === \true;
    }
    public function isLeftPartOfAssign(Node $node) : bool
    {
        if ($node->getAttribute(AttributeKey::IS_BEING_ASSIGNED) === \true) {
            return \true;
        }
        if ($node->getAttribute(AttributeKey::IS_ASSIGN_REF_EXPR) === \true) {
            return \true;
        }
        return $node->getAttribute(AttributeKey::IS_ASSIGN_OP_VAR) === \true;
    }
}
