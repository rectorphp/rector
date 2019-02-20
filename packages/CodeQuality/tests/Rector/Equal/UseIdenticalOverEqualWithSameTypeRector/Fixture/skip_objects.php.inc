<?php

namespace Rector\CodeQuality\Tests\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector\Fixture;

class SkipObjects
{
    public function run(\stdClass $firstValue, \stdClass $secondValue)
    {
         $isSame = $firstValue == $secondValue;
         $isDiffernt = $firstValue != $secondValue;
    }
}
