<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class StatementNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    /**
     * @var \PhpParser\Node\Stmt|null
     */
    private $previousStmt;
    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        $this->previousStmt = null;
        return null;
    }
    public function enterNode(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            if (!$node instanceof \PhpParser\Node\Stmt) {
                return null;
            }
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT, $this->previousStmt);
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT, $node);
            $this->previousStmt = $node;
        }
        if (\property_exists($node, 'stmts')) {
            $previous = $node;
            foreach ((array) $node->stmts as $stmt) {
                $stmt->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT, $previous);
                $stmt->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT, $stmt);
                $previous = $stmt;
            }
        }
        $currentStmt = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if ($parent && !$currentStmt) {
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT, $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT));
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT, $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT));
        }
        return null;
    }
}
