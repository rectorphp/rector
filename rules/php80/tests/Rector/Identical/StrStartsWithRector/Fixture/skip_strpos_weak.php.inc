<?php

namespace Rector\Php80\Tests\Rector\Identical\StrStartsWithRector\Fixture;

class SkipStrposWeak
{
    public function run()
    {
        $isMatch = strpos($haystack, $needle) == 0;
    }
}
