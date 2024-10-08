<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
/**
 * Inspired by https://github.com/phpstan/phpstan-src/blob/1.7.x/src/Parser/NewAssignedToPropertyVisitor.php
 */
final class AssignedToNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof AssignOp) {
            $node->var->setAttribute(AttributeKey::IS_ASSIGN_OP_VAR, \true);
            return null;
        }
        if ($node instanceof AssignRef) {
            $node->expr->setAttribute(AttributeKey::IS_ASSIGN_REF_EXPR, \true);
            return null;
        }
        if (!$node instanceof Assign) {
            return null;
        }
        $node->var->setAttribute(AttributeKey::IS_BEING_ASSIGNED, \true);
        $node->expr->setAttribute(AttributeKey::IS_ASSIGNED_TO, \true);
        if ($node->expr instanceof Assign) {
            $node->var->setAttribute(AttributeKey::IS_MULTI_ASSIGN, \true);
            $node->expr->setAttribute(AttributeKey::IS_MULTI_ASSIGN, \true);
            $node->expr->var->setAttribute(AttributeKey::IS_ASSIGNED_TO, \true);
        }
        return null;
    }
}
