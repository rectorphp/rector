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
    public function createFromFor(For_ $for, string $iteratedVariableName, ?Expr $iteratedExpr, ?string $keyValueName) : Foreach_
    {
        if ($iteratedExpr === null) {
            throw new ShouldNotHappenException();
        }
        if ($keyValueName === null) {
            throw new ShouldNotHappenException();
        }
        $foreach = new Foreach_($iteratedExpr, new Variable($iteratedVariableName));
        $foreach->stmts = $for->stmts;
        $foreach->keyVar = new Variable($keyValueName);
        return $foreach;
    }
}
