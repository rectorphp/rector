<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\List_;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * Inspired by https://github.com/phpstan/phpstan-src/blob/1.7.x/src/Parser/NewAssignedToPropertyVisitor.php
 */
final class AssignedToNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof AssignOp) {
            $this->markAssignOpVar($node->var);
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
        if ($node->var instanceof List_) {
            foreach ($node->var->items as $item) {
                if ($item instanceof ArrayItem) {
                    $item->value->setAttribute(AttributeKey::IS_BEING_ASSIGNED, \true);
                }
            }
        }
        $node->expr->setAttribute(AttributeKey::IS_ASSIGNED_TO, \true);
        if ($node->expr instanceof Assign) {
            $node->var->setAttribute(AttributeKey::IS_MULTI_ASSIGN, \true);
            $node->expr->setAttribute(AttributeKey::IS_MULTI_ASSIGN, \true);
            $node->expr->var->setAttribute(AttributeKey::IS_ASSIGNED_TO, \true);
        }
        return null;
    }
    /**
     * Marks the whole dim fetch chain, not just the outermost node: $array[$key][0] += 1 writes
     * to $array[$key] as well, so a rule that rewrites the inner fetch would change a write
     * into a read of a temporary value.
     */
    private function markAssignOpVar(Expr $expr): void
    {
        $expr->setAttribute(AttributeKey::IS_ASSIGN_OP_VAR, \true);
        while ($expr instanceof ArrayDimFetch) {
            $expr = $expr->var;
            $expr->setAttribute(AttributeKey::IS_ASSIGN_OP_VAR, \true);
        }
    }
}
