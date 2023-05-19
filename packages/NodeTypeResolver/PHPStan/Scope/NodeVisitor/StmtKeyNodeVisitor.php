<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
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
        // count = 1 is essential here as FileWithoutNamespace can merge with other Stmt
        if (\count($nodes) === 1) {
            $currentNode = \current($nodes);
            if ($currentNode instanceof FileWithoutNamespace) {
                foreach ($currentNode->stmts as $key => $stmt) {
                    $stmt->setAttribute(AttributeKey::STMT_KEY, $key);
                }
            }
            return $nodes;
        }
        foreach ($nodes as $key => $node) {
            $node->setAttribute(AttributeKey::STMT_KEY, $key);
        }
        return $nodes;
    }
    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes) : array
    {
        foreach ($nodes as $key => $node) {
            if (!$node instanceof Namespace_) {
                return $nodes;
            }
            $node->setAttribute(AttributeKey::STMT_KEY, $key);
        }
        return $nodes;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof StmtsAwareInterface) {
            return null;
        }
        // covered on beforeTraverse() as top level node handling
        if ($node instanceof FileWithoutNamespace) {
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        // re-index stmt key under current node
        foreach ($node->stmts as $key => $childStmt) {
            $childStmt->setAttribute(AttributeKey::STMT_KEY, $key);
        }
        return null;
    }
}
