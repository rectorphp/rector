<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class SkipThis
{
    public function run($value)
    {
        if ($value) {
            return true;
        }

        $value2 = 100;

        if ($value2) {
            return true;
        }
    }
}
