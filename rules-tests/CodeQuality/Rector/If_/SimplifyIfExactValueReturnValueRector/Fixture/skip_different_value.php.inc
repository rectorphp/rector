<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfExactValueReturnValueRector\Fixture;

final class SkipDifferentValue
{
    public function run($value)
    {
        if ($value === 94) {
            return 93;
        }

        return $value;
    }
}
