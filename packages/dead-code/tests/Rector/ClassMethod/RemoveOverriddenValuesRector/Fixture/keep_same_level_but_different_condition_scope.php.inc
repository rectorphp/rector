<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\Fixture;

final class KeepSameLevelButDifferentConditionScope
{
    public function run()
    {
        $value = 5;

        if (true) {
            $value = 10;
        }

        if (false) {
            $value = 15;
        }

        return $value;
    }
}
