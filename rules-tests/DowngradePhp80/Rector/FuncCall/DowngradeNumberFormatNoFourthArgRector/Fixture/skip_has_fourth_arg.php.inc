<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector\Fixture;

class SkipHasFourthArg
{
    public function run(float $data)
    {
        return number_format($data, 2, ',', '.');
    }
}
