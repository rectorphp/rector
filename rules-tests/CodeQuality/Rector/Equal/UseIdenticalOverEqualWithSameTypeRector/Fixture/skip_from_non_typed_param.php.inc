<?php

namespace Rector\Tests\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector\Fixture;

class SkipFromNonTypedParam
{
    /**
     * @param int $firstValue
     * @param int $secondValue
     */
    public function run($firstValue, $secondValue)
    {
         $isSame = $firstValue == $secondValue;
         $isDiffernt = $firstValue != $secondValue;
    }
}
