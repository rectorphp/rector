<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfExactValueReturnValueRector\Fixture;

final class SkipNotIdentical
{
    public function run($value)
    {
        if ($value !== 93) {
            return 93;
        }

        return $value;
    }
}
