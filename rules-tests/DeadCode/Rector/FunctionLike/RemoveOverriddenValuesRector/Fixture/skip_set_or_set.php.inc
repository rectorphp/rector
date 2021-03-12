<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class SkipSetOrSet
{
    public function run()
    {
        $isSet = (($one = 10) || ($one = 200));
    }
}
