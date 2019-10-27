<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class StatementNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Expression|null
     */
    private $previousStatement;

    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->previousStatement = null;

        return null;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parent) {
            $node->setAttribute(AttributeKey::PREVIOUS_STATEMENT, $this->previousStatement);
            $node->setAttribute(AttributeKey::CURRENT_STATEMENT, $node);
            $this->previousStatement = $node;
        }

        if (property_exists($node, 'stmts')) {
            $previous = null;
            $previous = $node;
            foreach ((array)$node->stmts as $stmt) {
                $stmt->setAttribute(AttributeKey::PREVIOUS_STATEMENT, $previous);
                $stmt->setAttribute(AttributeKey::CURRENT_STATEMENT, $stmt);
                $previous = $stmt;
            }
        }
        if (!$node->getAttribute(AttributeKey::CURRENT_STATEMENT)) {

            $node->setAttribute(AttributeKey::PREVIOUS_STATEMENT, $parent->getAttribute(AttributeKey::PREVIOUS_STATEMENT));
            $node->setAttribute(AttributeKey::CURRENT_STATEMENT, $parent->getAttribute(AttributeKey::CURRENT_STATEMENT));
        }
    }
}
