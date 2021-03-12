<?php

namespace Rector\Php80\Tests\Rector\Identical\StrStartsWithRector\Fixture;

class SkipNonZero
{
    public function run()
    {
        $isMatch = substr($haystack, 1, strlen($needle)) === $needle;
    }
}
