<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
final class ReprintNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    public function enterNode(Node $node) : ?Node
    {
        if ($node->getAttribute(AttributeKey::ORIGINAL_NODE) instanceof Node) {
            return null;
        }
        if ($node instanceof BinaryOp && !$node instanceof Coalesce) {
            if ($node->left instanceof BinaryOp && $node->left->getAttribute(AttributeKey::ORIGINAL_NODE) instanceof Node) {
                $node->left->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
            if ($node->right instanceof BinaryOp && $node->right->getAttribute(AttributeKey::ORIGINAL_NODE) instanceof Node) {
                $node->right->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
            return $node;
        }
        if ($node instanceof BooleanNot && $node->expr instanceof BinaryOp && $node->expr->getAttribute(AttributeKey::ORIGINAL_NODE) instanceof Node) {
            $node->expr->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \true);
        }
        return null;
    }
}
