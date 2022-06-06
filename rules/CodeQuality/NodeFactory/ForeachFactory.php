<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt\For_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Foreach_;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
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
