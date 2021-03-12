<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class SkipStrposWeak
{
    public function run()
    {
        $isMatch = strpos($haystack, $needle) == 0;
    }
}
