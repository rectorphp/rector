<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class SkipStrncmpHardCodedInvalidLength
{
    public function run()
    {
        $isMatch = strncmp($haystack, 'invalid_length', 1) === 0;

        $isMatch = 0 === strncmp($haystack, 'invalid_length', 1);
    }
}
