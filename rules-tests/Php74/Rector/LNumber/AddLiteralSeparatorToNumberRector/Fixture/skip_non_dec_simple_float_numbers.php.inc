<?php

namespace Rector\Php74\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector\Fixture;

class SkipNonDecSimpleFloatNumbers
{
    public function run()
    {
        $a = 6.674083e-11; // float
        $d = 0b01011111;   // binary
        $e = 0137041;      // octal
    }
}
