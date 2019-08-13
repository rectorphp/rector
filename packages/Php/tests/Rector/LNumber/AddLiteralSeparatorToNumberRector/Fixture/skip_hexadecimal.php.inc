<?php

namespace Rector\Php\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector\Fixture;

class SkipHexadecimal
{
    public function run()
    {
        $c = 0xCAFEF00D;   // hexadecimal
    }
}
