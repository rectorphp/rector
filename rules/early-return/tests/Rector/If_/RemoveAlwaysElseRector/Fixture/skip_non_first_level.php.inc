<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

use PhpParser\Node\Expr\BinaryOp\Equal;

class SkipNonFirstLevel
{
    public function test($node)
    {
        if ($node->cond instanceof Equal) {
            if ($node->stmts[0]->expr === null) {
                return null;
            }
        } else {
            return null;
        }
    }
}
