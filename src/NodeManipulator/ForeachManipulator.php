<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
final class ForeachManipulator
{
    /**
     * @param callable(Node $node, Foreach_ $foreach=): ?Node $callable
     */
    public function matchOnlyStmt(Foreach_ $foreach, callable $callable) : ?Node
    {
        if (\count($foreach->stmts) !== 1) {
            return null;
        }
        $innerNode = $foreach->stmts[0];
        $innerNode = $innerNode instanceof Expression ? $innerNode->expr : $innerNode;
        return $callable($innerNode, $foreach);
    }
}
