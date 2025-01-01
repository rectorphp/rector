<?php

declare (strict_types=1);
namespace Rector\Application;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Block;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NodeAttributeReIndexer
{
    public static function reIndexStmtKeyNodeAttributes(Node $node) : ?Node
    {
        if (!$node instanceof StmtsAwareInterface && !$node instanceof ClassLike && !$node instanceof Declare_ && !$node instanceof Block) {
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
        return $node;
    }
    public static function reIndexNodeAttributes(Node $node, bool $reIndexStmtKey = \true) : ?Node
    {
        if ($reIndexStmtKey) {
            self::reIndexStmtKeyNodeAttributes($node);
        }
        if ($node instanceof If_) {
            $node->elseifs = \array_values($node->elseifs);
            return $node;
        }
        if ($node instanceof TryCatch) {
            $node->catches = \array_values($node->catches);
            return $node;
        }
        if ($node instanceof FunctionLike) {
            /** @var ClassMethod|Function_|Closure $node */
            $node->params = \array_values($node->params);
            if ($node instanceof Closure) {
                $node->uses = \array_values($node->uses);
            }
            return $node;
        }
        if ($node instanceof CallLike) {
            /** @var FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $node */
            $node->args = \array_values($node->args);
            return $node;
        }
        if ($node instanceof Switch_) {
            $node->cases = \array_values($node->cases);
            return $node;
        }
        return null;
    }
}
