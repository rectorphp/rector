<?php

namespace Rector\Php74\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector\Fixture;

class SkipHexadecimal
{
    public function run()
    {
        $c = 0xCAFEF00D;   // hexadecimal
    }
}
