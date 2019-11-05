<?php

namespace Rector\CodingStyle\Tests\Rector\If_\NullableCompareToNullRector\Fixture;

class KeepNullableBool
{
    public function run(?bool $boolOrNull)
    {
        if (! $boolOrNull) {
            return 'no item nor null';
        }
    }
}
