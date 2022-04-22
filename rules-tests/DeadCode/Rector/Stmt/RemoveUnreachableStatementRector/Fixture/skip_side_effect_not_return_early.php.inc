<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SkipSideEffectNotReturnEarly
{
    public function run()
    {
        $this->value = 1;

        if ($this->sideEffect()) {

        }

        if ($this->value === 1) {
            return 'a';
        }

        return 'b';
    }
}
