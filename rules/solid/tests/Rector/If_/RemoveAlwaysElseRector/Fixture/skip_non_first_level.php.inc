<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElse\Fixture;

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
