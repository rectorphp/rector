<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class SkipSubstrCompareNotZero
{
    public function run()
    {
        $isMatch = substr_compare($haystack, $needle, -strlen($needle)) === 3;

        $isMatch = 3 === substr_compare($haystack, $needle, -strlen($needle));
    }
}
