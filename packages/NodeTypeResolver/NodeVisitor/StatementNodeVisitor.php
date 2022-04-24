<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class StatementNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    public function enterNode(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            if (!$node instanceof \PhpParser\Node\Stmt) {
                return null;
            }
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT, $node);
        }
        if (\property_exists($node, 'stmts')) {
            foreach ((array) $node->stmts as $stmt) {
                /** @var Stmt $stmt */
                $stmt->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT, $stmt);
            }
        }
        $currentStmt = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!$parent instanceof \PhpParser\Node) {
            return null;
        }
        if ($currentStmt instanceof \PhpParser\Node) {
            return null;
        }
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT, $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT));
        return null;
    }
}
