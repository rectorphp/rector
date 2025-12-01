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
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\TryCatch;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPrefix202512\Webmozart\Assert\Assert;
final class NodeAttributeReIndexer
{
    public static function reIndexNodeAttributes(Node $node): ?Node
    {
        self::reIndexStmtsKeys($node);
        if ($node instanceof If_) {
            $node->elseifs = array_values($node->elseifs);
            return $node;
        }
        if ($node instanceof TryCatch) {
            $node->catches = array_values($node->catches);
            return $node;
        }
        if ($node instanceof FunctionLike) {
            /** @var ClassMethod|Function_|Closure $node */
            $node->params = array_values($node->params);
            if ($node instanceof Closure) {
                $node->uses = array_values($node->uses);
            }
            return $node;
        }
        if ($node instanceof CallLike) {
            /** @var FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $node */
            $node->args = array_values($node->args);
            return $node;
        }
        if ($node instanceof Switch_) {
            $node->cases = array_values($node->cases);
            return $node;
        }
        return null;
    }
    private static function reIndexStmtsKeys(Node $node): ?Node
    {
        if (!NodeGroup::isStmtAwareNode($node) && !$node instanceof ClassLike && !$node instanceof Declare_) {
            return null;
        }
        Assert::propertyExists($node, 'stmts');
        if ($node->stmts === null) {
            return null;
        }
        $node->stmts = array_values($node->stmts);
        return $node;
    }
}
