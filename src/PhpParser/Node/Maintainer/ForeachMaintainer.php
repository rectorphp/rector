<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Maintainer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;

final class ForeachMaintainer
{
    public function matchOnlyStmt(Foreach_ $foreachNode, callable $callable): ?Node
    {
        if (count($foreachNode->stmts) !== 1) {
            return null;
        }

        $innerNode = $foreachNode->stmts[0];
        $innerNode = $innerNode instanceof Expression ? $innerNode->expr : $innerNode;

        return $callable($innerNode, $foreachNode);
    }
}
