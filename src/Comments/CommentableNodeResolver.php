<?php

declare(strict_types=1);

namespace Rector\Core\Comments;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * Resolve nearest node, where we can add comment
 */
final class CommentableNodeResolver
{
    public function resolve(Node $node): Node
    {
        $currentNode = $node;
        $previousNode = $node;

        while (! $currentNode instanceof Stmt) {
            $currentNode = $currentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($currentNode === null) {
                return $previousNode;
            }

            $previousNode = $currentNode;
        }

        return $currentNode;
    }
}
