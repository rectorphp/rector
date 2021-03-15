<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

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
