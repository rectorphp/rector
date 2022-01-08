<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector\Fixture;

class SkipNamedArg
{
    public function run(float $data)
    {
        return number_format(num: $data, decimals: 2);
    }
}
