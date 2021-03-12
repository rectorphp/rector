<?php

namespace Rector\Tests\CodingStyle\Rector\If_\NullableCompareToNullRector\Fixture;

class KeepNullableBool
{
    public function run(?bool $boolOrNull)
    {
        if (! $boolOrNull) {
            return 'no item nor null';
        }
    }
}
