<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class SkipSetOrSet
{
    public function run()
    {
        $isSet = (($one = 10) || ($one = 200));
    }
}
