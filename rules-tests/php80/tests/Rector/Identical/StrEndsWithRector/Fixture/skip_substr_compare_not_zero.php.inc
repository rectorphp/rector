<?php

namespace Rector\Php80\Tests\Rector\Identical\StrEndsWithRector\Fixture;

class SkipSubstrCompareNotZero
{
    public function run()
    {
        $isMatch = substr_compare($haystack, $needle, -strlen($needle)) === 3;
    }
}
