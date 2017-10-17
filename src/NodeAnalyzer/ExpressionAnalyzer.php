<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Expression;

final class ExpressionAnalyzer
{
    public function resolvePropertyFetch(Node $node): ?PropertyFetch
    {
        if (! $node instanceof Expression) {
            return null;
        }

        if (! $node->expr instanceof Assign) {
            return null;
        }

        return $this->resolvePropertyFetchNodeFromAssignNode($node->expr);
    }

    private function resolvePropertyFetchNodeFromAssignNode(Assign $assignNode): ?PropertyFetch
    {
        if ($assignNode->expr instanceof PropertyFetch) {
            return $assignNode->expr;
        }

        if ($assignNode->var instanceof PropertyFetch) {
            return $assignNode->var;
        }

        return null;
    }
}
