<?php

namespace Rector\Tests\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector\Fixture;

class SkipAlreadyFormatted
{
    function donate(int $amount = 10_000_00)
    {
        echo $amount;
    }
}
