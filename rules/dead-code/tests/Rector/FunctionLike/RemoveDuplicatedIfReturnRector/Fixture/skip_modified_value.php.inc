<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class SkipModifiedValue
{
    public function run()
    {
        $value = random_int(1, 5);
        if ($value) {
            return true;
        }

        $value = random_int(1, 5);
        if ($value) {
            return true;
        }
    }
}
