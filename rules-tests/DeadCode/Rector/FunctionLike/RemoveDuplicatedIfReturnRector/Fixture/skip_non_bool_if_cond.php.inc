<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class SkipNonBoolIfCond
{
    public function run(stdClass $value)
    {
        if ($value) {
            return true;
        }

        return $value;
    }
}
