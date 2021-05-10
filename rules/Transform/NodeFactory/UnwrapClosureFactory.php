<?php

declare (strict_types=1);
namespace Rector\Transform\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\ShouldNotHappenException;
final class UnwrapClosureFactory
{
    /**
     * @return Node[]
     */
    public function createAssign(\PhpParser\Node\Expr\Variable $resultVariable, \PhpParser\Node\Arg $arg) : array
    {
        $argValue = $arg->value;
        if ($argValue instanceof \PhpParser\Node\Expr\Closure) {
            $unwrappedNodes = $argValue->getStmts();
            \end($argValue->stmts);
            $lastStmtKey = \key($argValue->stmts);
            $lastStmt = $argValue->stmts[$lastStmtKey];
            if ($lastStmt instanceof \PhpParser\Node\Stmt\Return_ && $lastStmt->expr !== null) {
                unset($unwrappedNodes[$lastStmtKey]);
                $unwrappedNodes[] = new \PhpParser\Node\Expr\Assign($resultVariable, $lastStmt->expr);
            }
            return $unwrappedNodes;
        }
        throw new \Rector\Core\Exception\ShouldNotHappenException();
    }
}
