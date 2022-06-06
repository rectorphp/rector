<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Transform\NodeFactory;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
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
