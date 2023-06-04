<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
final class BreakingRemovalGuardNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof If_) {
            $node->cond->setAttribute(AttributeKey::IS_BREAKING_REMOVAL_NODE, \true);
            $node->cond->setAttribute(AttributeKey::CHILD_OF_NODE_TYPE, If_::class);
            return null;
        }
        if ($node instanceof BooleanNot) {
            $node->expr->setAttribute(AttributeKey::IS_BREAKING_REMOVAL_NODE, \true);
            $node->expr->setAttribute(AttributeKey::CHILD_OF_NODE_TYPE, BooleanNot::class);
            return null;
        }
        if ($node instanceof While_) {
            $node->cond->setAttribute(AttributeKey::IS_BREAKING_REMOVAL_NODE, \true);
            $node->cond->setAttribute(AttributeKey::CHILD_OF_NODE_TYPE, While_::class);
        }
        return null;
    }
}
