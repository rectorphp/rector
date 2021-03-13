<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class FirstLevelNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @return Node
     */
    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof FunctionLike) {
            foreach ((array) $node->getStmts() as $stmt) {
                $stmt->setAttribute(AttributeKey::IS_FIRST_LEVEL_STATEMENT, true);

                if ($stmt instanceof Expression) {
                    $stmt->expr->setAttribute(AttributeKey::IS_FIRST_LEVEL_STATEMENT, true);
                }
            }
        }

        return null;
    }
}
