<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\NodeManipulator;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Foreach_;
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
