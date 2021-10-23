<?php

namespace Rector\Tests\CodeQuality\Rector\If_\CombineIfRector\Fixture;

use PhpParser\Node\Expr\Assign;

final class SkipNestedType
{
    public function run($expr)
    {
        if ($this->isAssign($expr)) {
            /** @var Assign $expr */
            if ($expr->var) {
                return true;
            }
        }

        return false;
    }

    private function isAssign($expr)
    {
        if ($expr instanceof Assign) {
            return true;
        }

        return false;
    }
}
