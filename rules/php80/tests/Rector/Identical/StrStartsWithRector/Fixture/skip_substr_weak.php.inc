<?php

namespace Rector\Php80\Tests\Rector\Identical\StrStartsWithRector\Fixture;

class SkipSubstrWeak
{
    public function run()
    {
        $isMatch = substr($haystack, 0, strlen($needle)) == $needle;
    }
}
