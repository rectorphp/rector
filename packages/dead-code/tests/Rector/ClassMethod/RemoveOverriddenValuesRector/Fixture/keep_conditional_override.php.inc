<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\Fixture;

final class KeepConditionalOverride
{
    public function run()
    {
        $value = 5;

        if (true) {
            $value = 10;
        }

        return $value;
    }
}
