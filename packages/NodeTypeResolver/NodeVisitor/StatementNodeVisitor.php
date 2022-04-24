<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class StatementNodeVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node): ?Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Node) {
            if (! $node instanceof Stmt) {
                return null;
            }

            $node->setAttribute(AttributeKey::CURRENT_STATEMENT, $node);
        }

        if (property_exists($node, 'stmts')) {
            foreach ((array) $node->stmts as $stmt) {
                /** @var Stmt $stmt */
                $stmt->setAttribute(AttributeKey::CURRENT_STATEMENT, $stmt);
            }
        }

        $currentStmt = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if (! $parent instanceof Node) {
            return null;
        }

        if ($currentStmt instanceof Node) {
            return null;
        }

        $node->setAttribute(
            AttributeKey::CURRENT_STATEMENT,
            $parent->getAttribute(AttributeKey::CURRENT_STATEMENT)
        );
        return null;
    }
}
