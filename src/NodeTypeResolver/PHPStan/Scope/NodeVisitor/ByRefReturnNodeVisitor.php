<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
final class ByRefReturnNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof FunctionLike) {
            return null;
        }
        if (!$node->returnsByRef()) {
            return null;
        }
        $stmts = $node->getStmts();
        if ($stmts === null) {
            return null;
        }
        $this->setByRefAttribute($stmts);
        return null;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function setByRefAttribute(array $stmts) : void
    {
        foreach ($stmts as $stmt) {
            if ($stmt instanceof FunctionLike) {
                continue;
            }
            if ($stmt instanceof StmtsAwareInterface && $stmt->stmts !== null) {
                $this->setByRefAttribute($stmt->stmts);
                continue;
            }
            if ($stmt instanceof Return_) {
                $stmt->setAttribute(AttributeKey::IS_BYREF_RETURN, \true);
            }
        }
    }
}
