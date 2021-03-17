<?php

namespace Rector\Tests\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector\Fixture;

class SkipHexadecimal
{
    public function run()
    {
        $c = 0xCAFEF00D;   // hexadecimal
    }
}
