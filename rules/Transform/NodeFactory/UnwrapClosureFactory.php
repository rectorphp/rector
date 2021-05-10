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
    public function createAssign(Variable $resultVariable, Arg $arg) : array
    {
        $argValue = $arg->value;
        if ($argValue instanceof Closure) {
            $unwrappedNodes = $argValue->getStmts();
            \end($argValue->stmts);
            $lastStmtKey = \key($argValue->stmts);
            $lastStmt = $argValue->stmts[$lastStmtKey];
            if ($lastStmt instanceof Return_ && $lastStmt->expr !== null) {
                unset($unwrappedNodes[$lastStmtKey]);
                $unwrappedNodes[] = new Assign($resultVariable, $lastStmt->expr);
            }
            return $unwrappedNodes;
        }
        throw new ShouldNotHappenException();
    }
}
