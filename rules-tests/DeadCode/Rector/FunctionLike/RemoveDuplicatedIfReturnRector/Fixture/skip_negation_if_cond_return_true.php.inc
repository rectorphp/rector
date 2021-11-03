<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class SkipNegationIfCondReturnReturnTrueNextReturnCond
{
    public function run(bool $value)
    {
        if (! $value) {
            return true;
        }

        return $value;
    }
}