<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
final class StmtKeyNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        if ($nodes === []) {
            return null;
        }
        if (!$nodes[0] instanceof Declare_ && !$nodes[0] instanceof Namespace_) {
            return null;
        }
        // on target node or no other root stmt, eg: only namespace without declare, no need to index
        if (\count($nodes) === 1) {
            return null;
        }
        // ensure statement depth is 0 to avoid declare in deep statements
        // eg: declare(ticks=1) @see https://www.php.net/manual/en/control-structures.declare.php#123674
        $statementDepth = $nodes[0]->getAttribute(AttributeKey::STATEMENT_DEPTH);
        if ($statementDepth > 0 || $statementDepth === null) {
            return null;
        }
        foreach ($nodes as $key => $node) {
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
        $node->stmts = \array_values($node->stmts);
        // re-index stmt key under current node
        foreach ($node->stmts as $key => $childStmt) {
            $childStmt->setAttribute(AttributeKey::STMT_KEY, $key);
        }
        return null;
    }
}
