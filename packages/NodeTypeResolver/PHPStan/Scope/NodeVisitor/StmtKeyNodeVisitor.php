<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
final class StmtKeyNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function beforeTraverse(array $nodes) : array
    {
        foreach ($nodes as $key => $node) {
            $currentStmtKey = $node->getAttribute(AttributeKey::STMT_KEY);
            if ($currentStmtKey !== null) {
                continue;
            }
            $node->setAttribute(AttributeKey::STMT_KEY, $key);
        }
        return $nodes;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof StmtsAwareInterface && !$node instanceof ClassLike && !$node instanceof Declare_) {
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        // re-index stmt key under current node
        foreach ($node->stmts as $key => $childStmt) {
            $childStmt->setAttribute(AttributeKey::STMT_KEY, $key);
        }
        if ($node instanceof Else_ || $node instanceof ElseIf_ || $node instanceof Catch_ || $node instanceof Finally_) {
            $node->setAttribute(AttributeKey::STMT_KEY, 0);
        }
        return null;
    }
}
