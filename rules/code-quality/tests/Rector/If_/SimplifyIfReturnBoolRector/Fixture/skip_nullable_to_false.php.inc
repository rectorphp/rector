<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

class SkipNullableToFalse
{
    public function run(string $someString)
    {
        if (strpos($someString, "\n") !== false) {
            return true;
        }

        return 5;
    }
}
