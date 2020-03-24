<?php

namespace Rector\Php74\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector\Fixture;

class SkipAlreadySplit
{
    public function run()
    {
        $c = 100_00;
    }
}
