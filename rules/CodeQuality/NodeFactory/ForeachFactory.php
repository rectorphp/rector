<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Exception\ShouldNotHappenException;
final class ForeachFactory
{
    public function createFromFor(\PhpParser\Node\Stmt\For_ $for, string $iteratedVariableName, ?\PhpParser\Node\Expr $iteratedExpr, ?string $keyValueName) : \PhpParser\Node\Stmt\Foreach_
    {
        if ($iteratedExpr === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if ($keyValueName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $foreach = new \PhpParser\Node\Stmt\Foreach_($iteratedExpr, new \PhpParser\Node\Expr\Variable($iteratedVariableName));
        $foreach->stmts = $for->stmts;
        $foreach->keyVar = new \PhpParser\Node\Expr\Variable($keyValueName);
        return $foreach;
    }
}
