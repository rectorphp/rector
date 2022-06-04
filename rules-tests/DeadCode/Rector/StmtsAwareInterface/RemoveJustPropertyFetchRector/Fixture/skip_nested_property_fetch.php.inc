<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Fixture;

use PhpParser\Node\Expr\Assign;

final class SkipNestedPropertyFetch
{
    public function run(Assign $assign)
    {
        if ($assign->var instanceof Assign) {
            $nestedAssign = $assign->var;

            echo $nestedAssign->expr;
        }
    }
}
