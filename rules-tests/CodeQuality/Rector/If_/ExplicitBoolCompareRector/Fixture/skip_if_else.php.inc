<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

use PhpParser\Node\Stmt\If_;

final class SkipIfElse
{
    public function run(If_ $if)
    {
        if ((bool) $if->elseifs) {
            return true;
        }

        return false;
    }
}
