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
    public function matchOnlyStmt(\PhpParser\Node\Stmt\Foreach_ $foreach, callable $callable) : ?\PhpParser\Node
    {
        $stmts = $foreach->stmts;
        if (\count($stmts) !== 1) {
            return null;
        }
        $innerNode = $stmts[0];
        $innerNode = $innerNode instanceof \PhpParser\Node\Stmt\Expression ? $innerNode->expr : $innerNode;
        return $callable($innerNode, $foreach);
    }
}
